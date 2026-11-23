<?php // app/Livewire/Circulations.php

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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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
use Throwable;

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
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
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
        if (! in_array($this->filterStatus, ['borrowed', 'overdue', 'returned', 'all'], true)) {
            $this->filterStatus = 'borrowed';
        }
        $this->resetPage();
    }

    public function updatedFilterCondition(): void
    {
        $allowedConditions = ['all', 'good', 'minor damaged', 'severe damaged', 'lost'];
        if (! in_array($this->filterCondition, $allowedConditions, true)) {
            $this->filterCondition = 'all';
        }
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
        if (! in_array($mode, ['checkout', 'checkin', 'active_loans'], true)) {
            return;
        }

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

            $this->selectedPatron = Patron::query()
                ->with(['patronType'])
                ->where(function (Builder $q) use ($code, $op): void {
                    $q->where('school_id', $op, $code)
                        ->orWhere('rfid_tag', $op, $code);
                })
                ->first();

            if ($this->selectedPatron) {
                $this->isTimedIn = PatronLog::query()
                    ->where('patron_id', $this->selectedPatron->id)
                    ->whereDate('log_date', now()->toDateString())
                    ->whereNull('time_out')
                    ->exists();
            } else {
                $this->isTimedIn = true;
            }
        } catch (Throwable $e) {
            Log::error('Error looking up patron during checkout: ' . $e->getMessage());
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

            $this->selectedAccession = Accession::query()
                ->with(['catalog.author', 'catalog.assetType', 'catalog.generalReference', 'catalog.publisher'])
                ->where('accession_number', $this->likeOperator(), $code)
                ->first();
        } catch (Throwable $e) {
            Log::error('Error looking up accession during checkout: ' . $e->getMessage());
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
            $loan = Circulation::query()
                ->where('id', $loanId)
                ->whereIn('status', ['returned', 'lost'])
                ->where('is_paid', false)
                ->first();

            if (! $loan) {
                $this->dispatch('toast', message: 'NO UNPAID FINE RECORD FOUND FOR THIS PATRON.', type: 'error');

                return;
            }

            $this->openEditPaymentModal($loan->id);
        } catch (Throwable $e) {
            Log::error('Error locating fine record: ' . $e->getMessage());
            $this->dispatch('toast', message: 'AN ERROR OCCURRED LOCATING THE RECORD.', type: 'error');
        }
    }

    public function openEditPaymentModal(int $loanId): void
    {
        try {
            $loan = Circulation::query()
                ->where('id', $loanId)
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
            $this->resetErrorBag();
            $this->showEditPaymentModal = true;
        } catch (Throwable $e) {
            Log::error('Error opening payment editor: ' . $e->getMessage());
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

        $this->cleanFields(['editFineAmount', 'editReceiptNumber']);

        $rules = [
            'editFineAmount'    => ['required', 'numeric', 'min:5.00', 'max:99999999.99'],
            'editReceiptNumber' => $this->isEditPaid
                ? ['required', 'string', 'max:50']
                : ['nullable', 'string', 'max:50'],
        ];

        $messages = [
            'editFineAmount.min'         => 'Fine amount must be at least ₱5.00.',
            'editReceiptNumber.required' => 'Receipt number is required when marking a fine as paid.',
        ];

        $attributes = [
            'editFineAmount'    => 'Fine Amount',
            'editReceiptNumber' => 'Receipt Number',
        ];

        $this->validate($rules, $messages, $attributes);

        try {
            $updated = false;

            DB::transaction(function () use (&$updated): void {
                $loan = Circulation::query()
                    ->where('id', $this->editingLoanId)
                    ->whereIn('status', ['returned', 'lost'])
                    ->where('is_paid', false)
                    ->lockForUpdate()
                    ->first();

                if (! $loan) {
                    return;
                }

                $fineAmount = round((float) $this->editFineAmount, 2);
                $isPaid = $fineAmount > 0 ? (bool) $this->isEditPaid : true;

                $loan->update([
                    'fine_amount'    => $fineAmount,
                    'is_paid'        => $isPaid,
                    'receipt_number' => $isPaid && trim((string) $this->editReceiptNumber) !== ''
                        ? strtoupper(trim((string) $this->editReceiptNumber))
                        : null,
                ]);

                $updated = true;
            });

            if ($updated) {
                $this->dispatch('toast', message: 'PAYMENT DETAILS UPDATED SUCCESSFULLY.', type: 'success');
            } else {
                $this->dispatch('toast', message: 'UNABLE TO UPDATE. RECORD NO LONGER VALID.', type: 'error');
            }
        } catch (Throwable $e) {
            Log::error('Database error while saving payment update: ' . $e->getMessage());
            $this->dispatch('toast', message: 'DATABASE ERROR WHILE SAVING PAYMENT UPDATE.', type: 'error');
        }

        $this->closeEditPaymentModal();
    }

    public function processCheckout(): void
    {
        $this->cleanFields(['patronInput', 'accessionInput']);

        $rules = [
            'patronInput'    => ['required', 'string', 'max:100'],
            'accessionInput' => ['required', 'string', 'max:100'],
        ];

        $messages = [
            'patronInput.required'    => 'Please enter or scan a patron ID or RFID tag.',
            'accessionInput.required' => 'Please enter or scan an accession number.',
        ];

        $attributes = [
            'patronInput'    => 'Patron',
            'accessionInput' => 'Accession',
        ];

        $this->validate($rules, $messages, $attributes);

        try {
            $op = $this->likeOperator();
            $patronCode = trim($this->patronInput);
            $accessionCode = trim($this->accessionInput);

            $patron = Patron::query()
                ->with(['patronType'])
                ->where(function (Builder $q) use ($patronCode, $op): void {
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

            $hasTimedIn = PatronLog::query()
                ->where('patron_id', $patron->id)
                ->whereDate('log_date', now()->toDateString())
                ->whereNull('time_out')
                ->exists();

            if (! $hasTimedIn) {
                $this->dispatch('toast', message: 'BORROWING FAILED: PATRON HAS NOT TIMED IN TODAY OR IS ALREADY TIMED OUT.', type: 'error');

                return;
            }

            $previewAccession = Accession::query()
                ->where('accession_number', $op, $accessionCode)
                ->first();

            if (! $previewAccession) {
                $this->dispatch('toast', message: 'ACCESSION / ITEM NOT FOUND.', type: 'error');

                return;
            }

            if (strtolower($previewAccession->status ?? '') !== 'available') {
                $this->dispatch('toast', message: 'ITEM IS CURRENTLY ' . strtoupper((string) $previewAccession->status) . '.', type: 'error');

                return;
            }

            $isStudent = $this->isStudentPatron($patron);
            $loanDays = 7;

            $circulationRecord = null;
            $errorMessage = null;

            DB::transaction(function () use ($patron, $previewAccession, $isStudent, &$loanDays, &$circulationRecord, &$errorMessage): void {
                $lockedAccession = Accession::query()
                    ->where('id', $previewAccession->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedAccession || strtolower($lockedAccession->status ?? '') !== 'available') {
                    $errorMessage = 'ITEM IS CURRENTLY ' . strtoupper((string) ($lockedAccession?->status ?? 'UNAVAILABLE')) . '.';

                    return;
                }

                if ($isStudent) {
                    $policy = PolicyLoanLimit::query()
                        ->where('patron_type_id', $patron->patron_type_id)
                        ->first();

                    if (! $policy) {
                        $errorMessage = 'BORROWING FAILED: POLICY LOAN LIMIT HAS NOT BEEN SET FOR THIS STUDENT PATRON TYPE.';

                        return;
                    }

                    $loanDays = (int) ($policy->loan_duration_days ?? 7);
                    $maxBorrowLimit = (int) ($policy->max_borrow_limit ?? 3);

                    $activeBorrowCount = Circulation::query()
                        ->where('patron_id', $patron->id)
                        ->where('status', 'borrowed')
                        ->lockForUpdate()
                        ->count();

                    if ($activeBorrowCount >= $maxBorrowLimit) {
                        $errorMessage = "BORROWING LIMIT REACHED ({$maxBorrowLimit} ITEMS MAX).";

                        return;
                    }
                }

                $now = now();
                $dueDate = $now->copy()->addDays($loanDays);

                $circulationRecord = Circulation::query()->create([
                    'patron_id'      => $patron->id,
                    'accession_id'   => $lockedAccession->id,
                    'user_id'        => Auth::id(),
                    'borrowed_at'    => $now,
                    'due_at'         => $dueDate,
                    'fine_amount'    => 0.00,
                    'is_paid'        => true,
                    'receipt_number' => null,
                    'status'         => 'borrowed',
                    'condition'      => 'good',
                ]);

                $lockedAccession->update(['status' => 'on loan']);
            });

            if ($errorMessage) {
                $this->dispatch('toast', message: $errorMessage, type: 'error');

                return;
            }

            if ($patron->email && $circulationRecord) {
                try {
                    Mail::to($patron->email)->send(new ItemBorrowedMail($circulationRecord));
                } catch (Throwable $mailException) {
                    Log::error('Failed to send borrowed item email: ' . $mailException->getMessage());
                }
            }

            $this->reset(['accessionInput', 'selectedAccession']);
            $this->dispatch('toast', message: 'ITEM ISSUED SUCCESSFULLY.', type: 'success');
        } catch (QueryException $e) {
            Log::error('Database error during checkout: ' . $e->getMessage());
            $this->dispatch('toast', message: 'DATABASE ERROR OCCURRED DURING CHECKOUT TRANSACTION.', type: 'error');
        } catch (Throwable $e) {
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

            $accession = Accession::query()
                ->where('accession_number', $op, $code)
                ->first();

            if (! $accession) {
                $this->dispatch('toast', message: 'ACCESSION NUMBER NOT FOUND.', type: 'error');
                $this->inspectedLoan = null;

                return;
            }

            $activeLoan = Circulation::query()
                ->with(['patron.patronType', 'accession.catalog.assetType', 'accession.acquisition'])
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
                ? CirculationPenalty::query()->where('asset_type_id', $assetTypeId)->first()
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
        } catch (Throwable $e) {
            Log::error('Error inspecting return item: ' . $e->getMessage());
            $this->dispatch('toast', message: 'ERROR INSPECTING THE RETURN ITEM.', type: 'error');
        }
    }

    public function processCheckin(): void
    {
        if (! $this->inspectedLoan) {
            return;
        }

        $minAssessment = match ($this->returnCondition) {
            'minor damaged'          => 5.00,
            'severe damaged', 'lost' => 50.00,
            default                  => 0.00,
        };

        $totalFine = $this->totalCalculatedFine;

        $rules = [
            'returnCondition'      => ['required', 'string', 'in:good,minor damaged,severe damaged,lost'],
            'assessmentFeeAmount'  => $this->isConditionFeeRequired($this->returnCondition)
                ? ['required', 'numeric', "min:{$minAssessment}", 'max:99999999.99']
                : ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'checkinReceiptNumber' => ($totalFine > 0 && $this->isPaidNow)
                ? ['required', 'string', 'max:50']
                : ['nullable', 'string', 'max:50'],
        ];

        $messages = [
            'returnCondition.in'          => 'Selected return condition is invalid.',
            'assessmentFeeAmount.min'     => "Assessment fee must be at least ₱{$minAssessment}.",
            'checkinReceiptNumber.required' => 'Receipt number is required when settling a fine.',
        ];

        $attributes = [
            'returnCondition'      => 'Return Condition',
            'assessmentFeeAmount'  => 'Assessment Fee',
            'checkinReceiptNumber' => 'Receipt Number',
        ];

        $this->validate($rules, $messages, $attributes);

        try {
            $loanId = $this->inspectedLoan->id;

            DB::transaction(function () use ($loanId, $totalFine): void {
                $loan = Circulation::query()
                    ->with('accession')
                    ->where('id', $loanId)
                    ->where('status', 'borrowed')
                    ->lockForUpdate()
                    ->first();

                if (! $loan) {
                    return;
                }

                $accession = $loan->accession
                    ? Accession::query()->where('id', $loan->accession->id)->lockForUpdate()->first()
                    : null;

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
                    'is_paid'        => $totalFine > 0 ? (bool) $this->isPaidNow : true,
                    'receipt_number' => ($totalFine > 0 && $this->isPaidNow)
                        ? strtoupper(trim($this->checkinReceiptNumber))
                        : null,
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
        } catch (Throwable $e) {
            Log::error('Error processing check-in: ' . $e->getMessage());
            $this->dispatch('toast', message: 'ERROR PROCESSING CHECK-IN.', type: 'error');
        }
    }

    public function quickCheckin(string $accessionNumber): void
    {
        try {
            $cleanAcc = trim(strip_tags($accessionNumber));
            if ($cleanAcc === '') {
                return;
            }

            $checkedIn = false;

            DB::transaction(function () use ($cleanAcc, &$checkedIn): void {
                $loan = Circulation::query()
                    ->with('accession')
                    ->whereHas('accession', fn (Builder $q) => $q->where('accession_number', $this->likeOperator(), $cleanAcc))
                    ->where('status', 'borrowed')
                    ->latest('borrowed_at')
                    ->lockForUpdate()
                    ->first();

                if (! $loan) {
                    return;
                }

                $accession = $loan->accession
                    ? Accession::query()->where('id', $loan->accession->id)->lockForUpdate()->first()
                    : null;

                $loan->update([
                    'returned_at' => now(),
                    'status'      => 'returned',
                ]);

                $accession?->update(['status' => 'available']);
                $checkedIn = true;
            });

            if ($checkedIn) {
                $this->dispatch('toast', message: 'ITEM RETURNED DIRECTLY.', type: 'success');
            } else {
                $this->dispatch('toast', message: 'ACTIVE LOAN ITEM NOT FOUND.', type: 'error');
            }
        } catch (Throwable $e) {
            Log::error('Error processing quick check-in: ' . $e->getMessage());
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
        $term = '%' . addcslashes(trim($this->search), '%_\\') . '%';

        return Circulation::query()
            ->with(['patron.patronType', 'accession.catalog', 'user'])
            ->when($this->filterStatus === 'borrowed', function (Builder $q): void {
                $q->where('status', 'borrowed')
                    ->where(function (Builder $sub): void {
                        $sub->whereNull('due_at')
                            ->orWhere('due_at', '>=', now());
                    });
            })
            ->when($this->filterStatus === 'overdue', function (Builder $q): void {
                $q->where('status', 'borrowed')
                    ->whereNotNull('due_at')
                    ->where('due_at', '<', now());
            })
            ->when($this->filterStatus === 'returned', function (Builder $q): void {
                $q->whereIn('status', ['returned', 'lost']);
            })
            ->when(in_array($this->filterCondition, $allowedConditions, true), function (Builder $q): void {
                $condition = $this->filterCondition;
                $q->where(function (Builder $condQuery) use ($condition): void {
                    $condQuery->where('condition', $condition)
                        ->orWhereHas('accession', fn (Builder $accQ) => $accQ->where('condition', $condition));
                });
            })
            ->when(trim($this->search) !== '', function (Builder $q) use ($likeOperator, $term): void {
                $q->where(function (Builder $query) use ($likeOperator, $term): void {
                    $query->where('receipt_number', $likeOperator, $term)
                        ->orWhereHas('patron', function (Builder $patronQ) use ($likeOperator, $term): void {
                            $patronQ->where('school_id', $likeOperator, $term)
                                ->orWhere('first_name', $likeOperator, $term)
                                ->orWhere('last_name', $likeOperator, $term);
                        })->orWhereHas('accession', function (Builder $accQ) use ($likeOperator, $term): void {
                            $accQ->where('accession_number', $likeOperator, $term)
                                ->orWhereHas('catalog', fn (Builder $catQ) => $catQ->where('title', $likeOperator, $term));
                        });
                });
            })
            ->latest('borrowed_at');
    }

    public function exportExcel(): BinaryFileResponse|StreamedResponse|null
    {
        try {
            $fileName = 'circulation_report_' . now()->format('Y_m_d_His') . '.xlsx';

            return Excel::download(new CirculationsExport(trim($this->search), $this->filterStatus), $fileName);
        } catch (Throwable $e) {
            Log::error('Error exporting circulations Excel: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to export circulation Excel report.', type: 'error');

            return null;
        }
    }

    public function exportPdf(): ?StreamedResponse
    {
        try {
            $fileName = 'circulation_report_' . now()->format('Y_m_d_His') . '.pdf';
            $loans = $this->getFilteredLoansQuery()->orderBy('borrowed_at', 'desc')->get();

            $totalPaidFineSum = $loans->filter(function ($loan) {
                return $loan->returned_at && (bool) $loan->is_paid === true;
            })->sum('fine_amount');

            $pdf = Pdf::loadView('pdf.circulations-report', [
                'activeLoans'      => $loans,
                'filterStatus'     => $this->filterStatus,
                'filterCondition'  => $this->filterCondition,
                'totalPaidFineSum' => $totalPaidFineSum,
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(function () use ($pdf): void {
                echo $pdf->output();
            }, $fileName);
        } catch (Throwable $e) {
            Log::error('Error exporting circulations PDF: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to export circulation PDF report.', type: 'error');

            return null;
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Circulation Desk')]
    public function render()
    {
        $activeLoans = new LengthAwarePaginator([], 0, 10);

        try {
            $activeLoans = $this->getFilteredLoansQuery()->paginate(10);
        } catch (Throwable $e) {
            Log::error('Error rendering circulations table: ' . $e->getMessage());
        }

        return view('livewire.circulations', [
            'activeLoans' => $activeLoans,
        ]);
    }
}
