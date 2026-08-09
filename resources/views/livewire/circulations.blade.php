<div class="p-6 max-w-7xl mx-auto space-y-6">
    <!-- Circulation Desk Header Bar -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Circulation Desk</h1>
            <p class="text-sm text-slate-500 mt-0.5">Manage book borrowing, condition returns, fines, and historical loan status.</p>
        </div>

        <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/60 shrink-0">
            <button
                wire:click="$set('activeMode', 'checkout')"
                class="px-4 py-2 text-xs font-semibold rounded-lg transition-all {{ $activeMode === 'checkout' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Borrow (Check-Out)
            </button>
            <button
                wire:click="$set('activeMode', 'checkin')"
                class="px-4 py-2 text-xs font-semibold rounded-lg transition-all {{ $activeMode === 'checkin' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Return (Check-In)
            </button>
            <button
                wire:click="$set('activeMode', 'active_loans')"
                class="px-4 py-2 text-xs font-semibold rounded-lg transition-all {{ $activeMode === 'active_loans' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Loans & History Log
            </button>
        </div>
    </div>

    <!-- TAB 1: CHECKOUT MODE -->
    @if ($activeMode === 'checkout')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <!-- Step 1: Patron Identification -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">1</span>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Patron Identification</h2>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Scan or Input Patron ID</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="patronInput"
                        placeholder="Enter Patron ID..."
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-slate-800 transition"
                        autofocus
                    >
                </div>

                @if ($selectedPatron)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 text-sm">{{ $selectedPatron->first_name }} {{ $selectedPatron->last_name }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                {{ strtoupper($selectedPatron->status) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 pt-1 border-t border-slate-200/60">
                            <div><span class="text-slate-400">Patron ID:</span> <span class="font-medium text-slate-700">{{ $selectedPatron->patron_id }}</span></div>
                            <div><span class="text-slate-400">Type:</span> <span class="font-medium text-slate-700">{{ $selectedPatron->patronType->name ?? 'N/A' }}</span></div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Step 2: Book Accession Identification -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">2</span>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Book Accession</h2>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Scan or Input Accession No.</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="accessionInput"
                        placeholder="Enter Accession No..."
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-slate-800 transition"
                    >
                </div>

                @if ($selectedAccession)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 text-sm">{{ $selectedAccession->catalog->title ?? 'N/A' }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $selectedAccession->status === 'Available' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ strtoupper($selectedAccession->status) }}
                            </span>
                        </div>
                    </div>
                @endif

                <button
                    wire:click="processCheckout"
                    @disabled(! $selectedPatron || ! $selectedAccession || $selectedAccession->status !== 'Available')
                    class="w-full py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-40 transition text-sm shadow-sm">
                    Issue Book (Complete Check-Out)
                </button>
            </div>
        </div>
    @endif

    <!-- TAB 2: CHECKIN MODE (WITH CONDITION & FINES) -->
    @if ($activeMode === 'checkin')
        <div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-lg font-bold text-slate-900">Return Book Inspection</h2>
                <p class="text-xs text-slate-500">Scan accession barcode to inspect condition, apply penalties, and complete return.</p>
            </div>

            <!-- Accession Scan Input -->
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-500 uppercase">Scan / Input Accession Barcode</label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model="returnAccessionInput"
                        wire:keydown.enter="inspectReturn"
                        placeholder="Scan accession barcode..."
                        class="flex-1 px-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-slate-800"
                        autofocus
                    >
                    <button
                        wire:click="inspectReturn"
                        class="px-5 py-2.5 bg-slate-800 text-white rounded-xl font-semibold hover:bg-slate-900 transition text-xs shrink-0">
                        Inspect Loan
                    </button>
                </div>
            </div>

            <!-- Inspection & Fine Evaluation Form -->
            @if ($inspectedLoan)
                <div class="p-5 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-4">
                    <div class="flex justify-between items-start border-b pb-3 border-slate-200/70">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">{{ $inspectedLoan->accession->catalog->title ?? 'N/A' }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Borrowed by: <strong class="text-slate-800">{{ $inspectedLoan->patron->first_name }} {{ $inspectedLoan->patron->last_name }}</strong></p>
                        </div>
                        <span class="font-mono text-xs font-bold bg-white px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600">
                            {{ $inspectedLoan->accession->accession_number }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Asset Condition -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-600">Returned Condition</label>
                            <select wire:model.live="returnCondition" class="w-full text-xs font-medium rounded-xl border-slate-200 focus:border-blue-500">
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
                                class="w-full text-xs font-medium rounded-xl border-slate-200 focus:border-blue-500"
                            >
                        </div>
                    </div>

                    <!-- Fines & Penalties Summary -->
                    <div class="p-3.5 bg-amber-50/60 rounded-xl border border-amber-200/70 space-y-1 text-xs text-amber-900">
                        <div class="flex justify-between">
                            <span>Overdue Penalty (Policy):</span>
                            <span class="font-bold">₱{{ number_format($overdueFineAmount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Condition Penalty:</span>
                            <span class="font-bold">₱{{ number_format((float)$manualFineAmount, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-amber-200 pt-1 text-sm font-extrabold text-amber-950">
                            <span>Total Fine:</span>
                            <span>₱{{ number_format($overdueFineAmount + (float)$manualFineAmount, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button
                            wire:click="cancelInspection"
                            class="w-1/3 py-2.5 bg-slate-200 text-slate-700 rounded-xl font-semibold hover:bg-slate-300 transition text-xs">
                            Cancel
                        </button>
                        <button
                            wire:click="processCheckin"
                            class="w-2/3 py-2.5 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition text-xs shadow-sm">
                            Confirm Return & Update Status
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 3: ACTIVE LOANS & CIRCULATION HISTORY -->
    @if ($activeMode === 'active_loans')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-sm">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search patron or book title..."
                        class="w-full pl-3.5 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 text-slate-800"
                    >
                </div>

                <!-- Status Filter Dropdown -->
                <select wire:model.live="filterStatus" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:border-blue-500">
                    <option value="borrowed">Active Borrowed Items</option>
                    <option value="returned">Returned / History Log</option>
                    <option value="overdue">Overdue Items</option>
                    <option value="all">All Records</option>
                </select>
            </div>

            <!-- Data Table -->
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
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
                            <tr class="hover:bg-slate-50/60 transition-colors">
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
                                <td class="p-3.5 pr-4 text-right">
                                    @if ($loan->status === 'returned')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            RETURNED
                                        </span>
                                    @elseif ($loan->status === 'lost')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800">
                                            LOST
                                        </span>
                                    @elseif (\Carbon\Carbon::parse($loan->due_at)->isPast() || $loan->status === 'overdue')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-700">
                                            OVERDUE
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700">
                                            BORROWED
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-400">No circulation records found matching your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $activeLoans->links() }}
            </div>
        </div>
    @endif
</div>
