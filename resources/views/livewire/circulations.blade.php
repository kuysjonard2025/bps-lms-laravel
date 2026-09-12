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
         class="fixed bottom-5 right-5 z-50 flex items-center gap-3 rounded-xl p-4 shadow-lg text-sm font-medium text-white"
         :class="toastType === 'error' ? 'bg-rose-600' : 'bg-emerald-600'">
        <span x-text="toastMessage"></span>
        <button type="button" @click="showToast = false" class="text-white/80 hover:text-white">&times;</button>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 shadow-xs">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('message') }}</span>
            </div>
            <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800">&times;</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" class="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800 shadow-xs">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="show = false" class="text-rose-600 hover:text-rose-800">&times;</button>
        </div>
    @endif

    <!-- CIRCULATION DESK HEADER BAR -->
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Circulations</h1>
            <p class="mt-1 text-sm text-slate-500">Manage book borrowing, condition inspections, penalties, and historical loan records.</p>
        </div>

        <!-- Mode Switcher -->
        <div class="inline-flex shrink-0 self-start rounded-xl border border-slate-200/80 bg-slate-100/80 p-1.5 md:self-auto">
            <button
                type="button"
                wire:click="switchMode('checkout')"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold transition-all {{ $activeMode === 'checkout' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Borrow (Check-Out)
            </button>
            <button
                type="button"
                wire:click="switchMode('checkin')"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold transition-all {{ $activeMode === 'checkin' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Return (Check-In)
            </button>
            <button
                type="button"
                wire:click="switchMode('active_loans')"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold transition-all {{ $activeMode === 'active_loans' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Loans & History Log
            </button>
        </div>
    </div>

    <!-- TAB 1: CHECKOUT MODE -->
    @if ($activeMode === 'checkout')
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
            <!-- Step 1: Borrower Identification -->
            <div class="flex flex-col justify-between space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full border border-indigo-100 bg-indigo-50 text-xs font-bold text-indigo-600">1</span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">Borrower Identification</h2>
                            <p class="text-xs text-slate-500">Scan RFID card, or search by student/employee number</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Scan or Input Student/Employee # / RFID</label>
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
                        @error('patronInput') <span class="text-xs text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if ($selectedPatron)
                        <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-900 capitalize">{{ $selectedPatron->first_name }} {{ $selectedPatron->last_name }}</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-800">
                                        {{ $selectedPatron->status }}
                                    </span>
                                    @if ($isTimedIn)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                            Timed In
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                                            Not Timed In
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 border-t border-slate-200/80 pt-3 text-xs text-slate-600">
                                @if ($selectedPatron->patronType?->name == "student")
                                    <div><span class="font-medium text-slate-400">Student #:</span> <span class="font-mono font-semibold text-slate-800">{{ $selectedPatron->school_id }}</span></div>
                                @else
                                    <div><span class="font-medium text-slate-400">Employee #:</span> <span class="font-mono font-semibold text-slate-800">{{ $selectedPatron->school_id }}</span></div>
                                @endif
                                <div><span class="font-medium text-slate-400">Type:</span> <span class="font-semibold text-slate-800 capitalize">{{ $selectedPatron->patronType?->name ?? 'N/A' }}</span></div>
                            </div>

                            @if (! $isTimedIn)
                                <div class="rounded-lg border border-rose-200 bg-rose-50 p-2.5 text-[11px] font-medium text-rose-700">
                                    <strong>Transaction Blocked:</strong> Borrower is not timed in at the entrance kiosk today.
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center">
                            <p class="text-xs font-medium text-slate-400">Awaiting borrower scan or search...</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Step 2: Book Accession Identification -->
            <div class="flex flex-col justify-between space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full border border-indigo-100 bg-indigo-50 text-xs font-bold text-indigo-600">2</span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">Book Accession</h2>
                            <p class="text-xs text-slate-500">Scan barcode or input accession number</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Scan or Input Accession No.</label>
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
                                wire:keydown.enter.prevent="processCheckout"
                                placeholder="Enter Accession No..."
                                class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            />
                        </div>
                        @error('accessionInput') <span class="text-xs text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if ($selectedAccession)
                        <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                            {{ $selectedAccession->catalog?->isbn_issn ? '<div class="text-medium text-slate-500 uppercase">ISBN/ISSN: ' . $selectedAccession->catalog?->isbn_issn . '</div>' : '' }}
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-bold leading-snug text-slate-900 capitalize italic">{{ $selectedAccession->catalog?->title ?? 'N/A' }}</span>
                                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ strtolower($selectedAccession->status ?? '') === 'available' ? 'border border-emerald-200 bg-emerald-100 text-emerald-800' : 'border border-amber-200 bg-amber-100 text-amber-800' }}">
                                    {{ $selectedAccession->status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold leading-snug text-slate-900 capitalize">Type: {{ $selectedAccession->catalog?->assetType?->name ?? 'N/A' }}</span>
                            </div>
                            <div class="text-xs text-slate-500 capitalize">
                                <span>Author: {{ $selectedAccession->catalog?->author?->name ?? 'N/A' }}</span>
                            </div>
                            <div class="text-xs text-slate-500 capitalize">
                                <span>Copyright Year: {{ $selectedAccession->catalog?->publication_year ?? 'N/A' }}</span>
                            </div>
                            <div class="text-xs text-slate-500 capitalize">
                                <span>General Reference: {{ $selectedAccession->catalog?->generalReference->name ?? 'N/A' }}</span>
                            </div>
                            <div class="text-xs text-slate-500 capitalize">
                                <span>Call Number: {{ $selectedAccession->call_number ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center">
                            <p class="text-xs font-medium text-slate-400">Awaiting book accession scan...</p>
                        </div>
                    @endif
                </div>

                <button
                    type="button"
                    wire:click="processCheckout"
                    wire:loading.attr="disabled"
                    @disabled(! $selectedPatron || ! $selectedAccession || strtolower($selectedAccession->status ?? '') !== 'available' || ! $isTimedIn)
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-40">
                    <svg wire:loading wire:target="processCheckout" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Complete Check-Out</span>
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
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Scan Return Accession Barcode</label>
                        <input
                            type="text"
                            wire:model="returnAccessionInput"
                            placeholder="Scan or enter Accession No..."
                            class="w-full rounded-xl border border-slate-300 py-2.5 px-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            autofocus
                        />
                        @error('returnAccessionInput') <span class="text-xs text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                        Inspect Loan
                    </button>
                </form>
            </div>

            <!-- Inspected Item Details & Action Card -->
            @if ($inspectedLoan)
                @php
                    $totalFineCalculated = (float) $overdueFineAmount + (float) ($returnCondition !== 'good' ? $manualFineAmount : 0);
                    $itemPrice = $inspectedLoan->accession?->acquisition?->unit_cost ?? 0.00;
                    $overdueDays = $inspectedLoan->due_at?->isPast() ? (int) $inspectedLoan->due_at->diffInDays(now()) : 0;
                @endphp
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Return Inspection</h3>
                            <p class="text-xs text-slate-500">Accession: <span class="font-mono font-medium text-slate-800 uppercase">{{ $inspectedLoan->accession?->accession_number ?? 'N/A' }}</span></p>
                        </div>
                        <button type="button" wire:click="cancelInspection" class="text-xs font-semibold text-slate-500 hover:text-slate-800 cursor-pointer">Cancel</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- LEFT SIDE: ITEM DETAILS & POLICY PENALTY SUMMARY -->
                        <div class="space-y-4 text-sm text-slate-600">
                            <div class="space-y-2">
                                <p><strong class="text-slate-800">Title:</strong> <span class="capitalize">{{ $inspectedLoan->accession?->catalog?->title ?? 'N/A' }}</span></p>
                                <p><strong class="text-slate-800">Borrower:</strong> <span class="capitalize">{{ $inspectedLoan->patron?->first_name }} {{ $inspectedLoan->patron?->last_name }}</span></p>
                                <p><strong class="text-slate-800">Type:</strong> <span class="capitalize">{{ $inspectedLoan->patron?->patronType?->name ?? 'N/A' }}</span></p>
                                <p><strong class="text-slate-800">Borrow Date:</strong> <span class="capitalize">{{ $inspectedLoan->borrowed_at?->format('M d, Y') ?? 'N/A' }}</span></p>
                                @if ($inspectedLoan->patron?->patronType?->name)
                                    @if (strtolower($inspectedLoan->patron?->patronType?->name) === 'student')
                                        <p><strong class="text-slate-800">Due Date:</strong> <span class="capitalize">{{ $inspectedLoan->due_at?->format('M d, Y') ?? 'N/A' }}</span></p>
                                    @endif
                                @endif
                                <p><strong class="text-slate-800">Book Cost/Price:</strong> <span class="font-semibold text-indigo-700">₱{{ number_format((float)$itemPrice, 2) }}</span></p>
                            </div>

                            <!-- PENALTY BREAKDOWN SUMMARY -->
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 space-y-2 text-xs">
                                <div class="font-bold text-slate-800 uppercase tracking-wider text-[11px] border-b border-slate-200 pb-1.5">
                                    Penalty Policy Calculation
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Overdue Day(s):</span>
                                    <span class="font-semibold text-slate-800">{{ $overdueDays }} day(s)</span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>Overdue Fine (Policy Rate):</span>
                                    <span class="font-semibold text-slate-800">₱{{ number_format($overdueFineAmount, 2) }}</span>
                                </div>
                                @if ($returnCondition !== 'good')
                                    <div class="flex justify-between text-slate-600">
                                        <span>Condition Assessment Fine:</span>
                                        <span class="font-semibold text-slate-800">₱{{ number_format((float)$manualFineAmount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between pt-1 border-t border-slate-200 font-bold text-slate-900">
                                    <span>Total Calculated Fine:</span>
                                    <span class="text-indigo-600">₱{{ number_format($totalFineCalculated, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT SIDE: CONDITION & FINE INPUT CONTROLS -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Return Condition</label>
                                <select wire:model.live="returnCondition" class="w-full rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none">
                                    <option value="good">Good Condition</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="lost">Lost</option>
                                </select>
                            </div>

                            <!-- MANUAL FINE FIELD -->
                            @if ($returnCondition !== 'good')
                                <div class="space-y-1">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Additional Condition Fine / Fee</label>
                                    <input type="number" step="0.01" min="0" wire:model.live="manualFineAmount" placeholder="Enter fine amount..." class="w-full rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none" />
                                    <p class="text-[11px] text-slate-400">Reference item cost above (₱{{ number_format((float)$itemPrice, 2) }}) when assessing replacement or repair fees.</p>
                                </div>
                            @endif

                            <!-- FINE & PAYMENT SETTLEMENT SECTION -->
                            @if ($totalFineCalculated > 0)
                                <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-amber-900 uppercase">Total Fine Assessed:</span>
                                        <span class="text-base font-extrabold text-amber-800">₱{{ number_format($totalFineCalculated, 2) }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="checkbox" id="isPaidNow" wire:model.live="isPaidNow" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                                        <label for="isPaidNow" class="text-xs font-medium text-slate-800 cursor-pointer">Settle payment now at check-in</label>
                                    </div>

                                    @if ($isPaidNow)
                                        <div class="space-y-1 pt-1">
                                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Receipt # / OR Number <span class="text-rose-600">*</span></label>
                                            <input type="text" wire:model="checkinReceiptNumber" placeholder="e.g. TRX-123456" class="w-full rounded-lg border border-slate-300 py-1.5 px-3 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none" />
                                            @error('checkinReceiptNumber') <span class="text-xs text-rose-600 block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <button type="button" wire:click="processCheckin" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                            Confirm Return
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
                    placeholder="Search by borrower, accession, receipt #..."
                    class="w-full sm:w-80 rounded-xl border border-slate-300 py-2 px-4 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:outline-none"
                />

                <div class="flex items-center gap-3">
                    <select wire:model.live="filterStatus" class="rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none">
                        <option value="borrowed">Active Loans</option>
                        <option value="returned">Returned</option>
                        <option value="overdue">Overdue</option>
                        <option value="all">All Logs</option>
                    </select>

                    <!-- EXPORT DROPDOWN (ALPINE.JS) -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button
                            type="button"
                            @click="open = !open"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition shadow-xs">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export
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
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-xs font-medium text-slate-700 hover:bg-slate-50">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export Excel
                            </button>
                            <button
                                type="button"
                                wire:click="exportPdf"
                                @click="open = false"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-xs font-medium text-slate-700 hover:bg-slate-50">
                                <svg class="h-4 w-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Export PDF
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
                            <th class="px-4 py-3 whitespace-nowrap">Accession #</th>
                            <th class="px-4 py-3 whitespace-nowrap">Book Title</th>
                            <th class="px-4 py-3 whitespace-nowrap">Student/Employee #</th>
                            <th class="px-4 py-3 whitespace-nowrap">Name</th>
                            <th class="px-4 py-3 whitespace-nowrap">Type</th>
                            <th class="px-4 py-3 whitespace-nowrap">Borrowed Date</th>
                            <th class="px-4 py-3 whitespace-nowrap">Due Date</th>
                            <th class="px-4 py-3 whitespace-nowrap">Status</th>
                            @if ($filterStatus === 'returned' || $filterStatus === 'all')
                            <th class="px-4 py-3 whitespace-nowrap">Returned Date</th>
                            <th class="px-4 py-3 whitespace-nowrap">Receipt #</th>
                            <th class="px-4 py-3 whitespace-nowrap">Payment</th>
                            <th class="px-4 py-3 whitespace-nowrap">Fine Amount</th>
                            @endif
                            <th class="px-4 py-3 whitespace-nowrap">Condition</th>
                            <th class="px-4 py-3 whitespace-nowrap">Processed By</th>
                            <th class="px-4 py-3 whitespace-nowrap text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 border-t border-slate-100">
                        @forelse ($activeLoans as $loan)
                            @php
                                $isStudent = strtolower($loan->patron?->patronType?->name ?? '') === 'student';
                                $statusLower = strtolower($loan->status ?? '');
                                $conditionLower = strtolower($loan->condition ?? 'good');
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 font-mono font-medium text-slate-900 uppercase whitespace-nowrap">
                                    {{ $loan->accession?->accession_number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800 capitalize whitespace-nowrap">
                                    {{ $loan->accession?->catalog?->title ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $loan->patron?->school_id ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="capitalize">{{ $loan->patron?->first_name }} {{ $loan->patron?->middle_name ?? '' }} {{ $loan->patron?->last_name }}</span> {{ strtoupper($loan->patron?->suffix) ?? '' }}
                                </td>
                                <td class="px-4 py-3 capitalize whitespace-nowrap">
                                    {{ $loan->patron?->patronType?->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $loan->borrowed_at?->format('M d, Y h:i A') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $isStudent ? ($loan->due_at?->format('M d, Y') ?? '-') : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ match($statusLower) { 'borrowed' => 'bg-indigo-100 text-indigo-800 border-indigo-200', 'returned' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'overdue' => 'bg-rose-100 text-rose-800 border-rose-200', default => 'bg-slate-100 text-slate-800 border-slate-200' } }}">
                                        {{ $loan->status }}
                                    </span>
                                </td>
                                @if ($filterStatus === 'returned' || $filterStatus === 'all')
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $loan->returned_at?->format('M d, Y h:i A') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">
                                    {{ $loan->receipt_number ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">
                                    @if ($loan->returned_at && $loan->fine_amount > 0)
                                        {{ $loan->is_paid ? 'Paid' : 'Unpaid' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold whitespace-nowrap">
                                    @if ($loan->returned_at && $loan->fine_amount > 0)
                                        <span class="text-red-600">₱{{ number_format((float) $loan->fine_amount, 2) }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-medium capitalize {{ match($conditionLower) { 'good' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'damaged' => 'bg-amber-50 text-amber-700 border-amber-200', 'lost' => 'bg-rose-50 text-rose-700 border-rose-200', default => 'bg-slate-50 text-slate-700 border-slate-200' } }}">
                                        {{ ucfirst($loan->condition ?? 'Good') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">
                                    {{ $loan->user?->getFullNameAttribute() ?? 'System User' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    @if (in_array($statusLower, ['borrowed', 'overdue']))
                                        <button type="button" wire:click="quickInspect('{{ $loan->accession?->accession_number }}')" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                            Return / Inspect
                                        </button>
                                    @elseif ($statusLower === 'returned' && !$loan->is_paid && in_array($conditionLower, ['damaged', 'lost']))
                                        <button type="button" wire:click="payFine({{ $loan->id }})" class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 shadow-2xs hover:bg-amber-100 transition">
                                            Settle Fine
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $filterStatus === 'returned' || $filterStatus === 'all' ? '15' : '11' }}" class="px-4 py-8 text-center text-xs font-medium text-slate-400">
                                    No circulation logs found matching criteria.
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
                    <h3 class="text-base font-bold text-slate-900">Settle Unpaid Fine</h3>
                    <button type="button" wire:click="closeEditPaymentModal" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Fine Amount (₱)</label>
                        <input type="number" step="0.01" min="0" wire:model="editFineAmount" class="w-full rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none" />
                        @error('editFineAmount') <span class="text-xs text-rose-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="isEditPaid" wire:model.live="isEditPaid" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        <label for="isEditPaid" class="text-xs font-medium text-slate-800 cursor-pointer">Mark as paid & settled</label>
                    </div>

                    @if ($isEditPaid)
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Receipt # / OR Number <span class="text-rose-600">*</span></label>
                            <input type="text" wire:model="editReceiptNumber" placeholder="e.g. OR-987654" class="w-full rounded-xl border border-slate-300 py-2 px-3 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none" />
                            @error('editReceiptNumber') <span class="text-xs text-rose-600 block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" wire:click="closeEditPaymentModal" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="button" wire:click="updatePayment" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">Save Changes</button>
                </div>
            </div>
        </div>
    @endif
</div>
