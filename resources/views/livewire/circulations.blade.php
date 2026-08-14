<div class="space-y-6 p-4 md:p-6">
    <!-- CIRCULATION DESK HEADER BAR -->
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Circulation Desk</h1>
            <p class="mt-1 text-sm text-slate-500">Manage book borrowing, condition inspections, penalties, and historical loan records.</p>
        </div>

        <!-- Mode Switcher -->
        <div class="inline-flex self-start rounded-xl border border-slate-200/80 bg-slate-100/80 p-1.5 shrink-0 md:self-auto">
            <button
                wire:click="$set('activeMode', 'checkout')"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold transition-all {{ $activeMode === 'checkout' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Borrow (Check-Out)
            </button>
            <button
                wire:click="$set('activeMode', 'checkin')"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold transition-all {{ $activeMode === 'checkin' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900' }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Return (Check-In)
            </button>
            <button
                wire:click="$set('activeMode', 'active_loans')"
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
            <!-- Step 1: Patron Identification -->
            <div class="flex flex-col justify-between space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full border border-indigo-100 bg-indigo-50 text-xs font-bold text-indigo-600">1</span>
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">Patron Identification</h2>
                            <p class="text-xs text-slate-500">Scan card or search by patron ID</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Scan or Input Patron ID</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="patronInput"
                                placeholder="Enter Patron ID..."
                                class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                autofocus
                            />
                        </div>
                    </div>

                    @if ($selectedPatron)
                        <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-900">{{ $selectedPatron->first_name }} {{ $selectedPatron->last_name }}</span>
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-800">
                                    {{ $selectedPatron->status }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 border-t border-slate-200/80 pt-3 text-xs text-slate-600">
                                <div><span class="font-medium text-slate-400">Patron ID:</span> <span class="font-mono font-semibold text-slate-800">{{ $selectedPatron->patron_id }}</span></div>
                                <div><span class="font-medium text-slate-400">Type:</span> <span class="font-semibold text-slate-800">{{ $selectedPatron->patronType->name ?? 'N/A' }}</span></div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center">
                            <p class="text-xs font-medium text-slate-400">Awaiting patron scan or search...</p>
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
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="accessionInput"
                                placeholder="Enter Accession No..."
                                class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            />
                        </div>
                    </div>

                    @if ($selectedAccession)
                        <div class="space-y-2 rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-slate-900 leading-snug">{{ $selectedAccession->catalog->title ?? 'N/A' }}</span>
                                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $selectedAccession->status === 'Available' ? 'border border-emerald-200 bg-emerald-100 text-emerald-800' : 'border border-amber-200 bg-amber-100 text-amber-800' }}">
                                    {{ $selectedAccession->status }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center">
                            <p class="text-xs font-medium text-slate-400">Awaiting book accession scan...</p>
                        </div>
                    @endif
                </div>

                <button
                    wire:click="processCheckout"
                    @disabled(! $selectedPatron || ! $selectedAccession || $selectedAccession->status !== 'Available')
                    class="mt-4 w-full rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-40">
                    Issue Book (Complete Check-Out)
                </button>
            </div>
        </div>
    @endif

    <!-- TAB 2: CHECKIN MODE -->
    @if ($activeMode === 'checkin')
        <div class="mx-auto max-w-2xl space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900">Return Book Inspection</h2>
                <p class="mt-0.5 text-xs text-slate-500">Scan accession barcode to inspect condition, apply penalties, and complete return.</p>
            </div>

            <!-- Accession Scan Input -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Scan / Input Accession Barcode</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model="returnAccessionInput"
                            wire:keydown.enter="inspectReturn"
                            placeholder="Scan accession barcode..."
                            class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            autofocus
                        />
                    </div>
                    <button
                        wire:click="inspectReturn"
                        class="shrink-0 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900">
                        Inspect Loan
                    </button>
                </div>
            </div>

            <!-- Inspection & Fine Evaluation Form -->
            @if ($inspectedLoan)
                <div class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50/80 p-5">
                    <div class="flex items-start justify-between border-b border-slate-200/80 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ $inspectedLoan->accession->catalog->title ?? 'N/A' }}</h3>
                            <p class="mt-1 text-xs text-slate-500">Borrowed by: <strong class="font-semibold text-slate-800">{{ $inspectedLoan->patron->first_name }} {{ $inspectedLoan->patron->last_name }}</strong></p>
                        </div>
                        <span class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 font-mono text-xs font-bold text-slate-700 shadow-2xs">
                            {{ $inspectedLoan->accession->accession_number }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Asset Condition -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-600">Returned Condition</label>
                            <select wire:model.live="returnCondition" class="w-full rounded-xl border-slate-300 py-2 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="New">New</option>
                                <option value="Good">Good Condition</option>
                                <option value="Fair">Fair Condition</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Missing">Missing / Lost</option>
                            </select>
                        </div>

                        <!-- Manual / Condition Fine -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-600">Condition / Damage Fine (₱)</label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model.live="manualFineAmount"
                                placeholder="0.00"
                                class="w-full rounded-xl border-slate-300 py-2 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            />
                        </div>
                    </div>

                    <!-- Fines & Penalties Summary -->
                    <div class="space-y-1.5 rounded-xl border border-amber-200/80 bg-amber-50/80 p-4 text-xs text-amber-900">
                        <div class="flex justify-between">
                            <span class="text-amber-800">Overdue Penalty (Policy):</span>
                            <span class="font-bold">₱{{ number_format($overdueFineAmount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-amber-800">Condition Penalty:</span>
                            <span class="font-bold">₱{{ number_format((float)$manualFineAmount, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-amber-200/80 pt-2 text-sm font-extrabold text-amber-950">
                            <span>Total Fine:</span>
                            <span>₱{{ number_format($overdueFineAmount + (float)$manualFineAmount, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            wire:click="cancelInspection"
                            class="w-1/3 rounded-xl bg-slate-200/80 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-300/80 focus:outline-none focus:ring-2 focus:ring-slate-300">
                            Cancel
                        </button>
                        <button
                            wire:click="processCheckin"
                            class="w-2/3 rounded-xl bg-emerald-600 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Confirm Return & Update Status
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 3: ACTIVE LOANS & CIRCULATION HISTORY -->
    @if ($activeMode === 'active_loans')
        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- Search Box -->
                <div class="relative flex-1 max-w-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search patron or book title..."
                        class="w-full rounded-xl border border-slate-300 py-2 pl-9 pr-4 text-xs text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    />
                </div>

                <!-- Status Filter Dropdown -->
                <select wire:model.live="filterStatus" class="rounded-xl border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="borrowed">Active Borrowed Items</option>
                    <option value="returned">Returned / History Log</option>
                    <option value="overdue">Overdue Items</option>
                    <option value="all">All Records</option>
                </select>
            </div>

            <!-- Data Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="p-3.5 pl-4">Patron</th>
                            <th class="p-3.5">Book Title</th>
                            <th class="p-3.5">Accession No.</th>
                            <th class="p-3.5">Borrowed At</th>
                            <th class="p-3.5">Due Date</th>
                            <th class="p-3.5">Returned At</th>
                            <th class="p-3.5">Fine Amount</th>
                            <th class="p-3.5 pr-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($activeLoans as $loan)
                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="p-3.5 pl-4 font-bold text-slate-900">
                                    {{ $loan->patron->first_name ?? '' }} {{ $loan->patron->last_name ?? '' }}
                                </td>
                                <td class="p-3.5 font-medium text-slate-800">{{ $loan->accession->catalog->title ?? 'N/A' }}</td>
                                <td class="p-3.5 font-mono text-[11px] text-slate-500">{{ $loan->accession->accession_number ?? 'N/A' }}</td>
                                <td class="p-3.5 text-slate-500">{{ $loan->borrowed_at ? \Carbon\Carbon::parse($loan->borrowed_at)->format('M d, Y') : '—' }}</td>
                                <td class="p-3.5 text-slate-500">{{ $loan->due_at ? \Carbon\Carbon::parse($loan->due_at)->format('M d, Y') : '—' }}</td>
                                <td class="p-3.5 text-slate-500">
                                    {{ $loan->returned_at ? \Carbon\Carbon::parse($loan->returned_at)->format('M d, Y h:i A') : '—' }}
                                </td>
                                <td class="p-3.5 font-semibold {{ $loan->fine_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    ₱{{ number_format($loan->fine_amount, 2) }}
                                </td>
                                <td class="p-3.5 pr-4 text-right whitespace-nowrap">
                                    @if ($loan->status === 'returned')
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800">
                                            RETURNED
                                        </span>
                                    @elseif ($loan->status === 'lost')
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-800">
                                            LOST
                                        </span>
                                    @elseif (\Carbon\Carbon::parse($loan->due_at)->isPast() || $loan->status === 'overdue')
                                        <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-100 px-2.5 py-0.5 text-[10px] font-bold text-rose-700">
                                            OVERDUE
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-blue-700">
                                            BORROWED
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-600">No circulation records found</p>
                                    <p class="mt-0.5 text-[11px] text-slate-400">Try refining your search terms or filter selection.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $activeLoans->links() }}
            </div>
        </div>
    @endif
</div>
