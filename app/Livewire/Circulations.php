<?php

namespace App\Livewire;

use App\Exports\CirculationsExport;
use App\Models\Accession;
use App\Models\Circulation;
use App\Models\CirculationPolicy;
use App\Models\Patron;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Circulations extends Component
{
    use WithPagination;

    public string $activeMode = 'checkout'; // 'checkout', 'checkin', 'active_loans'

    // Modal Control State
    public bool $showPaymentModal = false;
    public bool $showEditPaymentModal = false;

    // Checkout Form Inputs & Selected State
    public string $patronInput = '';
    public string $accessionInput = '';
    public ?Patron $selectedPatron = null;
    public ?Accession $selectedAccession = null;
    public bool $isTimedIn = true; // Kiosk check-in verification flag

    // Checkin / Inspection State
    public string $returnAccessionInput = '';
    public ?Circulation $inspectedLoan = null;
    public string $returnCondition = 'good'; // 'good', 'damaged', 'lost'
    public float $overdueFineAmount = 0.00;
    public float $manualFineAmount = 0.00;
    public bool $isPaidNow = false;
    public string $checkinReceiptNumber = '';

    // Payment Edit State (For Returned, Unpaid & Damaged/Lost records)
    public ?int $editingLoanId = null;
    public float $editFineAmount = 0.00;
    public ?string $editReceiptNumber = '';
    public bool $isEditPaid = false;

    // Active Loans / History Search & Filters
    public string $search = '';
    public string $filterStatus = 'borrowed'; // 'all', 'borrowed', 'returned', 'overdue'

    public function updatedSearch(): void
    {
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
        $code = trim($this->patronInput);
        if (empty($code)) {
            $this->selectedPatron = null;
            return;
        }

        $this->selectedPatron = Patron::with(['patronType'])
            ->where('school_id', $code)
            ->orWhere('rfid_tag', $code)
            ->first();
    }

    public function updatedAccessionInput(): void
    {
        $code = trim($this->accessionInput);
        if (empty($code)) {
            $this->selectedAccession = null;
            return;
        }

        $this->selectedAccession = Accession::with(['catalog.author'])
            ->where('accession_number', $code)
            ->first();
    }

    public function updatedReturnCondition(): void
    {
        if ($this->returnCondition === 'good') {
            $this->manualFineAmount = 0.00;
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

    // ------------------------------------------------------------------
    // EDIT PAYMENT MODAL METHODS & ACTION HANDLERS
    // Restricted strictly to: Returned + Unpaid + (Damaged OR Lost)
    // ------------------------------------------------------------------

    /**
     * Action called by the Blade template button: wire:click="payFine('ACC_NUM')"
     */
    public function payFine(string $accessionNumber): void
    {
        $loan = Circulation::whereHas('accession', fn ($q) => $q->where('accession_number', $accessionNumber))
            ->where('status', 'returned')
            ->where('is_paid', false)
            ->whereIn('condition', ['damaged', 'lost'])
            ->latest('borrowed_at')
            ->first();

        if (! $loan) {
            $this->dispatch('toast', message: 'No unpaid returned record with damaged or lost condition found for this item.', type: 'error');
            return;
        }

        $this->openEditPaymentModal($loan->id);
    }

    public function openEditPaymentModal(int $loanId): void
    {
        $loan = Circulation::where('id', $loanId)
            ->where('status', 'returned')
            ->where('is_paid', false)
            ->whereIn('condition', ['damaged', 'lost'])
            ->first();

        if (! $loan) {
            $this->dispatch('toast', message: 'Only returned items with unpaid fines and damaged/lost condition can be edited.', type: 'error');
            return;
        }

        $this->editingLoanId = $loan->id;
        $this->editFineAmount = (float) $loan->fine_amount;
        $this->editReceiptNumber = $loan->receipt_number ?? '';
        $this->isEditPaid = (bool) $loan->is_paid;
        $this->showEditPaymentModal = true;
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

        $this->validate([
            'editFineAmount' => 'required|numeric|min:0',
            'editReceiptNumber' => $this->isEditPaid ? 'required|string|max:50' : 'nullable|string|max:50',
        ]);

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
            $this->dispatch('toast', message: 'Unable to update. Record does not meet the specified criteria.', type: 'error');
        }

        $this->closeEditPaymentModal();
    }

    // ------------------------------------------------------------------
    // BORROW / CHECKOUT PROCESS
    // ------------------------------------------------------------------
    public function processCheckout(): void
    {
        $this->validate([
            'patronInput' => 'required|string',
            'accessionInput' => 'required|string',
        ]);

        $patron = Patron::where('school_id', trim($this->patronInput))
            ->orWhere('rfid_tag', trim($this->patronInput))
            ->first();

        if (! $patron) {
            $this->dispatch('toast', message: 'Patron not found.', type: 'error');
            return;
        }

        if (strtolower($patron->status ?? '') !== 'active') {
            $this->dispatch('toast', message: 'Patron account is not active.', type: 'error');
            return;
        }

        $accession = Accession::with('catalog')->where('accession_number', trim($this->accessionInput))->first();
        if (! $accession) {
            $this->dispatch('toast', message: 'Accession / Book not found.', type: 'error');
            return;
        }

        if (strtolower($accession->status ?? '') !== 'available') {
            $this->dispatch('toast', message: "Item is currently {$accession->status}.", type: 'error');
            return;
        }

        // Fetch Circulation Policy
        $policy = CirculationPolicy::where('patron_type_id', $patron->patron_type_id)
            ->where('asset_type_id', $accession->catalog?->asset_type_id)
            ->where('is_active', true)
            ->first();

        $loanDays = $policy ? $policy->loan_duration_days : 7;
        $maxBorrowLimit = $policy ? $policy->max_borrow_limit : 3;

        // Check active borrowings limit
        $activeBorrowCount = Circulation::where('patron_id', $patron->id)
            ->where('status', 'borrowed')
            ->count();

        if ($activeBorrowCount >= $maxBorrowLimit) {
            $this->dispatch('toast', message: "Borrowing limit reached ({$maxBorrowLimit} items max).", type: 'error');
            return;
        }

        DB::transaction(function () use ($patron, $accession, $loanDays) {
            $now = now();
            $dueDate = $now->copy()->addDays($loanDays);

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
    }

    // ------------------------------------------------------------------
    // RETURN / CHECKIN INSPECTION PROCESS
    // ------------------------------------------------------------------

    /**
     * Action called by the Blade template button: wire:click="quickInspect('ACC_NUM')"
     */
    public function quickInspect(string $accessionNumber): void
    {
        $this->activeMode = 'checkin';
        $this->returnAccessionInput = $accessionNumber;
        $this->inspectReturn();
    }

    public function inspectReturn(): void
    {
        $code = trim($this->returnAccessionInput);
        if (empty($code)) {
            return;
        }

        $accession = Accession::where('accession_number', $code)->first();
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

        // Calculate Overdue Fine from policy
        $now = now();
        $this->overdueFineAmount = 0.00;

        if ($now->greaterThan($activeLoan->due_at)) {
            $policy = CirculationPolicy::where('patron_type_id', $activeLoan->patron?->patron_type_id)
                ->where('asset_type_id', $activeLoan->accession?->catalog?->asset_type_id)
                ->first();

            $graceDays = $policy ? $policy->grace_period_days : 0;
            $finePerDay = $policy ? $policy->fine_per_day : 5.00;
            $maxFine = $policy ? $policy->max_fine_amount : 100.00;

            $daysOverdue = max(0, Carbon::parse($activeLoan->due_at)->diffInDays($now) - $graceDays);
            $this->overdueFineAmount = min($daysOverdue * $finePerDay, $maxFine);
        }
    }

    public function processCheckin(): void
    {
        if (! $this->inspectedLoan) {
            return;
        }

        $totalFine = (float) $this->overdueFineAmount + (float) $this->manualFineAmount;

        if ($totalFine > 0 && $this->isPaidNow) {
            $this->validate([
                'checkinReceiptNumber' => 'required|string|max:50',
            ]);
        }

        $loan = $this->inspectedLoan;
        $accession = $loan->accession;

        DB::transaction(function () use ($loan, $accession, $totalFine) {
            $now = now();
            $accessionStatus = 'Available';

            if ($this->returnCondition === 'damaged') {
                $accessionStatus = 'under maintenance';
            } elseif ($this->returnCondition === 'lost' || $this->returnCondition === 'missing') {
                $accessionStatus = 'dumped';
            }

            $loan->update([
                'returned_at' => $now,
                'fine_amount' => $totalFine,
                'is_paid' => $totalFine > 0 ? $this->isPaidNow : true,
                'receipt_number' => $this->isPaidNow ? $this->checkinReceiptNumber : null,
                'status' => 'returned',
                'condition' => $this->returnCondition,
            ]);

            $accession->update([
                'status' => $accessionStatus,
                'condition' => ucfirst($this->returnCondition),
            ]);
        });

        $this->cancelInspection();
        $this->dispatch('toast', message: 'Item checked in successfully.', type: 'success');
    }

    /**
     * Accepts string $accessionNumber from blade wire:click="quickCheckin('...')"
     */
    public function quickCheckin(string $accessionNumber): void
    {
        $loan = Circulation::with('accession')
            ->whereHas('accession', fn ($q) => $q->where('accession_number', $accessionNumber))
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

    // ------------------------------------------------------------------
    // EXPORT HANDLERS & QUERY HELPER
    // ------------------------------------------------------------------
    protected function getFilteredLoansQuery(): Builder
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return Circulation::with(['patron', 'accession.catalog', 'user'])
            ->when($this->filterStatus === 'borrowed', fn ($q) => $q->where('status', 'borrowed'))
            ->when($this->filterStatus === 'returned', fn ($q) => $q->where('status', 'returned'))
            ->when($this->filterStatus === 'overdue', fn ($q) => $q->where('status', 'borrowed')->where('due_at', '<', now()))
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($query) use ($likeOperator) {
                    $query->where('receipt_number', $likeOperator, "%{$this->search}%")
                        ->orWhereHas('patron', function ($patronQ) use ($likeOperator) {
                            $patronQ->where('school_id', $likeOperator, "%{$this->search}%")
                                ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                                ->orWhere('last_name', $likeOperator, "%{$this->search}%");
                        })->orWhereHas('accession', function ($accQ) use ($likeOperator) {
                            $accQ->where('accession_number', $likeOperator, "%{$this->search}%")
                                ->orWhereHas('catalog', fn ($catQ) => $catQ->where('title', $likeOperator, "%{$this->search}%"));
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
        $loans = $this->getFilteredLoansQuery()->get();

        $pdf = Pdf::loadView('pdf.circulations-report', [
            'activeLoans'  => $loans,
            'filterStatus' => $this->filterStatus,
        ])->setPaper('a4', 'landscape');

        $fileName = 'circulation_report_' . now()->format('Y_m_d_His') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $fileName);
    }

    // ------------------------------------------------------------------
    // RENDER VIEW
    // ------------------------------------------------------------------
    #[Layout('components.layouts.app')]
    #[Title('Circulation Desk')]
    public function render()
    {
        return view('livewire.circulations', [
            'activeLoans' => $this->getFilteredLoansQuery()->paginate(10),
        ]);
    }
}
