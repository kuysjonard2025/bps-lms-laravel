<?php

namespace App\Livewire;

use App\Models\Accession;
use App\Models\Circulation;
use App\Models\CirculationPolicy;
use App\Models\Patron;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Circulations extends Component
{
    use WithPagination;

    public string $activeMode = 'checkout'; // 'checkout', 'checkin', 'active_loans'

    // Checkout Form Inputs
    public string $patronInput = '';
    public string $accessionInput = '';

    // Selected Entities State (Checkout)
    public ?Patron $selectedPatron = null;
    public ?Accession $selectedAccession = null;

    // Checkin Inspection State
    public string $returnAccessionInput = '';
    public ?Circulation $inspectedLoan = null;
    public string $returnCondition = 'Good'; // 'New', 'Good', 'Fair', 'Damaged', 'Missing' (or 'Lost' from UI)
    public float $overdueFineAmount = 0.00;
    public float $manualFineAmount = 0.00;
    public string $fineReason = '';

    // Active Loans / History Search & Filters
    public string $search = '';
    public string $filterStatus = 'borrowed'; // 'all', 'borrowed', 'returned', 'overdue'

    public function updatedPatronInput(): void
    {
        $code = trim($this->patronInput);
        if (empty($code)) {
            $this->selectedPatron = null;
            return;
        }

        $this->selectedPatron = Patron::with(['patronType', 'gradeLevel', 'section'])
            ->where('patron_id', $code)
            ->first();
    }

    public function updatedAccessionInput(): void
    {
        $code = trim($this->accessionInput);
        if (empty($code)) {
            $this->selectedAccession = null;
            return;
        }

        $this->selectedAccession = Accession::with(['catalog.assetType', 'catalog.author'])
            ->where('accession_number', $code)
            ->first();
    }

    /**
     * Helper to generate unique transaction numbers for payments / penalties
     */
    private function generateTransactionNumber(): string
    {
        return 'TRX-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd');
    }

    // ------------------------------------------------------------------
    // BORROW / CHECKOUT PROCESS
    // ------------------------------------------------------------------
    public function processCheckout(): void
    {
        $this->validate([
            'patronInput'    => 'required|string',
            'accessionInput' => 'required|string',
        ]);

        $patron = Patron::where('patron_id', trim($this->patronInput))->first();
        if (! $patron) {
            $this->dispatch('toast', message: 'Patron not found.', type: 'error');
            return;
        }

        if ($patron->status !== 'active') {
            $this->dispatch('toast', message: 'Patron account is not active.', type: 'error');
            return;
        }

        $accession = Accession::with('catalog')->where('accession_number', trim($this->accessionInput))->first();
        if (! $accession) {
            $this->dispatch('toast', message: 'Accession / Book not found.', type: 'error');
            return;
        }

        if ($accession->status !== 'Available') {
            $this->dispatch('toast', message: "Item is currently {$accession->status}.", type: 'error');
            return;
        }

        // Fetch Circulation Policy
        $policy = CirculationPolicy::where('patron_type_id', $patron->patron_type_id)
            ->where('asset_type_id', $accession->catalog->asset_type_id)
            ->where('is_active', true)
            ->first();

        $loanDays = $policy ? $policy->loan_duration_days : 7;
        $maxBorrowLimit = $policy ? $policy->max_borrow_limit : 3;

        // Check active borrowings limit
        $activeBorrowCount = Circulation::where('patron_id', $patron->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->count();

        if ($activeBorrowCount >= $maxBorrowLimit) {
            $this->dispatch('toast', message: "Borrowing limit reached ({$maxBorrowLimit} items max).", type: 'error');
            return;
        }

        DB::transaction(function () use ($patron, $accession, $loanDays) {
            $now = now();
            $dueDate = $now->copy()->addDays($loanDays);

            Circulation::create([
                'patron_id'          => $patron->id,
                'accession_id'       => $accession->id,
                'processed_by'       => auth()->id(),
                'borrowed_at'        => $now,
                'due_at'             => $dueDate,
                'transaction_number' => null,
                'fine_amount'        => 0.00,
                'status'             => 'borrowed',
            ]);

            $accession->update(['status' => 'On Loan']);
        });

        $this->reset(['accessionInput', 'selectedAccession']);
        $this->dispatch('toast', message: 'Book issued successfully!', type: 'success');
        $this->dispatch('play-sound', type: 'out');
    }

    // ------------------------------------------------------------------
    // RETURN / CHECKIN INSPECTION PROCESS
    // ------------------------------------------------------------------
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

        $activeLoan = Circulation::with(['patron.patronType', 'accession.catalog.assetType'])
            ->where('accession_id', $accession->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->first();

        if (! $activeLoan) {
            $this->dispatch('toast', message: 'This item is not currently recorded as borrowed.', type: 'error');
            $this->inspectedLoan = null;
            return;
        }

        $this->inspectedLoan = $activeLoan;
        $this->returnCondition = $accession->condition ?? 'Good';
        $this->manualFineAmount = 0.00;

        // Calculate Overdue Fine from policy
        $now = now();
        $this->overdueFineAmount = 0.00;

        if ($now->greaterThan($activeLoan->due_at)) {
            $policy = CirculationPolicy::where('patron_type_id', $activeLoan->patron->patron_type_id)
                ->where('asset_type_id', $activeLoan->accession->catalog->asset_type_id)
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

        $loan = $this->inspectedLoan;
        $accession = $loan->accession;

        DB::transaction(function () use ($loan, $accession) {
            $now = now();
            $totalFine = (float)$this->overdueFineAmount + (float)$this->manualFineAmount;
            $transactionNumber = $totalFine > 0 ? $this->generateTransactionNumber() : null;

            // Default Check-In Statuses
            $circulationStatus = 'returned';
            $accessionStatus = 'Available';

            // Valid DB Enum values for condition: ['New', 'Good', 'Fair', 'Damaged', 'Missing']
            $conditionValue = in_array($this->returnCondition, ['New', 'Good', 'Fair', 'Damaged', 'Missing'])
                ? $this->returnCondition
                : 'Good';

            if ($this->returnCondition === 'Damaged') {
                $accessionStatus = 'Damaged';
            } elseif ($this->returnCondition === 'Lost' || $this->returnCondition === 'Missing') {
                $circulationStatus = 'lost';
                $accessionStatus = 'Lost';      // Valid accession status enum option
                $conditionValue = 'Missing';   // Valid accession condition enum option
            }

            $loan->update([
                'returned_at'        => $now,
                'fine_amount'        => $totalFine,
                'transaction_number' => $transactionNumber,
                'status'             => $circulationStatus,
            ]);

            $accession->update([
                'status'    => $accessionStatus,
                'condition' => $conditionValue,
            ]);
        });

        $this->reset(['returnAccessionInput', 'inspectedLoan', 'overdueFineAmount', 'manualFineAmount', 'returnCondition', 'fineReason']);
        $this->dispatch('toast', message: 'Item checked in and condition recorded successfully.', type: 'success');
        $this->dispatch('play-sound', type: 'in');
    }

    public function cancelInspection(): void
    {
        $this->reset(['returnAccessionInput', 'inspectedLoan', 'overdueFineAmount', 'manualFineAmount', 'returnCondition', 'fineReason']);
    }

    // ------------------------------------------------------------------
    // RENDER VIEW
    // ------------------------------------------------------------------
    #[Layout('components.layouts.app')]
    #[Title('Circulation Desk')]
    public function render()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        $loans = Circulation::with(['patron', 'accession.catalog'])
            ->when($this->filterStatus === 'borrowed', fn ($q) => $q->whereIn('status', ['borrowed', 'overdue']))
            ->when($this->filterStatus === 'returned', fn ($q) => $q->whereIn('status', ['returned', 'lost']))
            ->when($this->filterStatus === 'overdue', fn ($q) => $q->where('status', 'overdue')->orWhere(function ($sub) {
                $sub->whereNull('returned_at')->where('due_at', '<', now());
            }))
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($query) use ($likeOperator) {
                    $query->whereHas('patron', function ($patronQ) use ($likeOperator) {
                        $patronQ->where('patron_id', $likeOperator, "%{$this->search}%")
                            ->orWhere('first_name', $likeOperator, "%{$this->search}%")
                            ->orWhere('last_name', $likeOperator, "%{$this->search}%");
                    })->orWhereHas('accession', function ($accQ) use ($likeOperator) {
                        $accQ->where('accession_number', $likeOperator, "%{$this->search}%")
                            ->orWhereHas('catalog', fn ($catQ) => $catQ->where('title', $likeOperator, "%{$this->search}%"));
                    });
                });
            })
            ->latest('borrowed_at')
            ->paginate(10);

        return view('livewire.circulations', [
            'activeLoans' => $loans,
        ]);
    }
}
