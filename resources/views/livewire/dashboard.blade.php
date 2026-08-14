<div class="space-y-6">

    {{-- Header & Quick Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1">Overview of library activity, circulation, and metrics.</p>
        </div>
    </div>

    {{-- Key Stats / Metrics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Assets --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Assets</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalBooks) }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 font-bold flex items-center justify-center shrink-0">
                📚
            </div>
        </div>

        {{-- Active Borrows --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Borrows</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($activeBorrows) }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 font-bold flex items-center justify-center shrink-0">
                📖
            </div>
        </div>

        {{-- Overdue Assets --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Overdue Assets</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($overdueBooks) }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center shrink-0">
                ⚠️
            </div>
        </div>

        {{-- Total Patrons --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Patrons</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalPatrons) }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center shrink-0">
                👥
            </div>
        </div>
    </div>

    {{-- Graphical Section: Side-by-Side Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <livewire:dashboard.accession-chart />
        <livewire:dashboard.accession-trend-chart />
    </div>

    {{-- Layout Grid: Overdue Alerts (1/3) & Recent Transactions (2/3) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Overdue Alerts Section --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col h-full">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                    Overdue Alerts
                </h2>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-100">
                    {{ $overdueAlerts->count() }} Urgent
                </span>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-[380px]">
                @forelse ($overdueAlerts as $alert)
                    <div class="p-3 rounded-xl border border-rose-100 bg-rose-50/40 flex flex-col justify-between gap-2">
                        <div>
                            <p class="text-xs font-bold text-slate-900">
                                {{ $alert->patron->first_name ?? 'Patron' }} {{ $alert->patron->last_name ?? '' }}
                            </p>
                            <p class="text-xs text-slate-600 truncate mt-0.5">
                                {{ $alert->accession->catalog->title ?? 'Unknown Title' }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-rose-600 font-medium pt-1 border-t border-rose-100/80">
                            <span>Due: {{ $alert->due_at ? $alert->due_at->format('M d, Y') : 'N/A' }}</span>
                            <span>{{ $alert->due_at ? $alert->due_at->diffForHumans() : '' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-400 text-xs">
                        No overdue assets right now! 🎉
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity / Transactions Section --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="text-base font-bold text-slate-900">Recent Transactions</h2>

                {{-- Search Filter --}}
                <div class="relative max-w-xs w-full">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search patron, book, or transaction #..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none"
                    />
                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse text-xs text-slate-600">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                            <th class="p-3 pl-4">Patron</th>
                            <th class="p-3">Book Title</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Fine</th>
                            <th class="p-3 pr-4">Due Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentTransactions as $transaction)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="p-3 pl-4 font-medium text-slate-900 whitespace-nowrap">
                                    <div class="font-bold">{{ $transaction->patron->first_name ?? 'N/A' }} {{ $transaction->patron->last_name ?? '' }}</div>
                                    @if($transaction->transaction_number)
                                        <div class="text-[10px] text-slate-400 font-mono">TX: {{ $transaction->transaction_number }}</div>
                                    @endif
                                </td>
                                <td class="p-3 text-slate-700 max-w-[200px] truncate whitespace-nowrap">
                                    <div class="font-semibold">{{ $transaction->accession->catalog->title ?? 'Unknown Title' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">Acc #: {{ $transaction->accession->accession_number ?? 'N/A' }}</div>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    @switch($transaction->status)
                                        @case('returned')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">RETURNED</span>
                                            @break
                                        @case('borrowed')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">BORROWED</span>
                                            @break
                                        @case('overdue')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">OVERDUE</span>
                                            @break
                                        @case('lost')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-800">LOST</span>
                                            @break
                                        @default
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">{{ strtoupper($transaction->status) }}</span>
                                    @endswitch
                                </td>
                                <td class="p-3 font-mono whitespace-nowrap">
                                    @if($transaction->fine_amount > 0)
                                        <span class="text-rose-600 font-bold">₱{{ number_format($transaction->fine_amount, 2) }}</span>
                                    @else
                                        <span class="text-slate-400">₱0.00</span>
                                    @endif
                                </td>
                                <td class="p-3 pr-4 text-slate-500 whitespace-nowrap">
                                    {{ $transaction->due_at ? $transaction->due_at->format('M d, Y') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-slate-400">
                                    No recent circulation transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Safe Pagination Rendering --}}
            <div class="pt-2">
                {{ $recentTransactions->links() }}
            </div>
        </div>

    </div>
</div>
