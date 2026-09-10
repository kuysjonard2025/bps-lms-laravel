<?php

namespace App\Livewire;

use App\Exports\CirculationsExport;
use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Accession;
use App\Models\Circulation;
use App\Models\CirculationPolicy;
use App\Models\Patron;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Circulations extends Component
{
    use WithPagination, SanitizesInputs;

    public string $activeMode = 'checkout'; // 'checkout', 'checkin', 'active_loans'

    // Modal Control State
    public bool $showPaymentModal = false;
    public bool $showEditPaymentModal = false;

    // Checkout Form Inputs & Selected State
    public string $patronInput = '';
    public string $accessionInput = '';
    public ?Patron $selectedPatron = null;
    public ?Accession $selectedAccession = null;
    public bool $isTimedIn = true;

    // Checkin / Inspection State
    public string $returnAccessionInput = '';
    public ?Circulation $inspectedLoan = null;
    public string $returnCondition = 'good';
    public float $overdueFineAmount = 0.00;
    public float $manualFineAmount = 0.00;
    public bool $isPaidNow = false;
    public string $checkinReceiptNumber = '';

    // Payment Edit State
    public ?int $editingLoanId = null;
    public float $editFineAmount = 0.00;
    public ?string $editReceiptNumber = '';
    public bool $isEditPaid = false;

    // Active Loans / History Search & Filters
    public string $search = '';
    public string $filterStatus = 'borrowed';

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function switchMode(string $mode): void
    {
        $this->activeMode = $mode;
        $this->resetErrorBag();
    }

    public function updatedPatronInput(): void
    {
        try {
            $code = trim(strip_tags($this->patronInput));
            if (empty($code)) {
                $this->selectedPatron = null;
                return;
            }

            $this->selectedPatron = Patron::with(['patronType'])
                ->where('school_id', $code)
                ->orWhere('rfid_tag', $code)
                ->first();
        } catch (\Exception $e) {
            $this->selectedPatron = null;
        }
    }

    public function updatedAccessionInput(): void
    {
        try {
            $code = trim(strip_tags($this->accessionInput));
            if (empty($code)) {
                $this->selectedAccession = null;
                return;
            }

            $this->selectedAccession = Accession::with(['catalog.author'])
                ->where('accession_number', $code)
                ->first();
        } catch (\Exception $e) {
            $this->selectedAccession = null;
        }
    }

    public function updatedReturnCondition(): void
    {
        if ($this->returnCondition === 'good') {
            $this->manualFineAmount = 0.00;
        } elseif (in_array($this->returnCondition, ['damaged', 'lost'])) {
            if ($this->manualFineAmount < 50.00) {
                $this->manualFineAmount = 50.00;
            }
        }
    }

    public function openPaymentModal(): void
    {
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
    }

    public function payFine(int $loanId): void
    {
        try {
            $loan = Circulation::where('id', $loanId)
                ->where('status', 'returned')
                ->where('is_paid', false)
                ->whereIn('condition', ['damaged', 'lost'])
                ->first();

            if (! $loan) {
                $this->dispatch('toast', message: 'No unpaid returned record with damaged or lost condition found for this item.', type: 'error');
                return;
            }

            $this->openEditPaymentModal($loan->id);
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'An error occurred locating the record.', type: 'error');
        }
    }

    public function openEditPaymentModal(int $loanId): void
    {
        try {
            $loan = Circulation::where('id', $loanId)
                ->where('status', 'returned')
                ->where('is_paid', false)
                ->whereIn('condition', ['damaged', 'lost'])
                ->first();

            if (! $loan) {
                $this->dispatch('toast', message: 'Record does not meet the specified criteria.', type: 'error');
                return;
            }

            $this->editingLoanId = $loan->id;
            $this->editFineAmount = max(50.00, (float) $loan->fine_amount);
            $this->editReceiptNumber = $loan->receipt_number ?? '';
            $this->isEditPaid = (bool) $loan->is_paid;
            $this->showEditPaymentModal = true;
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Unable to open payment editor.', type: 'error');
        }
    }

    public function closeEditPaymentModal(): void
    {
        $this->showEditPaymentModal = false;
        $this->reset(['editingLoanId', 'editFineAmount', 'editReceiptNumber', 'isEditPaid']);
        $this->resetErrorBag();
    }

    public function updatePayment(): void
    {
        if (! $this->editingLoanId) {
            return;
        }

        $fields = ['editFineAmount', 'editReceiptNumber'];
        $this->cleanFields($fields);

        $this->validate([
            'editFineAmount' => 'required|numeric|min:50.00',
            'editReceiptNumber' => $this->isEditPaid ? 'required|string|max:50' : 'nullable|string|max:50',
        ], [], [
            'editFineAmount' => 'Fine Amount',
            'editReceiptNumber' => 'Receipt Number',
        ]);

        try {
            $loan = Circulation::where('id', $this->editingLoanId)
                ->where('status', 'returned')
                ->where('is_paid', false)
                ->whereIn('condition', ['damaged', 'lost'])
                ->first();

            if ($loan) {
                $loan->update([
                    'fine_amount' => $this->editFineAmount,
                    'is_paid' => $this->editFineAmount > 0 ? $this->isEditPaid : true,
                    'receipt_number' => $this->isEditPaid ? $this->editReceiptNumber : null,
                ]);

                $this->dispatch('toast', message: 'Payment details updated successfully.', type: 'success');
            } else {
                $this->dispatch('toast', message: 'Unable to update. Record no longer valid.', type: 'error');
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Database error while saving payment update.', type: 'error');
        }

        $this->closeEditPaymentModal();
    }

    public function processCheckout(): void
    {
        $fields = ['patronInput', 'accessionInput'];
        $this->cleanFields($fields);

        $this->validate([
            'patronInput' => 'required|string',
            'accessionInput' => 'required|string',
        ], [], [
            'patronInput' => 'Borrower',
            'accessionInput' => 'Accession',
        ]);

        try {
            $patronCode = trim($this->patronInput);
            $patron = Patron::with('patronType')
                ->where('school_id', $patronCode)
                ->orWhere('rfid_tag', $patronCode)
                ->first();

            if (! $patron) {
                $this->dispatch('toast', message: 'Patron not found.', type: 'error');
                return;
            }

            if (strtolower($patron->status ?? '') !== 'active') {
                $this->dispatch('toast', message: 'Patron account is not active.', type: 'error');
                return;
            }

            $accessionCode = trim($this->accessionInput);
            $accession = Accession::with('catalog')->where('accession_number', $accessionCode)->first();
            if (! $accession) {
                $this->dispatch('toast', message: 'Accession / Book not found.', type: 'error');
                return;
            }

            if (strtolower($accession->status ?? '') !== 'available') {
                $this->dispatch('toast', message: "Item is currently {$accession->status}.", type: 'error');
                return;
            }

            $loanDays = 7;
            $maxBorrowLimit = 3;

            $patronTypeName = strtolower($patron->patronType?->name ?? '');
            $isStudent = str_contains($patronTypeName, 'student');

            if ($isStudent) {
                $policy = CirculationPolicy::where('patron_type_id', $patron->patron_type_id)
                    ->where('asset_type_id', $accession->catalog?->asset_type_id)
                    ->where('is_active', true)
                    ->first();

                if ($policy) {
                    $loanDays = $policy->loan_duration_days ?? 7;
                    $maxBorrowLimit = $policy->max_borrow_limit ?? 3;
                }
            }

            $activeBorrowCount = Circulation::where('patron_id', $patron->id)
                ->where('status', 'borrowed')
                ->count();

            if ($activeBorrowCount >= $maxBorrowLimit) {
                $this->dispatch('toast', message: "Borrowing limit reached ({$maxBorrowLimit} items max).", type: 'error');
                return;
            }

            DB::transaction(function () use ($patron, $accession, $loanDays, $isStudent) {
                $now = now();
                $dueDate = $isStudent ? $now->copy()->addDays($loanDays) : null;

                Circulation::create([
                    'patron_id' => $patron->id,
                    'accession_id' => $accession->id,
                    'user_id' => auth()->id(),
                    'borrowed_at' => $now,
                    'due_at' => $dueDate,
                    'fine_amount' => 0.00,
                    'is_paid' => true,
                    'receipt_number' => null,
                    'status' => 'borrowed',
                    'condition' => 'good',
                ]);

                $accession->update(['status' => 'On Loan']);
            });

            $this->reset(['accessionInput', 'selectedAccession']);
            $this->dispatch('toast', message: 'Book issued successfully!', type: 'success');
        } catch (QueryException $e) {
            Log::error('Database error during checkout: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Database error occurred during checkout transaction.', type: 'error');
        } catch (\Exception $e) {
            Log::error('Unexpected error during checkout: ' . $e->getMessage());
            $this->dispatch('toast', message: 'An unexpected error occurred during checkout.', type: 'error');
        }
    }

    public function quickInspect(string $accessionNumber): void
    {
        $this->activeMode = 'checkin';
        $this->returnAccessionInput = trim(strip_tags($accessionNumber));
        $this->inspectReturn();
    }

    public function inspectReturn(): void
    {
        try {
            $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

            $code = strtolower(trim(strip_tags($this->returnAccessionInput)));
            if (empty($code)) {
                return;
            }

            $accession = Accession::where('accession_number', $likeOperator, $code)->first();
            if (! $accession) {
                $this->dispatch('toast', message: 'Accession number not found.', type: 'error');
                $this->inspectedLoan = null;
                return;
            }

            $activeLoan = Circulation::with(['patron.patronType', 'accession.catalog', 'accession.acquisition'])
                ->where('accession_id', $accession->id)
                ->where('status', 'borrowed')
                ->first();

            if (! $activeLoan) {
                $this->dispatch('toast', message: 'This item is not currently recorded as borrowed.', type: 'error');
                $this->inspectedLoan = null;
                return;
            }

            $this->inspectedLoan = $activeLoan;
            $this->returnCondition = 'good';
            $this->manualFineAmount = 0.00;
            $this->isPaidNow = false;
            $this->checkinReceiptNumber = '';

            $now = now();
            $this->overdueFineAmount = 0.00;

            if ($activeLoan->due_at && $now->greaterThan($activeLoan->due_at)) {
                $patronTypeName = strtolower($activeLoan->patron?->patronType?->name ?? '');
                $isStudent = str_contains($patronTypeName, 'student');

                if ($isStudent) {
                    $finePerDay = 5.00;
                    $maxFine = 100.00;

                    $policy = CirculationPolicy::where('patron_type_id', $activeLoan->patron?->patron_type_id)
                        ->where('asset_type_id', $activeLoan->accession?->catalog?->asset_type_id)
                        ->where('is_active', true)
                        ->first();

                    if ($policy) {
                        $finePerDay = $policy->fine_per_day ?? 5.00;
                        $maxFine = $policy->max_fine_amount ?? 100.00;
                    }

                    $daysOverdue = max(0, Carbon::parse($activeLoan->due_at)->diffInDays($now));
                    $this->overdueFineAmount = min($daysOverdue * $finePerDay, $maxFine);
                }
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Error inspecting the return item.', type: 'error');
        }
    }

    public function processCheckin(): void
    {
        if (! $this->inspectedLoan) {
            return;
        }

        // Apply validation rules ensuring damaged or lost conditions enforce a minimum fine of 50.00
        $rules = [
            'manualFineAmount' => in_array($this->returnCondition, ['damaged', 'lost']) ? 'required|numeric|min:50.00' : 'nullable|numeric|min:0',
        ];

        $totalFine = (float) $this->overdueFineAmount + (float) $this->manualFineAmount;

        if ($totalFine > 0 && $this->isPaidNow) {
            $rules['checkinReceiptNumber'] = 'required|string|max:50';
        }

        $this->validate($rules, [], [
            'manualFineAmount' => 'Fine Amount',
            'checkinReceiptNumber' => 'Receipt Number',
        ]);

        try {
            $loan = $this->inspectedLoan;
            $accession = $loan->accession;

            DB::transaction(function () use ($loan, $accession, $totalFine) {
                $accessionStatus = 'Available';

                if ($this->returnCondition === 'damaged') {
                    $accessionStatus = 'under maintenance';
                } elseif (in_array($this->returnCondition, ['lost', 'missing'])) {
                    $accessionStatus = 'dumped';
                }

                $loan->update([
                    'returned_at' => now(),
                    'fine_amount' => $totalFine,
                    'is_paid' => $totalFine > 0 ? $this->isPaidNow : true,
                    'receipt_number' => $this->isPaidNow ? $this->checkinReceiptNumber : null,
                    'status' => 'returned',
                    'condition' => $this->returnCondition,
                ]);

                $accession?->update([
                    'status' => $accessionStatus,
                    'condition' => ucfirst($this->returnCondition),
                ]);
            });

            $this->cancelInspection();
            $this->dispatch('toast', message: 'Item checked in successfully.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Error processing check-in.', type: 'error');
        }
    }

    public function quickCheckin(string $accessionNumber): void
    {
        try {
            $cleanAcc = trim(strip_tags($accessionNumber));
            $loan = Circulation::with('accession')
                ->whereHas('accession', fn ($q) => $q->where('accession_number', $cleanAcc))
                ->where('status', 'borrowed')
                ->latest('borrowed_at')
                ->first();

            if (! $loan) {
                $this->dispatch('toast', message: 'Active loan item not found.', type: 'error');
                return;
            }

            DB::transaction(function () use ($loan) {
                $loan->update([
                    'returned_at' => now(),
                    'status' => 'returned',
                ]);

                $loan->accession?->update(['status' => 'Available']);
            });

            $this->dispatch('toast', message: 'Item returned directly.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Error processing quick check-in.', type: 'error');
        }
    }

    public function cancelInspection(): void
    {
        $this->reset([
            'returnAccessionInput',
            'inspectedLoan',
            'overdueFineAmount',
            'manualFineAmount',
            'returnCondition',
            'isPaidNow',
            'checkinReceiptNumber',
            'showPaymentModal',
        ]);
    }

    protected function getFilteredLoansQuery(): Builder
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return Circulation::with(['patron.patronType', 'accession.catalog', 'user'])
            ->when($this->filterStatus === 'borrowed', fn ($q) => $q->where('status', 'borrowed'))
            ->when($this->filterStatus === 'returned', fn ($q) => $q->where('status', 'returned'))
            ->when($this->filterStatus === 'overdue', fn ($q) => $q->where('status', 'borrowed')->where('due_at', '<', now()))
            ->when($this->search, function ($q) use ($likeOperator) {
                $searchTerm = "%{$this->search}%";
                $q->where(function ($query) use ($likeOperator, $searchTerm) {
                    $query->where('receipt_number', $likeOperator, $searchTerm)
                        ->orWhereHas('patron', function ($patronQ) use ($likeOperator, $searchTerm) {
                            $patronQ->where('school_id', $likeOperator, $searchTerm)
                                ->orWhere('first_name', $likeOperator, $searchTerm)
                                ->orWhere('last_name', $likeOperator, $searchTerm);
                        })->orWhereHas('accession', function ($accQ) use ($likeOperator, $searchTerm) {
                            $accQ->where('accession_number', $likeOperator, $searchTerm)
                                ->orWhereHas('catalog', fn ($catQ) => $catQ->where('title', $likeOperator, $searchTerm));
                        });
                });
            })
            ->latest('borrowed_at');
    }

    public function exportExcel(): BinaryFileResponse
    {
        $fileName = 'circulation_report_' . now()->format('Y_m_d_His') . '.xlsx';
        return Excel::download(new CirculationsExport($this->search, $this->filterStatus), $fileName);
    }

    public function exportPdf(): StreamedResponse
    {
        $fileName = 'circulation_report_' . now()->format('Y_m_d_His') . '.pdf';

        return response()->streamDownload(function () {
            $loans = $this->getFilteredLoansQuery()->orderBy('borrowed_at', 'desc')->get();

            // Calculate total paid fines from the retrieved dataset
            $totalPaidFineSum = $loans->filter(function ($loan) {
                return $loan->returned_at && $loan->is_paid === true;
            })->sum('fine_amount');

            $pdf = Pdf::loadView('pdf.circulations-report', [
                'activeLoans'    => $loans,
                'filterStatus'   => $this->filterStatus,
                'totalPaidFineSum' => $totalPaidFineSum,
            ])->setPaper('a4', 'landscape');

            echo $pdf->output();
        }, $fileName);
    }

    #[Layout('components.layouts.app')]
    #[Title('Circulation Desk')]
    public function render()
    {
        return view('livewire.circulations', [
            'activeLoans' => $this->getFilteredLoansQuery()->paginate(10),
        ]);
    }
}
