<?php

namespace App\Livewire;

use App\Exports\CirculationsExport;
use App\Livewire\Helpers\SanitizesInputs;
use App\Mail\ItemBorrowedMail;
use App\Models\Accession;
use App\Models\Circulation;
use App\Models\CirculationPenalty;
use App\Models\Patron;
use App\Models\PatronLog;
use App\Models\PolicyLoanLimit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
    public float $assessmentFeeAmount = 0.00;
    public bool $isPaidNow = false;
    public string $checkinReceiptNumber = '';
    public bool $exceededLostThreshold = false;
    public int $policyLostAfterDays = 14;
    public float $policyDailyRate = 0.00;
    public float $inspectedUnitPrice = 0.00;

    // Payment Edit State
    public ?int $editingLoanId = null;
    public float $editFineAmount = 0.00;
    public ?string $editReceiptNumber = '';
    public bool $isEditPaid = false;

    // Active Loans / History Search & Filters
    public string $search = '';
    public string $filterStatus = 'borrowed';
    public string $filterCondition = 'all';

    public function mount(): void
    {
        $this->hydrate();
    }

    public function hydrate(): void
    {
        $this->assessmentFeeAmount = is_numeric($this->assessmentFeeAmount)
            ? (float) $this->assessmentFeeAmount
            : 0.00;
        $this->overdueFineAmount = is_numeric($this->overdueFineAmount)
            ? (float) $this->overdueFineAmount
            : 0.00;
        $this->inspectedUnitPrice = is_numeric($this->inspectedUnitPrice)
            ? (float) $this->inspectedUnitPrice
            : 0.00;
        $this->policyDailyRate = is_numeric($this->policyDailyRate)
            ? (float) $this->policyDailyRate
            : 0.00;
        $this->editFineAmount = is_numeric($this->editFineAmount)
            ? (float) $this->editFineAmount
            : 0.00;
    }

    public function updatedAssessmentFeeAmount(mixed $value): void
    {
        if ($value === '' || $value === null || ! is_numeric($value)) {
            $this->assessmentFeeAmount = 0.00;
            return;
        }

        $this->assessmentFeeAmount = round((float) $value, 2);
    }

    private function likeOperator(): string
    {
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }

    private function isStudentPatron(?Patron $patron): bool
    {
        if (! $patron) {
            return false;
        }

        $typeName = strtolower(trim($patron->patronType?->name ?? $patron->type ?? ''));

        return $typeName === 'student';
    }

    private function requiresUnitCostAddition(string $condition): bool
    {
        return in_array($condition, ['severe damaged', 'lost'], true);
    }

    private function isConditionFeeRequired(string $condition): bool
    {
        return in_array($condition, ['minor damaged', 'severe damaged', 'lost'], true);
    }

    public function getSuggestedMinorDamagedFeeProperty(): float
    {
        $price = (float) $this->inspectedUnitPrice;

        if ($price <= 0) {
            return 10.00;
        }

        $computed = max(5.00, round($price * 0.25, 2));

        return round(min($price, $computed), 2);
    }

    public function getConditionFineTotalProperty(): float
    {
        if ($this->returnCondition === 'good') {
            return 0.00;
        }

        $baseAssessment = (float) $this->assessmentFeeAmount;
        $unitCostAddition = $this->requiresUnitCostAddition($this->returnCondition)
            ? (float) $this->inspectedUnitPrice
            : 0.00;

        return round($baseAssessment + $unitCostAddition, 2);
    }

    public function getTotalCalculatedFineProperty(): float
    {
        return round((float) $this->overdueFineAmount + $this->conditionFineTotal, 2);
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCondition(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterStatus = 'borrowed';
        $this->filterCondition = 'all';
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
            if ($code === '') {
                $this->selectedPatron = null;
                $this->isTimedIn = true;
                return;
            }

            $op = $this->likeOperator();

            $this->selectedPatron = Patron::with(['patronType'])
                ->where(function ($q) use ($code, $op) {
                    $q->where('school_id', $op, $code)
                        ->orWhere('rfid_tag', $op, $code);
                })
                ->first();

            if ($this->selectedPatron) {
                $this->isTimedIn = PatronLog::where('patron_id', $this->selectedPatron->id)
                    ->where('log_date', now()->toDateString())
                    ->whereNull('time_out')
                    ->exists();
            } else {
                $this->isTimedIn = true;
            }
        } catch (\Exception $e) {
            $this->selectedPatron = null;
            $this->isTimedIn = true;
        }
    }

    public function updatedAccessionInput(): void
    {
        try {
            $code = trim(strip_tags($this->accessionInput));
            if ($code === '') {
                $this->selectedAccession = null;
                return;
            }

            $this->selectedAccession = Accession::with(['catalog.author', 'catalog.assetType', 'catalog.generalReference'])
                ->where('accession_number', $this->likeOperator(), $code)
                ->first();
        } catch (\Exception $e) {
            $this->selectedAccession = null;
        }
    }

    public function updatedReturnCondition(): void
    {
        if ($this->returnCondition === 'good') {
            $this->assessmentFeeAmount = 0.00;
            return;
        }

        if ($this->returnCondition === 'minor damaged') {
            $this->assessmentFeeAmount = $this->suggestedMinorDamagedFee;
            return;
        }

        $this->assessmentFeeAmount = 50.00;
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
                ->whereIn('status', ['returned', 'lost'])
                ->where('is_paid', false)
                ->first();

            if (! $loan) {
                $this->dispatch('toast', message: 'NO UNPAID FINE RECORD FOUND FOR THIS PATRON.', type: 'error');
                return;
            }

            $this->openEditPaymentModal($loan->id);
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'AN ERROR OCCURRED LOCATING THE RECORD.', type: 'error');
        }
    }

    public function openEditPaymentModal(int $loanId): void
    {
        try {
            $loan = Circulation::where('id', $loanId)
                ->whereIn('status', ['returned', 'lost'])
                ->where('is_paid', false)
                ->first();

            if (! $loan) {
                $this->dispatch('toast', message: 'RECORD DOES NOT MEET PAYMENT CRITERIA.', type: 'error');
                return;
            }

            $this->editingLoanId = $loan->id;
            $this->editFineAmount = max(5.00, (float) $loan->fine_amount);
            $this->editReceiptNumber = $loan->receipt_number ?? '';
            $this->isEditPaid = (bool) $loan->is_paid;
            $this->showEditPaymentModal = true;
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'UNABLE TO OPEN PAYMENT EDITOR.', type: 'error');
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
            'editFineAmount'    => 'required|numeric|min:5.00',
            'editReceiptNumber' => $this->isEditPaid ? 'required|string|max:50' : 'nullable|string|max:50',
        ], [], [
            'editFineAmount'    => 'Fine Amount',
            'editReceiptNumber' => 'Receipt Number',
        ]);

        try {
            $loan = Circulation::where('id', $this->editingLoanId)
                ->whereIn('status', ['returned', 'lost'])
                ->where('is_paid', false)
                ->first();

            if ($loan) {
                $loan->update([
                    'fine_amount'    => $this->editFineAmount,
                    'is_paid'        => $this->editFineAmount > 0 ? $this->isEditPaid : true,
                    'receipt_number' => $this->isEditPaid ? strtoupper(trim((string) $this->editReceiptNumber)) : null,
                ]);

                $this->dispatch('toast', message: 'PAYMENT DETAILS UPDATED SUCCESSFULLY.', type: 'success');
            } else {
                $this->dispatch('toast', message: 'UNABLE TO UPDATE. RECORD NO LONGER VALID.', type: 'error');
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'DATABASE ERROR WHILE SAVING PAYMENT UPDATE.', type: 'error');
        }

        $this->closeEditPaymentModal();
    }

    public function processCheckout(): void
    {
        $fields = ['patronInput', 'accessionInput'];
        $this->cleanFields($fields);

        $this->validate([
            'patronInput'    => 'required|string',
            'accessionInput' => 'required|string',
        ], [], [
            'patronInput'    => 'Patron',
            'accessionInput' => 'Accession',
        ]);

        try {
            $op = $this->likeOperator();
            $patronCode = trim($this->patronInput);

            $patron = Patron::with(['patronType'])
                ->where(function ($q) use ($patronCode, $op) {
                    $q->where('school_id', $op, $patronCode)
                        ->orWhere('rfid_tag', $op, $patronCode);
                })
                ->first();

            if (! $patron) {
                $this->dispatch('toast', message: 'PATRON NOT FOUND.', type: 'error');
                return;
            }

            if (strtolower($patron->status ?? '') !== 'active') {
                $this->dispatch('toast', message: 'PATRON ACCOUNT IS NOT ACTIVE.', type: 'error');
                return;
            }

            $hasTimedIn = PatronLog::where('patron_id', $patron->id)
                ->where('log_date', now()->toDateString())
                ->whereNull('time_out')
                ->exists();

            if (! $hasTimedIn) {
                $this->dispatch('toast', message: 'BORROWING FAILED: PATRON HAS NOT TIMED IN TODAY OR IS ALREADY TIMED OUT.', type: 'error');
                return;
            }

            $accessionCode = trim($this->accessionInput);
            $accession = Accession::with('catalog')
                ->where('accession_number', $op, $accessionCode)
                ->first();

            if (! $accession) {
                $this->dispatch('toast', message: 'ACCESSION / ITEM NOT FOUND.', type: 'error');
                return;
            }

            if (strtolower($accession->status ?? '') !== 'available') {
                $this->dispatch('toast', message: 'ITEM IS CURRENTLY ' . strtoupper((string) $accession->status) . '.', type: 'error');
                return;
            }

            $isStudent = $this->isStudentPatron($patron);
            $loanDays = 7;
            $maxBorrowLimit = 3;

            if ($isStudent) {
                $policy = PolicyLoanLimit::where('patron_type_id', $patron->patron_type_id)->first();

                if (! $policy) {
                    $this->dispatch('toast', message: 'BORROWING FAILED: POLICY LOAN LIMIT HAS NOT BEEN SET FOR THIS STUDENT PATRON TYPE.', type: 'error');
                    return;
                }

                $loanDays = (int) ($policy->loan_duration_days ?? 7);
                $maxBorrowLimit = (int) ($policy->max_borrow_limit ?? 3);

                $activeBorrowCount = Circulation::where('patron_id', $patron->id)
                    ->where('status', 'borrowed')
                    ->count();

                if ($activeBorrowCount >= $maxBorrowLimit) {
                    $this->dispatch('toast', message: "BORROWING LIMIT REACHED ({$maxBorrowLimit} ITEMS MAX).", type: 'error');
                    return;
                }
            }

            $circulationRecord = null;

            DB::transaction(function () use ($patron, $accession, $loanDays, &$circulationRecord) {
                $now = now();
                $dueDate = $now->copy()->addDays($loanDays);

                $circulationRecord = Circulation::create([
                    'patron_id'      => $patron->id,
                    'accession_id'   => $accession->id,
                    'user_id'        => auth()->id(),
                    'borrowed_at'    => $now,
                    'due_at'         => $dueDate,
                    'fine_amount'    => 0.00,
                    'is_paid'        => true,
                    'receipt_number' => null,
                    'status'         => 'borrowed',
                    'condition'      => 'good',
                ]);

                $accession->update(['status' => 'on loan']);
            });

            if ($patron->email && $circulationRecord) {
                Mail::to($patron->email)->send(new ItemBorrowedMail($circulationRecord));
            }

            $this->reset(['accessionInput', 'selectedAccession']);
            $this->dispatch('toast', message: 'ITEM ISSUED SUCCESSFULLY AND EMAIL SENT.', type: 'success');
        } catch (QueryException $e) {
            Log::error('Database error during checkout: ' . $e->getMessage());
            $this->dispatch('toast', message: 'DATABASE ERROR OCCURRED DURING CHECKOUT TRANSACTION.', type: 'error');
        } catch (\Exception $e) {
            Log::error('Unexpected error during checkout: ' . $e->getMessage());
            $this->dispatch('toast', message: 'AN UNEXPECTED ERROR OCCURRED DURING CHECKOUT.', type: 'error');
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
            $op = $this->likeOperator();
            $code = trim(strip_tags($this->returnAccessionInput));

            if ($code === '') {
                return;
            }

            $accession = Accession::where('accession_number', $op, $code)->first();
            if (! $accession) {
                $this->dispatch('toast', message: 'ACCESSION NUMBER NOT FOUND.', type: 'error');
                $this->inspectedLoan = null;
                return;
            }

            $activeLoan = Circulation::with(['patron.patronType', 'accession.catalog.assetType', 'accession.acquisition'])
                ->where('accession_id', $accession->id)
                ->where('status', 'borrowed')
                ->first();

            if (! $activeLoan) {
                $this->dispatch('toast', message: 'THIS ITEM IS NOT CURRENTLY RECORDED AS BORROWED.', type: 'error');
                $this->inspectedLoan = null;
                return;
            }

            $this->inspectedLoan = $activeLoan;
            $this->returnCondition = 'good';
            $this->assessmentFeeAmount = 0.00;
            $this->isPaidNow = false;
            $this->checkinReceiptNumber = '';
            $this->overdueFineAmount = 0.00;
            $this->exceededLostThreshold = false;
            $this->policyLostAfterDays = 14;
            $this->policyDailyRate = 0.00;

            $unitPrice = (float) (
                $activeLoan->accession?->acquisition?->unit_cost
                ?? $activeLoan->accession?->acquisition?->unit_price
                ?? 0
            );
            $this->inspectedUnitPrice = $unitPrice;

            $assetTypeId = $activeLoan->accession?->catalog?->asset_type_id;
            $penalty = $assetTypeId
                ? CirculationPenalty::where('asset_type_id', $assetTypeId)->first()
                : null;

            $lostAfterDays = max(1, (int) ($penalty?->lost_after_days ?? $penalty?->max_days ?? 14));
            $this->policyLostAfterDays = $lostAfterDays;

            $dailyRate = $unitPrice > 0
                ? ($unitPrice / $lostAfterDays)
                : (float) ($penalty?->fine_per_day ?? 5.00);
            $this->policyDailyRate = round($dailyRate, 2);

            $now = now();

            if ($activeLoan->due_at && $now->greaterThan($activeLoan->due_at) && $this->isStudentPatron($activeLoan->patron)) {
                $daysOverdue = max(0, (int) Carbon::parse($activeLoan->due_at)->diffInDays($now));
                $billableDays = min($daysOverdue, $lostAfterDays);
                $this->overdueFineAmount = round($billableDays * $dailyRate, 2);

                if ($daysOverdue > $lostAfterDays) {
                    $this->exceededLostThreshold = true;
                }
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'ERROR INSPECTING THE RETURN ITEM.', type: 'error');
        }
    }

    public function processCheckin(): void
    {
        if (! $this->inspectedLoan) {
            return;
        }

        $this->validate([
            'returnCondition' => 'required|string|in:good,minor damaged,severe damaged,lost',
        ]);

        $minAssessment = match ($this->returnCondition) {
            'minor damaged'          => 5.00,
            'severe damaged', 'lost' => 50.00,
            default                  => 0.00,
        };

        $rules = [
            'assessmentFeeAmount' => $this->isConditionFeeRequired($this->returnCondition)
                ? "required|numeric|min:{$minAssessment}"
                : 'nullable|numeric|min:0',
        ];

        $totalFine = $this->totalCalculatedFine;

        if ($totalFine > 0 && $this->isPaidNow) {
            $rules['checkinReceiptNumber'] = 'required|string|max:50';
        }

        $this->validate($rules, [], [
            'assessmentFeeAmount'  => 'Assessment Fee',
            'checkinReceiptNumber' => 'Receipt Number',
        ]);

        try {
            $loan = $this->inspectedLoan;
            $accession = $loan->accession;

            DB::transaction(function () use ($loan, $accession, $totalFine) {
                $isMinorDamage = $this->returnCondition === 'minor damaged';
                $isLostCondition = in_array($this->returnCondition, ['severe damaged', 'lost'], true)
                    || ($this->exceededLostThreshold && ! $isMinorDamage && $this->returnCondition === 'good');

                $loanStatus = 'returned';
                $loanCondition = $this->returnCondition;
                $accessionStatus = 'available';

                if ($isLostCondition) {
                    $loanStatus = 'lost';
                    $loanCondition = 'lost';
                    $accessionStatus = 'dumped';
                } elseif ($isMinorDamage) {
                    $loanStatus = 'returned';
                    $accessionStatus = 'under maintenance';
                }

                $loan->update([
                    'returned_at'    => now(),
                    'fine_amount'    => $totalFine,
                    'is_paid'        => $totalFine > 0 ? $this->isPaidNow : true,
                    'receipt_number' => $this->isPaidNow ? strtoupper(trim($this->checkinReceiptNumber)) : null,
                    'status'         => $loanStatus,
                    'condition'      => $loanCondition,
                ]);

                $accession?->update([
                    'status'    => $accessionStatus,
                    'condition' => $loanCondition,
                ]);
            });

            $this->cancelInspection();
            $this->dispatch('toast', message: 'ITEM CHECKED IN SUCCESSFULLY.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'ERROR PROCESSING CHECK-IN.', type: 'error');
        }
    }

    public function quickCheckin(string $accessionNumber): void
    {
        try {
            $cleanAcc = trim(strip_tags($accessionNumber));
            $loan = Circulation::with('accession')
                ->whereHas('accession', fn ($q) => $q->where('accession_number', $this->likeOperator(), $cleanAcc))
                ->where('status', 'borrowed')
                ->latest('borrowed_at')
                ->first();

            if (! $loan) {
                $this->dispatch('toast', message: 'ACTIVE LOAN ITEM NOT FOUND.', type: 'error');
                return;
            }

            DB::transaction(function () use ($loan) {
                $loan->update([
                    'returned_at' => now(),
                    'status'      => 'returned',
                ]);

                $loan->accession?->update(['status' => 'available']);
            });

            $this->dispatch('toast', message: 'ITEM RETURNED DIRECTLY.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'ERROR PROCESSING QUICK CHECK-IN.', type: 'error');
        }
    }

    public function cancelInspection(): void
    {
        $this->reset([
            'returnAccessionInput',
            'inspectedLoan',
            'overdueFineAmount',
            'assessmentFeeAmount',
            'returnCondition',
            'isPaidNow',
            'checkinReceiptNumber',
            'exceededLostThreshold',
            'policyLostAfterDays',
            'policyDailyRate',
            'inspectedUnitPrice',
            'showPaymentModal',
        ]);
    }

    protected function getFilteredLoansQuery(): Builder
    {
        $likeOperator = $this->likeOperator();
        $allowedConditions = ['good', 'minor damaged', 'severe damaged', 'lost'];

        return Circulation::with(['patron.patronType', 'accession.catalog', 'user'])
            ->when($this->filterStatus === 'borrowed', function ($q) {
                $q->where('status', 'borrowed')
                    ->where(function ($sub) {
                        $sub->whereNull('due_at')
                            ->orWhere('due_at', '>=', now());
                    });
            })
            ->when($this->filterStatus === 'overdue', function ($q) {
                $q->where('status', 'borrowed')
                    ->whereNotNull('due_at')
                    ->where('due_at', '<', now());
            })
            ->when($this->filterStatus === 'returned', function ($q) {
                $q->whereIn('status', ['returned', 'lost']);
            })
            ->when(in_array($this->filterCondition, $allowedConditions, true), function ($q) {
                $condition = $this->filterCondition;
                $q->where(function ($condQuery) use ($condition) {
                    $condQuery->where('condition', $condition)
                        ->orWhereHas('accession', fn ($accQ) => $accQ->where('condition', $condition));
                });
            })
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

            $totalPaidFineSum = $loans->filter(function ($loan) {
                return $loan->returned_at && $loan->is_paid === true;
            })->sum('fine_amount');

            $pdf = Pdf::loadView('pdf.circulations-report', [
                'activeLoans'      => $loans,
                'filterStatus'     => $this->filterStatus,
                'filterCondition'  => $this->filterCondition,
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
