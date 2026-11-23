<?php // resources/views/livewire/circulations.blade.php ?>
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-5 text-content-body">
    {{-- Header Card & Mode Switcher --}}
    <section aria-label="Circulation desk header" class="flex flex-col gap-4 rounded-2xl border border-surface-border bg-surface-card px-6 py-5 shadow-2xs lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-content-heading">Circulations</h1>
            <p class="mt-0.5 text-xs text-content-muted">Manage item borrowing, condition inspections, fines, and loan history records.</p>
        </div>

        <div role="tablist" aria-label="Circulation modes" class="inline-flex flex-wrap items-center gap-1.5 rounded-xl border border-surface-border bg-surface-canvas p-1 self-start lg:self-auto">
            <button
                type="button"
                role="tab"
                aria-selected="{{ $activeMode === 'checkout' ? 'true' : 'false' }}"
                wire:click="switchMode('checkout')"
                wire:target="switchMode"
                class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeMode === 'checkout' ? 'bg-brand-primary text-white shadow-2xs' : 'text-content-body hover:bg-surface-card hover:text-content-heading' }}"
            >
                <x-heroicon-o-plus class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                <span>Borrow</span>
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="{{ $activeMode === 'checkin' ? 'true' : 'false' }}"
                wire:click="switchMode('checkin')"
                wire:target="switchMode"
                class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeMode === 'checkin' ? 'bg-brand-primary text-white shadow-2xs' : 'text-content-body hover:bg-surface-card hover:text-content-heading' }}"
            >
                <x-heroicon-o-arrow-uturn-left class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                <span>Return</span>
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="{{ $activeMode === 'active_loans' ? 'true' : 'false' }}"
                wire:click="switchMode('active_loans')"
                wire:target="switchMode"
                class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeMode === 'active_loans' ? 'bg-brand-primary text-white shadow-2xs' : 'text-content-body hover:bg-surface-card hover:text-content-heading' }}"
            >
                <x-heroicon-o-document-text class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                <span>Loans &amp; History</span>
            </button>
        </div>
    </section>

    {{-- TAB 1: BORROW / CHECK-OUT --}}
    @if ($activeMode === 'checkout')
        <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-2">
            {{-- Step 1: Patron Identification --}}
            <section aria-label="Step 1 patron identification" class="rounded-2xl border border-surface-border bg-surface-card p-6 shadow-2xs space-y-5">
                <div class="flex items-center gap-3 border-b border-surface-border/70 pb-4">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-tint-light text-xs font-bold text-brand-primary shrink-0">1</span>
                    <div>
                        <h2 class="text-sm font-bold text-content-heading">Patron Identification</h2>
                        <p class="text-xs text-content-muted">Scan an RFID card or search by student/employee number.</p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="checkout-patron-input" class="block text-xs font-semibold text-content-heading">
                        Student / Employee # or RFID <span class="text-brand-secondary">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <x-heroicon-o-user wire:loading.remove wire:target="patronInput" class="h-4 w-4 text-content-muted" aria-hidden="true" />
                            <span wire:loading wire:target="patronInput" class="animate-spin h-4 w-4 border-2 border-brand-primary border-t-transparent rounded-full inline-block"></span>
                        </div>
                        <input
                            id="checkout-patron-input"
                            type="text"
                            wire:model.live.debounce.300ms="patronInput"
                            wire:keydown.escape="$set('patronInput', '')"
                            placeholder="Search ID or scan RFID..."
                            class="w-full rounded-xl border border-surface-border bg-surface-canvas py-2.5 pl-10 pr-4 text-xs text-content-heading placeholder:text-content-muted transition focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15"
                        />
                    </div>
                    @error('patronInput') <span class="text-xs text-brand-secondary block mt-1 font-medium">{{ $message }}</span> @enderror
                </div>

                @if ($selectedPatron)
                    @php
                        $middleInitial = $selectedPatron->middle_name ? mb_substr(trim($selectedPatron->middle_name), 0, 1) . '.' : '';
                        $patronName = Str::title(trim("{$selectedPatron->first_name} {$middleInitial} {$selectedPatron->last_name}")) . ($selectedPatron->suffix ? ' ' . strtoupper(trim($selectedPatron->suffix)) : '');
                        $isStudentSelected = strtolower(trim($selectedPatron->patronType?->name ?? '')) === 'student';
                    @endphp
                    <div class="space-y-3 rounded-xl border border-surface-border bg-surface-canvas/70 p-4">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="text-sm font-bold text-content-heading">{{ $patronName }}</span>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center rounded-full bg-brand-tint-light px-2.5 py-0.5 text-[11px] font-semibold text-brand-primary">
                                    {{ Str::title((string) $selectedPatron->status) }}
                                </span>
                                @if ($isTimedIn)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-tint px-2.5 py-0.5 text-[11px] font-semibold text-brand-primary">
                                        <span class="h-1.5 w-1.5 rounded-full bg-brand-primary"></span>
                                        Timed In
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-secondary/15 px-2.5 py-0.5 text-[11px] font-semibold text-brand-secondary">
                                        <span class="h-1.5 w-1.5 rounded-full bg-brand-secondary"></span>
                                        Not Timed In
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 border-t border-surface-border pt-3 text-xs">
                            <div>
                                <span class="text-content-muted">{{ $isStudentSelected ? 'Student #:' : 'Employee #:' }}</span>
                                <span class="font-mono font-semibold text-content-heading ml-1">{{ $selectedPatron->school_id }}</span>
                            </div>
                            <div>
                                <span class="text-content-muted">Type:</span>
                                <span class="font-semibold text-content-heading ml-1">{{ $selectedPatron->patronType?->name ? Str::title($selectedPatron->patronType->name) : 'N/A' }}</span>
                            </div>
                        </div>

                        @if (! $isTimedIn)
                            <div class="rounded-xl border border-brand-secondary/25 bg-brand-secondary/10 px-3 py-2 text-xs text-brand-secondary font-medium">
                                Patron is not timed in at the entrance kiosk today.
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-surface-border bg-surface-canvas/40 py-8 px-4 text-center">
                        <p class="text-xs text-content-muted">Scan an RFID card or enter a school ID to load patron details.</p>
                    </div>
                @endif
            </section>

            {{-- Step 2: Asset Accession Identification --}}
            <section aria-label="Step 2 asset accession identification" class="rounded-2xl border border-surface-border bg-surface-card p-6 shadow-2xs space-y-5">
                <div class="flex items-center gap-3 border-b border-surface-border/70 pb-4">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-tint-light text-xs font-bold text-brand-primary shrink-0">2</span>
                    <div>
                        <h2 class="text-sm font-bold text-content-heading">Asset Accession</h2>
                        <p class="text-xs text-content-muted">Scan item barcode or enter accession number.</p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="checkout-accession-input" class="block text-xs font-semibold text-content-heading">
                        Accession Number <span class="text-brand-secondary">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <x-heroicon-o-book-open wire:loading.remove wire:target="accessionInput" class="h-4 w-4 text-content-muted" aria-hidden="true" />
                            <span wire:loading wire:target="accessionInput" class="animate-spin h-4 w-4 border-2 border-brand-primary border-t-transparent rounded-full inline-block"></span>
                        </div>
                        <input
                            id="checkout-accession-input"
                            type="text"
                            wire:model.live.debounce.300ms="accessionInput"
                            wire:keydown.escape="$set('accessionInput', '')"
                            wire:keydown.enter.prevent
                            placeholder="Enter accession # (e.g. ACC-2026-001-01)..."
                            class="w-full rounded-xl border border-surface-border bg-surface-canvas py-2.5 pl-10 pr-4 text-xs text-content-heading placeholder:text-content-muted transition focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15"
                        />
                    </div>
                    @error('accessionInput') <span class="text-xs text-brand-secondary block mt-1 font-medium">{{ $message }}</span> @enderror
                </div>

                @if ($selectedAccession)
                    @php
                        $isAvailable = strtolower($selectedAccession->status ?? '') === 'available';
                    @endphp
                    <div class="space-y-2.5 rounded-xl border border-surface-border bg-surface-canvas/70 p-4 text-xs">
                        @if ($selectedAccession->catalog?->isbn_issn)
                            <div class="font-mono text-[11px] text-content-muted">{{ $selectedAccession->catalog->isbn_issn }}</div>
                        @endif
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-sm font-bold text-content-heading">{{ $selectedAccession->catalog?->title ? Str::title($selectedAccession->catalog->title) : 'N/A' }}</span>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $isAvailable ? 'bg-brand-tint-light text-brand-primary' : 'bg-brand-secondary/15 text-brand-secondary' }}">
                                {{ Str::title((string) $selectedAccession->status) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pt-1 text-content-body">
                            <div><span class="text-content-muted">Asset Type:</span> <span class="font-medium text-content-heading">{{ $selectedAccession->catalog?->assetType?->name ? Str::title($selectedAccession->catalog->assetType->name) : 'N/A' }}</span></div>
                            <div><span class="text-content-muted">Call Number:</span> <span class="font-mono font-semibold text-brand-primary">{{ Str::upper($selectedAccession->catalog?->call_number) ?: 'N/A' }}</span></div>
                            <div><span class="text-content-muted">Author:</span> <span class="font-medium text-content-heading">{{ $selectedAccession->catalog?->author?->name ? Str::title($selectedAccession->catalog->author->name) : 'N/A' }}</span></div>
                            <div><span class="text-content-muted">Publisher:</span> <span class="font-medium text-content-heading">{{ $selectedAccession->catalog?->publisher?->name ? Str::title($selectedAccession->catalog->publisher->name) : 'N/A' }}</span></div>
                            <div><span class="text-content-muted">Copyright Year:</span> <span class="font-mono text-content-heading">{{ $selectedAccession->catalog?->publication_year ?? 'N/A' }}</span></div>
                            <div><span class="text-content-muted">General Reference:</span> <span class="font-medium text-content-heading">{{ $selectedAccession->catalog?->generalReference?->name ? Str::title($selectedAccession->catalog->generalReference->name) : 'N/A' }}</span></div>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-surface-border bg-surface-canvas/40 py-8 px-4 text-center">
                        <p class="text-xs text-content-muted">Scan or enter an accession number to preview the item.</p>
                    </div>
                @endif

                <button
                    type="button"
                    wire:click="processCheckout"
                    wire:target="processCheckout"
                    wire:loading.attr="disabled"
                    @disabled(! $selectedPatron || ! $selectedAccession || strtolower($selectedAccession->status ?? '') !== 'available' || ! $isTimedIn)
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-primary py-2.5 px-4 text-xs font-semibold text-white shadow-2xs transition hover:bg-brand-primary-hover focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20 disabled:cursor-not-allowed disabled:opacity-45 cursor-pointer"
                >
                    <span wire:loading wire:target="processCheckout" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                    <span>Complete Check-Out</span>
                </button>
            </section>
        </div>
    @endif

    {{-- TAB 2: RETURN / CHECK-IN --}}
    @if ($activeMode === 'checkin')
        <div class="space-y-5">
            <section aria-label="Return accession inspection lookup" class="rounded-2xl border border-surface-border bg-surface-card p-5 shadow-2xs">
                <form wire:submit="inspectReturn" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1 space-y-1.5">
                        <label for="checkin-accession-input" class="block text-xs font-semibold text-content-heading">
                            Accession Barcode or Number <span class="text-brand-secondary">*</span>
                        </label>
                        <input
                            id="checkin-accession-input"
                            type="text"
                            wire:model.live.debounce.300ms="returnAccessionInput"
                            placeholder="Scan or enter accession # to inspect loan..."
                            class="w-full rounded-xl border border-surface-border bg-surface-canvas py-2.5 px-4 text-xs text-content-heading placeholder:text-content-muted transition focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15"
                        />
                        @error('returnAccessionInput') <span class="text-xs text-brand-secondary block mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>
                    <button
                        type="submit"
                        wire:target="inspectReturn"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-primary px-5 py-2.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-brand-primary-hover focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20 cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading wire:target="inspectReturn" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Inspect Loan</span>
                    </button>
                </form>
            </section>

            @if ($inspectedLoan)
                @php
                    $itemPrice = is_numeric($inspectedUnitPrice ?? null) ? (float) $inspectedUnitPrice : 0.00;
                    $safeAssessmentFee = is_numeric($assessmentFeeAmount ?? null) ? (float) $assessmentFeeAmount : 0.00;
                    $safeOverdueFine = is_numeric($overdueFineAmount ?? null) ? (float) $overdueFineAmount : 0.00;
                    $includesUnitCost = in_array($returnCondition, ['severe damaged', 'lost'], true);
                    $conditionFeeTotal = $returnCondition === 'good'
                        ? 0.00
                        : round($safeAssessmentFee + ($includesUnitCost ? $itemPrice : 0.00), 2);
                    $totalFineCalculated = round($safeOverdueFine + $conditionFeeTotal, 2);
                    $minFeeAllowed = $returnCondition === 'minor damaged' ? 5.00 : 50.00;
                    $suggestedMinor = $this->suggestedMinorDamagedFee ?? 0.00;
                    $overdueDays = $inspectedLoan->due_at?->isPast() ? (int) $inspectedLoan->due_at->diffInDays(now()) : 0;
                    $effectiveOverdueDays = min($overdueDays, $policyLostAfterDays ?? 0);
                    $inspectedMiddleInitial = $inspectedLoan->patron?->middle_name ? mb_substr(trim($inspectedLoan->patron->middle_name), 0, 1) . '.' : '';
                    $inspectedPatronName = $inspectedLoan->patron
                        ? Str::title(trim("{$inspectedLoan->patron->first_name} {$inspectedMiddleInitial} {$inspectedLoan->patron->last_name}")) . ($inspectedLoan->patron->suffix ? ' ' . strtoupper(trim($inspectedLoan->patron->suffix)) : '')
                        : 'N/A';
                    $isInspectedStudent = strtolower(trim($inspectedLoan->patron?->patronType?->name ?? '')) === 'student';
                    $assetTypeName = $inspectedLoan->accession?->catalog?->assetType?->name
                        ? Str::title($inspectedLoan->accession->catalog->assetType->name)
                        : 'Item';
                @endphp
                <section aria-label="Return inspection details" class="rounded-2xl border border-surface-border bg-surface-card p-6 shadow-2xs space-y-6">
                    <div class="flex items-center justify-between border-b border-surface-border/70 pb-4">
                        <div>
                            <h2 class="text-base font-bold text-content-heading">Return Inspection</h2>
                            <p class="text-xs text-content-muted mt-0.5">
                                Accession: <span class="font-mono font-semibold text-brand-primary">{{ $inspectedLoan->accession?->accession_number ?: 'N/A' }}</span>
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="cancelInspection"
                            wire:target="cancelInspection"
                            class="rounded-xl border border-surface-border bg-surface-canvas px-3.5 py-1.5 text-xs font-semibold text-content-body hover:text-content-heading cursor-pointer transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                        >Cancel</button>
                    </div>

                    @if ($exceededLostThreshold)
                        <div class="rounded-xl border border-brand-secondary/30 bg-brand-secondary/10 px-4 py-3 text-xs font-medium text-brand-secondary">
                            Item exceeded the {{ $policyLostAfterDays }}-day overdue limit. Daily overdue fine is capped at {{ $policyLostAfterDays }} day(s).
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Left Column: Loan & Fine Summary --}}
                        <div class="space-y-4 text-xs">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 rounded-xl border border-surface-border bg-surface-canvas/70 p-4">
                                <div class="sm:col-span-2">
                                    <span class="text-content-muted">Title:</span>
                                    <span class="font-bold text-content-heading ml-1">{{ $inspectedLoan->accession?->catalog?->title ? Str::title($inspectedLoan->accession->catalog->title) : 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-content-muted">Patron:</span>
                                    <span class="font-semibold text-content-heading ml-1">{{ $inspectedPatronName }}</span>
                                </div>
                                <div>
                                    <span class="text-content-muted">Type:</span>
                                    <span class="font-semibold text-content-heading ml-1">{{ $inspectedLoan->patron?->patronType?->name ? Str::title($inspectedLoan->patron->patronType->name) : 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-content-muted">Borrowed:</span>
                                    <span class="font-mono text-content-heading ml-1">{{ $inspectedLoan->borrowed_at?->format('M d, Y') ?: 'N/A' }}</span>
                                </div>
                                @if ($isInspectedStudent)
                                    <div>
                                        <span class="text-content-muted">Due Date:</span>
                                        <span class="font-mono text-content-heading ml-1">{{ $inspectedLoan->due_at?->format('M d, Y') ?: 'N/A' }}</span>
                                    </div>
                                @endif
                                <div class="{{ $isInspectedStudent ? '' : 'sm:col-span-2' }}">
                                    <span class="text-content-muted">Unit Cost:</span>
                                    <span class="font-mono font-semibold text-brand-primary ml-1">₱{{ number_format($itemPrice, 2) }}</span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-surface-border bg-surface-canvas p-4 space-y-2.5">
                                <div class="text-xs font-bold text-content-heading border-b border-surface-border pb-2">
                                    Fine Calculation ({{ $assetTypeName }})
                                </div>

                                @if ($isInspectedStudent)
                                    <div class="rounded-xl border border-surface-border bg-surface-card p-3 space-y-1 text-[11px] text-content-body">
                                        <div class="font-semibold text-content-heading">Overdue Formula</div>
                                        @if ($itemPrice > 0)
                                            <div>
                                                Daily rate: ₱{{ number_format($itemPrice, 2) }} &divide; {{ $policyLostAfterDays }} days =
                                                <strong class="font-mono text-brand-primary">₱{{ number_format((float) $policyDailyRate, 2) }}/day</strong>
                                            </div>
                                        @else
                                            <div>
                                                Daily rate: <strong class="font-mono text-brand-primary">₱{{ number_format((float) $policyDailyRate, 2) }}/day</strong>
                                            </div>
                                        @endif
                                        <div>
                                            Overdue fine: {{ $effectiveOverdueDays }} billable day(s) &times; ₱{{ number_format((float) $policyDailyRate, 2) }} =
                                            <strong class="font-mono text-content-heading">₱{{ number_format($safeOverdueFine, 2) }}</strong>
                                        </div>
                                        <div class="text-brand-secondary font-medium">
                                            Capped after {{ $policyLostAfterDays }} overdue days.
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-xl border border-surface-border bg-surface-card p-3 text-[11px] text-content-muted">
                                        Non-student patrons do not incur daily overdue fines (₱0.00). Condition fees still apply if damaged or lost.
                                    </div>
                                @endif

                                <div class="flex justify-between">
                                    <span class="text-content-muted">Overdue Days:</span>
                                    <span class="font-mono font-semibold text-content-heading">{{ $overdueDays }} day(s)</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-content-muted">Overdue Fine:</span>
                                    <span class="font-mono font-semibold text-content-heading">₱{{ number_format($safeOverdueFine, 2) }}</span>
                                </div>

                                @if ($returnCondition !== 'good')
                                    <div class="flex justify-between">
                                        <span class="text-content-muted">Assessment Fee:</span>
                                        <span class="font-mono font-semibold text-content-heading">₱{{ number_format($safeAssessmentFee, 2) }}</span>
                                    </div>
                                    @if ($includesUnitCost)
                                        <div class="flex justify-between">
                                            <span class="text-content-muted">Unit Cost ({{ Str::title($returnCondition) }}):</span>
                                            <span class="font-mono font-semibold text-content-heading">₱{{ number_format($itemPrice, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-content-muted">Condition Subtotal:</span>
                                        <span class="font-mono font-semibold text-content-heading">₱{{ number_format($conditionFeeTotal, 2) }}</span>
                                    </div>
                                @endif

                                <div class="flex justify-between pt-2 border-t border-surface-border text-sm font-bold text-content-heading">
                                    <span>Total Calculated Fine:</span>
                                    <span class="font-mono {{ $totalFineCalculated > 0 ? 'text-brand-secondary' : 'text-brand-primary' }}">
                                        ₱{{ number_format($totalFineCalculated, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Condition, Reference & Settlement --}}
                        <div class="space-y-4 text-xs">
                            <div>
                                <label for="return-condition" class="block font-semibold text-content-heading mb-1">
                                    Return Condition <span class="text-brand-secondary">*</span>
                                </label>
                                <select
                                    id="return-condition"
                                    wire:model.live="returnCondition"
                                    class="w-full rounded-xl border border-surface-border bg-surface-canvas py-2.5 px-3.5 text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                                >
                                    <option value="good">Good (₱0.00)</option>
                                    <option value="minor damaged">Minor Damaged (Repairable &bull; Assessment Fee)</option>
                                    <option value="severe damaged">Severe Damaged (Unrepairable &bull; Assessment Fee + Unit Cost)</option>
                                    <option value="lost">Lost (Assessment Fee + Unit Cost)</option>
                                </select>
                                @error('returnCondition') <span class="text-brand-secondary block mt-1 font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="rounded-xl border border-brand-primary/20 bg-brand-tint-light/50 p-3.5 space-y-2 text-[11px]">
                                <div class="font-bold text-content-heading">Condition Assessment Guide</div>
                                <div class="space-y-1.5">
                                    <div class="flex justify-between border-b border-brand-primary/15 pb-1">
                                        <span class="font-semibold text-brand-primary">Good</span>
                                        <span class="font-mono text-content-heading">₱0.00 &bull; Available</span>
                                    </div>
                                    <div class="border-b border-brand-primary/15 pb-1">
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-content-heading">Minor Damaged</span>
                                            <span class="font-mono text-content-heading">~₱{{ number_format((float) $suggestedMinor, 2) }}</span>
                                        </div>
                                        <p class="text-[10px] text-content-muted">Repairable &bull; Scaled for low-cost items &bull; Under maintenance</p>
                                    </div>
                                    <div class="border-b border-brand-primary/15 pb-1">
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-brand-secondary">Severe Damaged</span>
                                            <span class="font-mono text-content-heading">Fee + ₱{{ number_format($itemPrice, 2) }}</span>
                                        </div>
                                        <p class="text-[10px] text-content-muted">Unrepairable &bull; Full unit cost charged &bull; Dumped</p>
                                    </div>
                                    <div>
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-brand-secondary">Lost</span>
                                            <span class="font-mono text-content-heading">Fee + ₱{{ number_format($itemPrice, 2) }}</span>
                                        </div>
                                        <p class="text-[10px] text-content-muted">Assessment fee + full unit cost &bull; Marked lost &bull; Dumped</p>
                                    </div>
                                </div>
                            </div>

                            @if ($returnCondition !== 'good')
                                <div class="space-y-1">
                                    <label for="return-assessment-fee" class="block font-semibold text-content-heading">
                                        Assessment Fee (₱) <span class="text-brand-secondary">*</span>
                                    </label>
                                    <input
                                        id="return-assessment-fee"
                                        type="number"
                                        step="0.01"
                                        min="{{ $minFeeAllowed }}"
                                        wire:model.live.debounce.350ms="assessmentFeeAmount"
                                        placeholder="Enter assessment fee..."
                                        class="w-full rounded-xl border border-surface-border bg-surface-canvas py-2.5 px-3.5 text-xs font-mono text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                                    />
                                    @error('assessmentFeeAmount')
                                        <span class="text-brand-secondary block mt-1 font-medium">{{ $message }}</span>
                                    @enderror
                                    @if ($includesUnitCost)
                                        <p class="text-[11px] text-brand-primary font-medium">
                                            Subtotal: ₱{{ number_format($safeAssessmentFee, 2) }} + ₱{{ number_format($itemPrice, 2) }} = ₱{{ number_format($conditionFeeTotal, 2) }}
                                        </p>
                                    @else
                                        <p class="text-[11px] text-content-muted">
                                            Minimum ₱{{ number_format($minFeeAllowed, 2) }}, capped at unit cost (₱{{ number_format($itemPrice, 2) }}) for low-cost items.
                                        </p>
                                    @endif
                                </div>
                            @endif

                            @if ($totalFineCalculated > 0)
                                <div class="rounded-xl border border-brand-secondary/25 bg-brand-secondary/5 p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-content-heading">Total Fine Assessed</span>
                                        <span class="text-sm font-extrabold font-mono text-brand-secondary">₱{{ number_format($totalFineCalculated, 2) }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            id="isPaidNow"
                                            wire:model.live="isPaidNow"
                                            class="h-4 w-4 rounded border-surface-border text-brand-primary focus:ring-brand-primary cursor-pointer"
                                        />
                                        <label for="isPaidNow" class="text-xs font-medium text-content-heading cursor-pointer">Settle payment now at check-in</label>
                                    </div>

                                    @if ($isPaidNow)
                                        <div class="space-y-1">
                                            <label for="checkin-receipt-number" class="block text-xs font-semibold text-content-heading">
                                                Receipt # / OR Number <span class="text-brand-secondary">*</span>
                                            </label>
                                            <input
                                                id="checkin-receipt-number"
                                                type="text"
                                                wire:model.live.debounce.300ms="checkinReceiptNumber"
                                                placeholder="e.g. TRX-123456"
                                                class="w-full rounded-xl border border-surface-border bg-surface-card py-2 px-3 text-xs font-mono text-content-heading placeholder:text-content-muted focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                                            />
                                            @error('checkinReceiptNumber') <span class="text-brand-secondary block mt-1 font-medium">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-surface-border/70">
                        <button
                            type="button"
                            wire:click="processCheckin"
                            wire:target="processCheckin"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-primary px-5 py-2.5 text-xs font-semibold text-white shadow-2xs hover:bg-brand-primary-hover focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20 transition cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading wire:target="processCheckin" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                            <span>Confirm Return</span>
                        </button>
                    </div>
                </section>
            @endif
        </div>
    @endif

    {{-- TAB 3: LOANS & HISTORY LOG --}}
    @if ($activeMode === 'active_loans')
        <div class="space-y-5">
            <section aria-label="Loans and history filters" class="rounded-2xl border border-surface-border bg-surface-card p-5 shadow-2xs">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex-1 max-w-md">
                        <label for="circulation-loans-search" class="sr-only">Search by patron, accession, or receipt number</label>
                        <input
                            id="circulation-loans-search"
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search patron, accession #, or receipt #..."
                            class="w-full rounded-xl border border-surface-border bg-surface-canvas py-2 px-4 text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                        />
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <div>
                            <label for="circulation-filter-status" class="sr-only">Filter loan status</label>
                            <select
                                id="circulation-filter-status"
                                wire:model.live="filterStatus"
                                class="rounded-xl border border-surface-border bg-surface-canvas py-2 px-3 text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                            >
                                <option value="borrowed">Status: On-Loan</option>
                                <option value="overdue">Status: Overdue</option>
                                <option value="returned">Status: Returned</option>
                                <option value="all">Status: All Logs</option>
                            </select>
                        </div>

                        <div>
                            <label for="circulation-filter-condition" class="sr-only">Filter item condition</label>
                            <select
                                id="circulation-filter-condition"
                                wire:model.live="filterCondition"
                                class="rounded-xl border border-surface-border bg-surface-canvas py-2 px-3 text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                            >
                                <option value="all">Condition: All</option>
                                <option value="good">Condition: Good</option>
                                <option value="minor damaged">Condition: Minor Damaged</option>
                                <option value="severe damaged">Condition: Severe Damaged</option>
                                <option value="lost">Condition: Lost</option>
                            </select>
                        </div>

                        @if ($filterStatus !== 'borrowed' || $filterCondition !== 'all' || $search !== '')
                            <button
                                type="button"
                                wire:click="resetFilters"
                                wire:target="resetFilters"
                                class="rounded-xl border border-surface-border bg-surface-canvas px-3 py-2 text-xs font-medium text-content-muted hover:text-content-heading transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                            >
                                Reset
                            </button>
                        @endif

                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                            <button
                                type="button"
                                @click="open = !open"
                                aria-haspopup="menu"
                                :aria-expanded="open.toString()"
                                class="inline-flex items-center gap-2 rounded-xl border border-surface-border bg-surface-canvas px-3.5 py-2 text-xs font-semibold text-content-heading hover:bg-surface-card focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15 transition shadow-2xs cursor-pointer"
                            >
                                <x-heroicon-o-arrow-down-tray class="h-3.5 w-3.5 text-content-muted shrink-0" aria-hidden="true" />
                                <span>Export</span>
                                <x-heroicon-o-chevron-down class="h-3.5 w-3.5 text-content-muted transition-transform duration-200" ::class="{ 'rotate-180 text-brand-primary': open }" aria-hidden="true" />
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                role="menu"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 z-20 mt-2 w-40 rounded-xl border border-surface-border bg-surface-card p-1 shadow-lg"
                            >
                                <button
                                    type="button"
                                    role="menuitem"
                                    wire:click="exportExcel"
                                    wire:target="exportExcel"
                                    wire:loading.attr="disabled"
                                    @click="open = false"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-xs font-medium text-content-body hover:bg-brand-tint-light hover:text-brand-primary transition cursor-pointer disabled:opacity-50"
                                >
                                    <x-heroicon-o-table-cells class="h-4 w-4 text-brand-primary shrink-0" aria-hidden="true" />
                                    <span>Export Excel</span>
                                </button>
                                <button
                                    type="button"
                                    role="menuitem"
                                    wire:click="exportPdf"
                                    wire:target="exportPdf"
                                    wire:loading.attr="disabled"
                                    @click="open = false"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-xs font-medium text-content-body hover:bg-brand-secondary/10 hover:text-brand-secondary transition cursor-pointer disabled:opacity-50"
                                >
                                    <x-heroicon-o-document-arrow-down class="h-4 w-4 text-brand-secondary shrink-0" aria-hidden="true" />
                                    <span>Export PDF</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="rounded-2xl border border-surface-border bg-surface-card shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border-collapse text-xs text-content-body">
                        <thead>
                            <tr class="bg-surface-canvas border-b border-surface-border text-[11px] font-semibold uppercase tracking-wider text-content-muted">
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Accession #</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Item Title</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">ID Number</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Patron Name</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Type</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Borrowed Date</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Due Date</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Status</th>
                                @if ($filterStatus === 'returned' || $filterStatus === 'all')
                                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Returned Date</th>
                                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Receipt #</th>
                                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Payment</th>
                                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Fine Amount</th>
                                @endif
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Condition</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap">Processed By</th>
                                <th scope="col" class="px-4 py-3 whitespace-nowrap text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-border/70 bg-surface-card">
                            @forelse ($activeLoans as $loan)
                                @php
                                    $isStudent = strtolower(trim($loan->patron?->patronType?->name ?? '')) === 'student';
                                    $statusLower = strtolower((string) ($loan->status ?? ''));
                                    $conditionLower = strtolower((string) ($loan->condition ?? 'good'));
                                    $displayStatus = match ($statusLower) {
                                        'borrowed' => ($isStudent && $loan->due_at?->isPast()) ? 'Overdue' : 'On-Loan',
                                        'lost'     => 'Returned',
                                        default    => Str::title((string) ($loan->status ?? 'Returned')),
                                    };
                                    $rowMiddleInitial = $loan->patron?->middle_name ? mb_substr(trim($loan->patron->middle_name), 0, 1) . '.' : '';
                                    $rowPatronName = $loan->patron
                                        ? Str::title(trim("{$loan->patron->first_name} {$rowMiddleInitial} {$loan->patron->last_name}")) . ($loan->patron->suffix ? ' ' . strtoupper(trim($loan->patron->suffix)) : '')
                                        : 'Deleted Patron';
                                    $processorMiddleInitial = $loan->user?->middle_name ? mb_substr(trim($loan->user->middle_name), 0, 1) . '.' : '';
                                    $processorName = $loan->user
                                        ? Str::title(trim("{$loan->user->first_name} {$processorMiddleInitial} {$loan->user->last_name}"))
                                        : 'System User';
                                @endphp
                                <tr wire:key="circulation-row-{{ $loan->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                                    <td class="px-4 py-3 font-mono font-semibold text-brand-primary whitespace-nowrap">
                                        {{ $loan->accession?->accession_number ?: 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-content-heading whitespace-nowrap">
                                        {{ $loan->accession?->catalog?->title ? Str::title($loan->accession->catalog->title) : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono whitespace-nowrap">
                                        {{ $loan->patron?->school_id ?: 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-content-heading whitespace-nowrap">
                                        {{ $rowPatronName }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ $loan->patron?->patronType?->name ? Str::title($loan->patron->patronType->name) : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-[11px] whitespace-nowrap">
                                        {{ $loan->borrowed_at ? $loan->borrowed_at->format('M d, Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-[11px] whitespace-nowrap">
                                        {{ $isStudent ? ($loan->due_at ? $loan->due_at->format('M d, Y') : '-') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ match(strtolower($displayStatus)) {
                                            'on-loan' => 'bg-brand-tint text-brand-primary',
                                            'returned' => 'bg-brand-tint-light text-brand-primary',
                                            'overdue' => 'bg-brand-secondary/15 text-brand-secondary',
                                            default => 'bg-surface-canvas text-content-heading border border-surface-border'
                                        } }}">
                                            {{ strtoupper($displayStatus) }}
                                        </span>
                                    </td>
                                    @if ($filterStatus === 'returned' || $filterStatus === 'all')
                                        <td class="px-4 py-3 font-mono text-[11px] whitespace-nowrap">
                                            {{ $loan->returned_at ? $loan->returned_at->format('M d, Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 font-mono text-[11px] whitespace-nowrap">
                                            {{ $loan->receipt_number ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-[11px] font-medium whitespace-nowrap">
                                            @if ((float) $loan->fine_amount > 0)
                                                {{ $loan->is_paid ? 'Paid' : 'Unpaid' }}
                                            @else
                                                No Fine
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-mono whitespace-nowrap">
                                            @if ((float) $loan->fine_amount > 0)
                                                <span class="font-semibold {{ $loan->is_paid ? 'text-content-heading' : 'text-brand-secondary' }}">₱{{ number_format((float) $loan->fine_amount, 2) }}</span>
                                            @else
                                                <span class="text-content-muted">-</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ match($conditionLower) {
                                            'good' => 'bg-brand-tint-light text-brand-primary',
                                            'minor damaged' => 'bg-brand-tint text-brand-primary',
                                            'severe damaged', 'lost' => 'bg-brand-secondary/15 text-brand-secondary',
                                            default => 'bg-surface-canvas text-content-heading border border-surface-border'
                                        } }}">
                                            {{ strtoupper((string) ($loan->condition ?? 'GOOD')) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-content-body whitespace-nowrap">
                                        {{ $processorName }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        @if ($statusLower === 'borrowed')
                                            <button
                                                type="button"
                                                wire:click="quickInspect('{{ $loan->accession?->accession_number }}')"
                                                class="inline-flex items-center gap-1 rounded-lg border border-surface-border bg-surface-canvas px-2.5 py-1 text-xs font-semibold text-content-heading shadow-2xs hover:bg-brand-tint-light hover:text-brand-primary transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                            >
                                                Return / Inspect
                                            </button>
                                        @elseif (in_array($statusLower, ['returned', 'lost'], true) && ! $loan->is_paid && (float) $loan->fine_amount > 0)
                                            <button
                                                type="button"
                                                wire:click="payFine({{ $loan->id }})"
                                                class="inline-flex items-center gap-1 rounded-lg border border-brand-secondary/30 bg-brand-secondary/10 px-2.5 py-1 text-xs font-semibold text-brand-secondary shadow-2xs hover:bg-brand-secondary/15 transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                                            >
                                                Settle Fine
                                            </button>
                                        @else
                                            <span class="text-xs text-content-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $filterStatus === 'returned' || $filterStatus === 'all' ? '15' : '11' }}" class="px-4 py-10 text-center">
                                        <p class="text-xs font-semibold text-content-heading">No circulation logs found</p>
                                        <p class="text-[11px] text-content-muted mt-1">Try adjusting your search query, status filter, or condition filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (method_exists($activeLoans, 'links'))
                <div class="pt-1">
                    {{ $activeLoans->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Edit / Settle Payment Modal --}}
    @if ($showEditPaymentModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-content-heading/55 p-4 backdrop-blur-2xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="settle-fine-modal-title"
            @keydown.escape.window="$wire.closeEditPaymentModal()"
        >
            <div class="w-full max-w-md rounded-2xl border border-surface-border bg-surface-card p-6 shadow-xl space-y-5">
                <div class="flex items-center justify-between border-b border-surface-border/70 pb-3">
                    <h3 id="settle-fine-modal-title" class="text-sm font-bold text-content-heading">Settle Unpaid Fine</h3>
                    <button
                        type="button"
                        wire:click="closeEditPaymentModal"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading cursor-pointer rounded-lg p-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label for="edit-fine-amount" class="block font-semibold text-content-heading mb-1">
                            Fine Amount (₱) <span class="text-brand-secondary">*</span>
                        </label>
                        <input
                            id="edit-fine-amount"
                            type="number"
                            step="0.01"
                            min="5.00"
                            wire:model="editFineAmount"
                            class="w-full rounded-xl border border-surface-border bg-surface-canvas py-2 px-3 text-xs font-mono text-content-heading focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                        />
                        @error('editFineAmount') <span class="text-brand-secondary block mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="isEditPaid"
                            wire:model.live="isEditPaid"
                            class="h-4 w-4 rounded border-surface-border text-brand-primary focus:ring-brand-primary cursor-pointer"
                        />
                        <label for="isEditPaid" class="font-medium text-content-heading cursor-pointer">Mark as paid and settled</label>
                    </div>

                    @if ($isEditPaid)
                        <div>
                            <label for="edit-receipt-number" class="block font-semibold text-content-heading mb-1">
                                Receipt # / OR Number <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="edit-receipt-number"
                                type="text"
                                wire:model="editReceiptNumber"
                                placeholder="e.g. OR-987654"
                                class="w-full rounded-xl border border-surface-border bg-surface-canvas py-2 px-3 text-xs font-mono text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                            />
                            @error('editReceiptNumber') <span class="text-brand-secondary block mt-1 font-medium">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-surface-border/70">
                    <button
                        type="button"
                        wire:click="closeEditPaymentModal"
                        class="rounded-xl border border-surface-border bg-surface-canvas px-4 py-2 text-xs font-semibold text-content-body hover:text-content-heading transition cursor-pointer"
                    >Cancel</button>
                    <button
                        type="button"
                        wire:click="updatePayment"
                        wire:target="updatePayment"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-primary px-4 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-brand-primary-hover transition cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading wire:target="updatePayment" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
