<div class="space-y-6"
     x-data="{ toastMessage: '', toastType: 'success', showToast: false }"
     x-on:toast.window="toastMessage = $event.detail.message; toastType = $event.detail.type || 'success'; showToast = true; setTimeout(() => showToast = false, 4000)">

    <!-- TOAST DISPATCH NOTIFICATION -->
    <div x-show="showToast"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed bottom-5 right-5 z-50 flex items-center gap-3 rounded-xl p-4 shadow-lg text-sm font-medium text-white uppercase"
         :class="toastType === 'error' ? 'bg-rose-600' : 'bg-emerald-600'">
        <span x-text="toastMessage"></span>
        <button type="button" @click="showToast = false" class="text-white/80 hover:text-white cursor-pointer">&times;</button>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 shadow-xs">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="uppercase">{{ strtoupper(session('message')) }}</span>
            </div>
            <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800 cursor-pointer">&times;</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" class="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800 shadow-xs">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="uppercase">{{ strtoupper(session('error')) }}</span>
            </div>
            <button type="button" @click="show = false" class="text-rose-600 hover:text-rose-800 cursor-pointer">&times;</button>
        </div>
    @endif

    <!-- CIRCULATION DESK HEADER BAR -->
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">CIRCULATIONS</h1>
            <p class="mt-1 text-sm text-slate-500">Manage Item Borrowing, Condition Inspections, Penalties, And Historical Loan Records.</p>
        </div>

        <!-- Mode Switcher -->
        <div class="inline-flex shrink-0 self-start rounded-xl border border-slate-200/80 bg-slate-100/80 p-1.5 md:self-auto">
            <button
                type="button"
                wire:click="switchMode('checkout')"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase transition-all cursor-pointer {{ $activeMode === 'checkout' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                BORROW (CHECK-OUT)
            </button>
            <button
                type="button"
                wire:click="switchMode('checkin')"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase transition-all cursor-pointer {{ $activeMode === 'checkin' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                RETURN (CHECK-IN)
            </button>
            <button
                type="button"
                wire:click="switchMode('active_loans')"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase transition-all cursor-pointer {{ $activeMode === 'active_loans' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                LOANS & HISTORY LOG
            </button>
        </div>
    </div>

    <!-- TAB 1: CHECKOUT MODE -->
    @if ($activeMode === 'checkout')
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
            <!-- Step 1: Patron Identification -->
            <div class="flex flex-col justify-between space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full border border-indigo-100 bg-indigo-50 text-xs font-bold text-indigo-600">1</span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">PATRON IDENTIFICATION</h2>
                            <p class="text-xs text-slate-500">Scan RFID Card, Or Search By Student/Employee Number</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">SCAN OR INPUT STUDENT/EMPLOYEE # / RFID</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg wire:loading.remove wire:target="patronInput" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <svg wire:loading wire:target="patronInput" class="h-4 w-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="patronInput"
                                wire:keydown.escape="$set('patronInput', '')"
                                placeholder="Enter School ID or Scan RFID..."
                                class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                autofocus
                            />
                        </div>
                        @error('patronInput') <span class="text-xs text-rose-600 block mt-1 uppercase">{{ strtoupper($message) }}</span> @enderror
                    </div>

                    @if ($selectedPatron)
                        @php
                            $middleInitial = $selectedPatron->middle_name ? mb_substr(trim($selectedPatron->middle_name), 0, 1) . '.' : '';
                            $patronName = \Illuminate\Support\Str::title(trim("{$selectedPatron->first_name} {$middleInitial} {$selectedPatron->last_name}")) . ($selectedPatron->suffix ? ' ' . strtoupper(trim($selectedPatron->suffix)) : '');
                            $isStudentSelected = strtolower(trim($selectedPatron->patronType?->name ?? '')) === 'student';
                        @endphp
                        <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-slate-900">{{ $patronName }}</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-800">
                                        {{ strtoupper((string) $selectedPatron->status) }}
                                    </span>
                                    @if ($isTimedIn)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                            TIMED IN
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                                            NOT TIMED IN
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 border-t border-slate-200/80 pt-3 text-xs text-slate-600">
                                @if ($isStudentSelected)
                                    <div><span class="font-medium text-slate-400 uppercase">STUDENT #:</span> <span class="font-mono font-semibold text-slate-800 uppercase">{{ strtoupper((string) $selectedPatron->school_id) }}</span></div>
                                @else
                                    <div><span class="font-medium text-slate-400 uppercase">EMPLOYEE #:</span> <span class="font-mono font-semibold text-slate-800 uppercase">{{ strtoupper((string) $selectedPatron->school_id) }}</span></div>
                                @endif
                                <div><span class="font-medium text-slate-400 uppercase">TYPE:</span> <span class="font-semibold text-slate-800 uppercase">{{ $selectedPatron->patronType?->name ? strtoupper($selectedPatron->patronType->name) : 'N/A' }}</span></div>
                            </div>

                            @if (! $isTimedIn)
                                <div class="rounded-lg border border-rose-200 bg-rose-50 p-2.5 text-[11px] font-medium text-rose-700 uppercase">
                                    <strong>TRANSACTION BLOCKED:</strong> PATRON IS NOT TIMED IN AT THE ENTRANCE KIOSK TODAY.
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center">
                            <p class="text-xs font-medium text-slate-400 uppercase">AWAITING PATRON SCAN OR SEARCH...</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Step 2: Asset Accession Identification -->
            <div class="flex flex-col justify-between space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full border border-indigo-100 bg-indigo-50 text-xs font-bold text-indigo-600">2</span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">ASSET ACCESSION</h2>
                            <p class="text-xs text-slate-500">Scan Barcode Or Input Accession Number</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">SCAN OR INPUT ACCESSION NO.</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg wire:loading.remove wire:target="accessionInput" class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <svg wire:loading wire:target="accessionInput" class="h-4 w-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="accessionInput"
                                wire:keydown.escape="$set('accessionInput', '')"
                                wire:keydown.enter.prevent
                                placeholder="Enter Accession No..."
                                class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            />
                        </div>
                        @error('accessionInput') <span class="text-xs text-rose-600 block mt-1 uppercase">{{ strtoupper($message) }}</span> @enderror
                    </div>

                    @if ($selectedAccession)
                        <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                            @if ($selectedAccession->catalog?->isbn_issn)
                                <div class="text-sm text-slate-600 uppercase">{{ strtoupper($selectedAccession->catalog?->isbn_issn) }}</div>
                            @endif
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-bold leading-snug text-slate-900 italic">{{ $selectedAccession->catalog?->title ? \Illuminate\Support\Str::title($selectedAccession->catalog->title) : 'N/A' }}</span>
                                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ strtolower($selectedAccession->status ?? '') === 'available' ? 'border border-emerald-200 bg-emerald-100 text-emerald-800' : 'border border-amber-200 bg-amber-100 text-amber-800' }}">
                                    {{ strtoupper((string) $selectedAccession->status) }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 uppercase">
                                <span>TYPE: {{ $selectedAccession->catalog?->assetType?->name ? strtoupper($selectedAccession->catalog->assetType->name) : 'N/A' }}</span>
                            </div>
                            <div class="text-xs text-slate-500">
                                <span class="uppercase">AUTHOR:</span> <span>{{ $selectedAccession->catalog?->author?->name ? \Illuminate\Support\Str::title($selectedAccession->catalog->author->name) : 'N/A' }}</span>
                            </div>
                            <div class="text-xs text-slate-500 uppercase">
                                <span>COPYRIGHT YEAR: {{ $selectedAccession->catalog?->publication_year ?? 'N/A' }}</span>
                            </div>
                            <div class="text-xs text-slate-500 uppercase">
                                <span>GENERAL REFERENCE: {{ $selectedAccession->catalog?->generalReference?->name ? strtoupper($selectedAccession->catalog->generalReference->name) : 'N/A' }}</span>
                            </div>
                            <div class="text-xs text-slate-500 uppercase">
                                <span>CALL NUMBER: {{ $selectedAccession->call_number ? strtoupper($selectedAccession->call_number) : 'N/A' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center">
                            <p class="text-xs font-medium text-slate-400 uppercase">AWAITING ASSET ACCESSION SCAN...</p>
                        </div>
                    @endif
                </div>

                <button
                    type="button"
                    wire:click="processCheckout"
                    wire:loading.attr="disabled"
                    @disabled(! $selectedPatron || ! $selectedAccession || strtolower($selectedAccession->status ?? '') !== 'available' || ! $isTimedIn)
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white uppercase shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-40 cursor-pointer">
                    <svg wire:loading wire:target="processCheckout" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>COMPLETE CHECK-OUT</span>
                </button>
            </div>
        </div>
    @endif

    <!-- TAB 2: CHECKIN MODE -->
    @if ($activeMode === 'checkin')
        <div class="space-y-6">
            <!-- Inspection Lookup Form -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <form wire:submit.prevent="inspectReturn" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="flex-1 space-y-1.5">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">SCAN RETURN ACCESSION BARCODE</label>
                        <input
                            type="text"
                            wire:model="returnAccessionInput"
                            placeholder="Scan or enter Accession No..."
                            class="w-full rounded-xl border border-slate-300 py-2.5 px-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            autofocus
                        />
                        @error('returnAccessionInput') <span class="text-xs text-rose-600 block mt-1 uppercase">{{ strtoupper($message) }}</span> @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white uppercase shadow-sm transition hover:bg-indigo-700 cursor-pointer">
                        INSPECT LOAN
                    </button>
                </form>
            </div>

            <!-- Inspected Item Details & Action Card -->
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
                    $suggestedMinor = $this->suggestedMinorDamagedFee;
                    $overdueDays = $inspectedLoan->due_at?->isPast() ? (int) $inspectedLoan->due_at->diffInDays(now()) : 0;
                    $effectiveOverdueDays = min($overdueDays, $policyLostAfterDays);
                    $inspectedMiddleInitial = $inspectedLoan->patron?->middle_name ? mb_substr(trim($inspectedLoan->patron->middle_name), 0, 1) . '.' : '';
                    $inspectedPatronName = $inspectedLoan->patron
                        ? \Illuminate\Support\Str::title(trim("{$inspectedLoan->patron->first_name} {$inspectedMiddleInitial} {$inspectedLoan->patron->last_name}")) . ($inspectedLoan->patron->suffix ? ' ' . strtoupper(trim($inspectedLoan->patron->suffix)) : '')
                        : 'N/A';
                    $isInspectedStudent = strtolower(trim($inspectedLoan->patron?->patronType?->name ?? '')) === 'student';
                    $assetTypeName = $inspectedLoan->accession?->catalog?->assetType?->name
                        ? strtoupper($inspectedLoan->accession->catalog->assetType->name)
                        : 'ITEM';
                @endphp
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 uppercase">RETURN INSPECTION</h3>
                            <p class="text-xs text-slate-500 uppercase">ACCESSION: <span class="font-mono font-medium text-slate-800">{{ $inspectedLoan->accession?->accession_number ? strtoupper($inspectedLoan->accession->accession_number) : 'N/A' }}</span></p>
                        </div>
                        <button type="button" wire:click="cancelInspection" class="text-xs font-semibold text-slate-500 hover:text-slate-800 uppercase cursor-pointer">CANCEL</button>
                    </div>

                    @if ($exceededLostThreshold)
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-semibold text-rose-800 uppercase">
                            ITEM EXCEEDED THE {{ $policyLostAfterDays }}-DAY OVERDUE LIMIT. DAILY OVERDUE FINE IS CAPPED AT {{ $policyLostAfterDays }} DAY(S).
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- LEFT SIDE: ITEM DETAILS & POLICY PENALTY SUMMARY -->
                        <div class="space-y-4 text-sm text-slate-600">
                            <div class="space-y-2">
                                <p><strong class="text-slate-800 uppercase">TITLE:</strong> <span>{{ $inspectedLoan->accession?->catalog?->title ? \Illuminate\Support\Str::title($inspectedLoan->accession->catalog->title) : 'N/A' }}</span></p>
                                <p><strong class="text-slate-800 uppercase">PATRON:</strong> <span>{{ $inspectedPatronName }}</span></p>
                                <p><strong class="text-slate-800 uppercase">TYPE:</strong> <span class="uppercase">{{ $inspectedLoan->patron?->patronType?->name ? strtoupper($inspectedLoan->patron->patronType->name) : 'N/A' }}</span></p>
                                <p><strong class="text-slate-800 uppercase">BORROW DATE:</strong> <span class="uppercase">{{ $inspectedLoan->borrowed_at?->format('M d, Y') ? strtoupper($inspectedLoan->borrowed_at->format('M d, Y')) : 'N/A' }}</span></p>
                                @if ($isInspectedStudent)
                                    <p><strong class="text-slate-800 uppercase">DUE DATE:</strong> <span class="uppercase">{{ $inspectedLoan->due_at?->format('M d, Y') ? strtoupper($inspectedLoan->due_at->format('M d, Y')) : 'N/A' }}</span></p>
                                @endif
                                <p><strong class="text-slate-800 uppercase">ITEM COST/PRICE:</strong> <span class="font-semibold text-indigo-700">₱{{ number_format($itemPrice, 2) }}</span></p>
                            </div>

                            <!-- PENALTY BREAKDOWN SUMMARY -->
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-2.5 text-xs">
                                <div class="font-bold text-slate-800 uppercase tracking-wider text-[11px] border-b border-slate-200 pb-1.5">
                                    PENALTY POLICY CALCULATION ({{ $assetTypeName }})
                                </div>

                                @if ($isInspectedStudent)
                                    <div class="rounded-lg bg-white border border-slate-200/80 p-2.5 space-y-1 text-[11px] text-slate-600">
                                        <div class="font-semibold text-slate-800 uppercase">HOW OVERDUE IS COMPUTED:</div>
                                        @if ($itemPrice > 0)
                                            <div>
                                                DAILY RATE = ITEM UNIT PRICE (₱{{ number_format($itemPrice, 2) }}) &divide; {{ $policyLostAfterDays }} DAYS
                                                = <strong class="text-indigo-700">₱{{ number_format((float) $policyDailyRate, 2) }}/DAY</strong>
                                            </div>
                                        @else
                                            <div>
                                                DAILY RATE = DEFAULT POLICY RATE
                                                = <strong class="text-indigo-700">₱{{ number_format((float) $policyDailyRate, 2) }}/DAY</strong>
                                            </div>
                                        @endif
                                        <div>
                                            OVERDUE FINE = {{ $effectiveOverdueDays }} BILLABLE DAY(S) &times; ₱{{ number_format((float) $policyDailyRate, 2) }}
                                            = <strong class="text-slate-900">₱{{ number_format($safeOverdueFine, 2) }}</strong>
                                        </div>
                                        <div class="text-amber-700 font-medium uppercase">
                                            RULE: DAILY OVERDUE FINE IS CAPPED AFTER {{ $policyLostAfterDays }} OVERDUE DAYS.
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-lg bg-white border border-slate-200/80 p-2.5 text-[11px] text-slate-600 uppercase">
                                        NON-STUDENT PATRON: OVERDUE DAILY FINES DO NOT APPLY (₱0.00). CONDITION CHARGES STILL APPLY IF DAMAGED OR LOST.
                                    </div>
                                @endif

                                <div class="flex justify-between text-slate-600 uppercase">
                                    <span>OVERDUE DAY(S):</span>
                                    <span class="font-semibold text-slate-800">{{ $overdueDays }} DAY(S)</span>
                                </div>
                                <div class="flex justify-between text-slate-600 uppercase">
                                    <span>OVERDUE FINE (POLICY RATE):</span>
                                    <span class="font-semibold text-slate-800">₱{{ number_format($safeOverdueFine, 2) }}</span>
                                </div>

                                @if ($returnCondition !== 'good')
                                    <div class="flex justify-between text-slate-600 uppercase">
                                        <span>ASSESSMENT FEE:</span>
                                        <span class="font-semibold text-slate-800">₱{{ number_format($safeAssessmentFee, 2) }}</span>
                                    </div>
                                    @if ($includesUnitCost)
                                        <div class="flex justify-between text-slate-600 uppercase">
                                            <span>UNIT COST ({{ strtoupper($returnCondition) }}):</span>
                                            <span class="font-semibold text-slate-800">₱{{ number_format($itemPrice, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between text-slate-600 uppercase">
                                        <span>CONDITION SUBTOTAL ({{ strtoupper($returnCondition) }}):</span>
                                        <span class="font-semibold text-slate-800">₱{{ number_format($conditionFeeTotal, 2) }}</span>
                                    </div>
                                @endif

                                <div class="flex justify-between pt-1.5 border-t border-slate-200 font-bold text-slate-900 uppercase">
                                    <span>TOTAL CALCULATED FINE:</span>
                                    <span class="text-indigo-600">₱{{ number_format($totalFineCalculated, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT SIDE: CONDITION, ASSESSMENT GUIDE & FEE INPUT CONTROLS -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">RETURN CONDITION</label>
                                <select wire:model.live="returnCondition" class="w-full rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 uppercase focus:border-indigo-500 focus:outline-none">
                                    <option value="good">GOOD (₱0.00)</option>
                                    <option value="minor damaged">MINOR DAMAGED (REPAIRABLE &bull; ASSESSMENT FEE)</option>
                                    <option value="severe damaged">SEVERE DAMAGED (UNREPAIRABLE &bull; ASSESSMENT FEE + UNIT COST)</option>
                                    <option value="lost">LOST (ASSESSMENT FEE + UNIT COST)</option>
                                </select>
                            </div>

                            <!-- CONDITION ASSESSMENT COMPUTATION GUIDE -->
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-3.5 space-y-2 text-[11px] text-slate-600">
                                <div class="font-bold text-indigo-950 uppercase tracking-wider">
                                    CONDITION ASSESSMENT COMPUTATION GUIDE
                                </div>
                                <div class="grid grid-cols-1 gap-2">
                                    <div class="flex justify-between border-b border-indigo-100 pb-1.5">
                                        <span class="font-semibold text-emerald-700 uppercase">GOOD:</span>
                                        <span class="font-mono text-slate-800">₱0.00 (AVAILABLE)</span>
                                    </div>
                                    <div class="border-b border-indigo-100 pb-1.5 space-y-0.5">
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-amber-700 uppercase">MINOR DAMAGED:</span>
                                            <span class="font-mono text-slate-800">ASSESSMENT FEE (~₱{{ number_format($suggestedMinor, 2) }})</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 uppercase">REPAIRABLE ITEM &bull; SCALED FOR LOW-COST ITEMS &bull; UNDER MAINTENANCE</div>
                                    </div>
                                    <div class="border-b border-indigo-100 pb-1.5 space-y-0.5">
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-orange-700 uppercase">SEVERE DAMAGED:</span>
                                            <span class="font-mono text-slate-800">ASSESSMENT FEE + ₱{{ number_format($itemPrice, 2) }} UNIT COST</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 uppercase">UNREPAIRABLE ITEM &bull; FULL UNIT PRICE CHARGED &bull; DUMPED</div>
                                    </div>
                                    <div class="space-y-0.5">
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-rose-700 uppercase">LOST:</span>
                                            <span class="font-mono text-slate-800">ASSESSMENT FEE + ₱{{ number_format($itemPrice, 2) }} UNIT COST</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 uppercase">ASSESSMENT FEE + FULL ITEM UNIT COST &bull; MARKED LOST &bull; DUMPED</div>
                                    </div>
                                </div>
                            </div>

                            <!-- ASSESSMENT FEE FIELD -->
                            @if ($returnCondition !== 'good')
                                <div class="space-y-1">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        ASSESSMENT FEE (₱)
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="{{ $minFeeAllowed }}"
                                        wire:model.live.debounce.350ms="assessmentFeeAmount"
                                        placeholder="Enter assessment fee..."
                                        class="w-full rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none"
                                    />
                                    @error('assessmentFeeAmount')
                                        <span class="text-red-500 text-xs mt-1 block uppercase">{{ strtoupper($message) }}</span>
                                    @enderror
                                    @if ($includesUnitCost)
                                        <p class="text-[11px] text-indigo-700 font-medium uppercase">
                                            FORMULA: ASSESSMENT FEE (₱{{ number_format($safeAssessmentFee, 2) }}) + UNIT COST (₱{{ number_format($itemPrice, 2) }}) = ₱{{ number_format($conditionFeeTotal, 2) }}
                                        </p>
                                    @else
                                        <p class="text-[11px] text-slate-400 uppercase">
                                            REPAIRABLE ITEM ASSESSMENT FEE (MIN ₱{{ number_format($minFeeAllowed, 2) }}, CAPPED AT UNIT COST ₱{{ number_format($itemPrice, 2) }} FOR LOW-COST ITEMS).
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <!-- FINE & PAYMENT SETTLEMENT SECTION -->
                            @if ($totalFineCalculated > 0)
                                <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-amber-900 uppercase">TOTAL FINE ASSESSED:</span>
                                        <span class="text-base font-extrabold text-amber-800">₱{{ number_format($totalFineCalculated, 2) }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="checkbox" id="isPaidNow" wire:model.live="isPaidNow" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                                        <label for="isPaidNow" class="text-xs font-medium text-slate-800 uppercase cursor-pointer">SETTLE PAYMENT NOW AT CHECK-IN</label>
                                    </div>

                                    @if ($isPaidNow)
                                        <div class="space-y-1 pt-1">
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">RECEIPT # / OR NUMBER <span class="text-rose-600">*</span></label>
                                            <input type="text" wire:model="checkinReceiptNumber" placeholder="e.g. TRX-123456" class="w-full rounded-lg border border-slate-300 py-1.5 px-3 text-xs text-slate-800 uppercase focus:border-indigo-500 focus:outline-none" />
                                            @error('checkinReceiptNumber') <span class="text-xs text-rose-600 block mt-1 uppercase">{{ strtoupper($message) }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <button type="button" wire:click="processCheckin" wire:loading.attr="disabled" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white uppercase shadow-sm hover:bg-emerald-700 transition cursor-pointer disabled:opacity-50">
                            CONFIRM RETURN
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 3: ACTIVE LOANS & HISTORY LOG -->
    @if ($activeMode === 'active_loans')
        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by patron, accession, receipt #..."
                    class="w-full sm:w-80 rounded-xl border border-slate-300 py-2 px-4 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:outline-none"
                />

                <div class="flex items-center gap-3">
                    <select wire:model.live="filterStatus" class="rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 uppercase focus:border-indigo-500 focus:outline-none">
                        <option value="borrowed">ON LOAN</option>
                        <option value="returned">RETURNED</option>
                        <option value="overdue">OVERDUE</option>
                        <option value="lost">LOST</option>
                        <option value="all">ALL LOGS</option>
                    </select>

                    <!-- EXPORT DROPDOWN (ALPINE.JS) -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button
                            type="button"
                            @click="open = !open"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-700 uppercase hover:bg-slate-100 transition shadow-xs cursor-pointer">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            EXPORT
                            <svg class="h-3 w-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 transform"
                            x-transition:enter-end="opacity-100 scale-100 transform"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 transform"
                            x-transition:leave-end="opacity-0 scale-95 transform"
                            class="absolute right-0 z-20 mt-2 w-40 rounded-xl border border-slate-200 bg-white py-1 shadow-lg ring-1 ring-black/5">
                            <button
                                type="button"
                                wire:click="exportExcel"
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-xs font-medium text-slate-700 uppercase hover:bg-slate-50 cursor-pointer">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                EXPORT EXCEL
                            </button>
                            <button
                                type="button"
                                wire:click="exportPdf"
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-xs font-medium text-slate-700 uppercase hover:bg-slate-50 cursor-pointer">
                                <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                EXPORT PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">ACCESSION #</th>
                            <th class="px-4 py-3 whitespace-nowrap">ITEM TITLE</th>
                            <th class="px-4 py-3 whitespace-nowrap">STUDENT/EMPLOYEE #</th>
                            <th class="px-4 py-3 whitespace-nowrap">PATRON NAME</th>
                            <th class="px-4 py-3 whitespace-nowrap">TYPE</th>
                            <th class="px-4 py-3 whitespace-nowrap">BORROWED DATE</th>
                            <th class="px-4 py-3 whitespace-nowrap">DUE DATE</th>
                            <th class="px-4 py-3 whitespace-nowrap">STATUS</th>
                            @if ($filterStatus === 'returned' || $filterStatus === 'lost' || $filterStatus === 'all')
                            <th class="px-4 py-3 whitespace-nowrap">RETURNED DATE</th>
                            <th class="px-4 py-3 whitespace-nowrap">RECEIPT #</th>
                            <th class="px-4 py-3 whitespace-nowrap">PAYMENT</th>
                            <th class="px-4 py-3 whitespace-nowrap">FINE AMOUNT</th>
                            @endif
                            <th class="px-4 py-3 whitespace-nowrap">CONDITION</th>
                            <th class="px-4 py-3 whitespace-nowrap">PROCESSED BY</th>
                            <th class="px-4 py-3 whitespace-nowrap text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 border-t border-slate-100">
                        @forelse ($activeLoans as $loan)
                            @php
                                $isStudent = strtolower(trim($loan->patron?->patronType?->name ?? '')) === 'student';
                                $statusLower = strtolower((string) ($loan->status ?? ''));
                                $conditionLower = strtolower((string) ($loan->condition ?? 'good'));
                                $displayStatus = match ($statusLower) {
                                    'borrowed' => ($isStudent && $loan->due_at?->isPast()) ? 'OVERDUE' : 'ON LOAN',
                                    'lost'     => 'RETURNED',
                                    default    => strtoupper((string) ($loan->status ?? 'RETURNED')),
                                };
                                $rowMiddleInitial = $loan->patron?->middle_name ? mb_substr(trim($loan->patron->middle_name), 0, 1) . '.' : '';
                                $rowPatronName = $loan->patron
                                    ? \Illuminate\Support\Str::title(trim("{$loan->patron->first_name} {$rowMiddleInitial} {$loan->patron->last_name}")) . ($loan->patron->suffix ? ' ' . strtoupper(trim($loan->patron->suffix)) : '')
                                    : 'DELETED PATRON';
                                $processorMiddleInitial = $loan->user?->middle_name ? mb_substr(trim($loan->user->middle_name), 0, 1) . '.' : '';
                                $processorName = $loan->user
                                    ? strtoupper(trim("{$loan->user->first_name} {$processorMiddleInitial} {$loan->user->last_name}"))
                                    : 'SYSTEM USER';
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 font-mono font-medium text-slate-900 uppercase whitespace-nowrap">
                                    {{ $loan->accession?->accession_number ? strtoupper($loan->accession->accession_number) : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">
                                    {{ $loan->accession?->catalog?->title ? \Illuminate\Support\Str::title($loan->accession->catalog->title) : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 font-mono uppercase whitespace-nowrap">
                                    {{ $loan->patron?->school_id ? strtoupper($loan->patron->school_id) : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-900">
                                    {{ $rowPatronName }}
                                </td>
                                <td class="px-4 py-3 uppercase whitespace-nowrap">
                                    {{ $loan->patron?->patronType?->name ? strtoupper($loan->patron->patronType->name) : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 uppercase whitespace-nowrap">
                                    {{ $loan->borrowed_at ? strtoupper($loan->borrowed_at->format('M d, Y')) : '-' }}
                                </td>
                                <td class="px-4 py-3 uppercase whitespace-nowrap">
                                    {{ $isStudent ? ($loan->due_at ? strtoupper($loan->due_at->format('M d, Y')) : '-') : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ match($displayStatus) { 'ON LOAN' => 'bg-indigo-100 text-indigo-800 border-indigo-200', 'RETURNED' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'OVERDUE' => 'bg-rose-100 text-rose-800 border-rose-200', default => 'bg-slate-100 text-slate-800 border-slate-200' } }}">
                                        {{ $displayStatus }}
                                    </span>
                                </td>
                                @if ($filterStatus === 'returned' || $filterStatus === 'lost' || $filterStatus === 'all')
                                <td class="px-4 py-3 uppercase whitespace-nowrap">
                                    {{ $loan->returned_at ? strtoupper($loan->returned_at->format('M d, Y')) : '-' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs uppercase whitespace-nowrap">
                                    {{ $loan->receipt_number ? strtoupper($loan->receipt_number) : '-' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs uppercase whitespace-nowrap">
                                    @if ((float) $loan->fine_amount > 0)
                                        {{ $loan->is_paid ? 'PAID' : 'UNPAID' }}
                                    @else
                                        NO FINE
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold whitespace-nowrap">
                                    @if ((float) $loan->fine_amount > 0)
                                        <span class="{{ $loan->is_paid ? 'text-slate-800' : 'text-rose-600' }}">₱{{ number_format((float) $loan->fine_amount, 2) }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-medium uppercase {{ match($conditionLower) { 'good' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'minor damaged' => 'bg-amber-50 text-amber-700 border-amber-200', 'severe damaged' => 'bg-orange-50 text-orange-700 border-orange-200', 'lost' => 'bg-rose-50 text-rose-700 border-rose-200', default => 'bg-slate-50 text-slate-700 border-slate-200' } }}">
                                        {{ strtoupper((string) ($loan->condition ?? 'GOOD')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs font-semibold text-slate-700 uppercase">
                                    {{ $processorName }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    @if ($statusLower === 'borrowed')
                                        <button type="button" wire:click="quickInspect('{{ $loan->accession?->accession_number }}')" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 uppercase shadow-2xs hover:bg-slate-50 transition cursor-pointer">
                                            RETURN / INSPECT
                                        </button>
                                    @elseif (in_array($statusLower, ['returned', 'lost'], true) && ! $loan->is_paid && (float) $loan->fine_amount > 0)
                                        <button type="button" wire:click="payFine({{ $loan->id }})" class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 uppercase shadow-2xs hover:bg-amber-100 transition cursor-pointer">
                                            SETTLE FINE
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $filterStatus === 'returned' || $filterStatus === 'lost' || $filterStatus === 'all' ? '15' : '11' }}" class="px-4 py-8 text-center text-xs font-medium text-slate-400 uppercase">
                                    NO CIRCULATION LOGS FOUND MATCHING CRITERIA.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if (method_exists($activeLoans, 'links'))
                <div class="p-4 pt-2">
                    {{ $activeLoans->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- EDIT / SETTLE PAYMENT MODAL -->
    @if ($showEditPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900 uppercase">SETTLE UNPAID FINE</h3>
                    <button type="button" wire:click="closeEditPaymentModal" class="text-slate-400 hover:text-slate-600 cursor-pointer">&times;</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">FINE AMOUNT (₱)</label>
                        <input type="number" step="0.01" min="5.00" wire:model="editFineAmount" class="w-full rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none" />
                        @error('editFineAmount') <span class="text-xs text-rose-600 block mt-1 uppercase">{{ strtoupper($message) }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="isEditPaid" wire:model.live="isEditPaid" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                        <label for="isEditPaid" class="text-xs font-medium text-slate-800 uppercase cursor-pointer">MARK AS PAID & SETTLED</label>
                    </div>

                    @if ($isEditPaid)
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">RECEIPT # / OR NUMBER <span class="text-rose-600">*</span></label>
                            <input type="text" wire:model="editReceiptNumber" placeholder="e.g. OR-987654" class="w-full rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 uppercase focus:border-indigo-500 focus:outline-none" />
                            @error('editReceiptNumber') <span class="text-xs text-rose-600 block mt-1 uppercase">{{ strtoupper($message) }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="closeEditPaymentModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 uppercase hover:bg-slate-50 cursor-pointer">CANCEL</button>
                    <button type="button" wire:click="updatePayment" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white uppercase shadow-sm hover:bg-indigo-700 cursor-pointer">SAVE CHANGES</button>
                </div>
            </div>
        </div>
    @endif
</div>
