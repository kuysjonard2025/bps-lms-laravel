<div class="p-6 space-y-6">

    {{-- Header & Quick Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500">Overview of library activity and management actions.</p>
        </div>
    </div>

    {{-- Key Stats / Metrics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Books --}}
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Assets</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalBooks) }}</p>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
        </div>

        {{-- Active Borrows --}}
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Borrows</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($activeBorrows) }}</p>
            </div>
            <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>

        {{-- Overdue Books --}}
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Overdue Assets</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($overdueBooks) }}</p>
            </div>
            <div class="p-3 bg-red-50 text-red-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        {{-- Total Patrons --}}
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Patrons</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalPatrons) }}</p>
            </div>
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
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
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 flex flex-col h-full">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    Overdue Alerts
                </h2>
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-50 text-red-700">{{ $overdueAlerts->count() }} Urgent</span>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-[380px]">
                @forelse ($overdueAlerts as $alert)
                    <div class="p-3 rounded-lg border border-red-100 bg-red-50/50 flex flex-col justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-gray-900">{{ $alert->user->first_name }} {{ $alert->user->last_name }}</p>
                            <p class="text-xs text-gray-600 truncate">{{ $alert->book->title }}</p>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-red-600 font-medium pt-1 border-t border-red-100">
                            <span>Due: {{ $alert->due_date ? \Carbon\Carbon::parse($alert->due_date)->format('M d, Y') : 'N/A' }}</span>
                            <span>{{ $alert->due_date ? \Carbon\Carbon::parse($alert->due_date)->diffForHumans() : '' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 text-xs">
                        No overdue books at the moment! 🎉
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity / Transactions Section --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-xs p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="text-base font-bold text-gray-900">Recent Transactions</h2>

                {{-- Search Filter --}}
                <div class="relative max-w-xs w-full">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search patron or book title..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-semibold uppercase tracking-wider">
                            <th class="p-3">Patron</th>
                            <th class="p-3">Book</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Condition</th>
                            <th class="p-3">Due Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentTransactions as $transaction)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="p-3 font-medium text-gray-900">
                                    {{ $transaction->user->first_name }} {{ $transaction->user->last_name }}
                                </td>
                                <td class="p-3 text-gray-600 max-w-[200px] truncate">
                                    {{ $transaction->book->title }}
                                </td>
                                <td class="p-3">
                                    @if ($transaction->status === 'returned')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Returned</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">Borrowed</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if ($transaction->due_date && \Carbon\Carbon::parse($transaction->due_date)->isPast())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-800">Overdue</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-800">Good</span>
                                    @endif
                                </td>
                                <td class="p-3 text-gray-500">
                                    {{ $transaction->due_date ? \Carbon\Carbon::parse($transaction->due_date)->format('M d, Y') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-gray-400">
                                    No recent transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Safe Pagination Rendering --}}
            @if (method_exists($recentTransactions, 'links'))
                <div class="pt-2">
                    {{ $recentTransactions->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
