<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6 bg-slate-50 min-h-screen">
    {{-- Header & Profile Info Bar --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white font-bold flex items-center justify-center text-lg shadow-md shadow-blue-500/20 shrink-0">
                {{ strtoupper(substr(optional($patron)->first_name ?? 'P', 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900">{{ optional($patron)->first_name ?? 'Patron' }} {{ optional($patron)->last_name }}</h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Patron ID: <span class="text-slate-800 font-semibold">{{ optional($patron)->patron_id ?? 'N/A' }}</span> •
                    Type: <span class="text-blue-600 font-semibold">{{ $patron->patronType->name ?? 'Standard' }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- Navigation Tabs --}}
            <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/60 shrink-0">
                <button
                    wire:click="$set('activeTab', 'opac')"
                    class="px-4 py-2 text-xs font-semibold rounded-lg transition-all cursor-pointer {{ $activeTab === 'opac' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    🔍 OPAC (Book Catalog)
                </button>
                <button
                    wire:click="$set('activeTab', 'transactions')"
                    class="px-4 py-2 text-xs font-semibold rounded-lg transition-all cursor-pointer {{ $activeTab === 'transactions' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    📖 My Transactions
                </button>
            </div>

            <button
                wire:click="logout"
                class="px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition border border-rose-200 cursor-pointer">
                Logout
            </button>
        </div>
    </div>

    {{-- TAB 1: OPAC (ONLINE PUBLIC ACCESS CATALOG) --}}
    @if ($activeTab === 'opac')
        <div class="space-y-6">
            {{-- OPAC Search Bar --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="opacSearch"
                        placeholder="Search books by title, author, or ISBN..."
                        class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-slate-800 outline-none transition"
                    >
                </div>
            </div>

            {{-- OPAC Books Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                @forelse ($catalogItems as $item)
                    @php
                        $accessions = optional($item->accessions);
                        $availableCount = $accessions ? $accessions->where('status', 'Available')->count() : 0;
                        $totalCount = $accessions ? $accessions->count() : 0;
                    @endphp
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-blue-300 transition group">
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    {{ $item->assetType->name ?? 'Book' }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $availableCount > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $availableCount > 0 ? "{$availableCount} / {$totalCount} Available" : 'Out of Stock' }}
                                </span>
                            </div>

                            <div>
                                <h3 class="font-bold text-slate-900 text-sm line-clamp-2 group-hover:text-blue-600 transition">{{ $item->title }}</h3>
                                <p class="text-xs text-slate-500 mt-1">by {{ $item->author->name ?? 'Unknown Author' }}</p>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                            <span>ISBN: <strong class="text-slate-700">{{ $item->isbn_issn ?? 'N/A' }}</strong></span>
                            <span>Edition: <strong class="text-slate-700">{{ $item->edition ?? '—' }}</strong></span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-slate-200/80 text-slate-400">
                        No books or library catalog items found matching your search.
                    </div>
                @endforelse
            </div>

            <div>
                {{ $catalogItems->links() }}
            </div>
        </div>
    @endif

    {{-- TAB 2: VIEW CURRENT TRANSACTIONS --}}
    @if ($activeTab === 'transactions')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b pb-4 border-slate-100 gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">My Borrowing Transactions</h2>
                    <p class="text-xs text-slate-500">View active items currently in your possession and historical transactions.</p>
                </div>

                <select wire:model.live="transactionFilter" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:border-blue-500 outline-none">
                    <option value="active">Active Borrowed Items</option>
                    <option value="history">Returned / Past History Log</option>
                </select>
            </div>

            {{-- Transactions Table --}}
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                        <tr>
                            <th class="p-3.5">Accession No.</th>
                            <th class="p-3.5 pl-4">Book Title</th>
                            <th class="p-3.5">Author</th>
                            <th class="p-3.5">ISBN</th>
                            <th class="p-3.5">Borrowed Date</th>
                            <th class="p-3.5">Due Date</th>
                            <th class="p-3.5">Returned Date</th>
                            <th class="p-3.5">Fine / Penalty</th>
                            <th class="p-3.5 pr-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($myLoans as $loan)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-3.5 font-mono text-[11px] text-slate-500">{{ $loan->accession->accession_number ?? 'N/A' }}</td>
                                <td class="p-3.5 pl-4 font-bold text-slate-900">
                                    {{ $loan->accession->catalog->title ?? 'N/A' }}
                                </td>
                                <td class="p-3.5 text-slate-500">{{ $loan->accession->catalog->author ?? 'N/A' }}</td>
                                <td class="p-3.5 text-slate-500">{{ $loan->accession->catalog->isbn ?? 'N/A' }}</td>
                                <td class="p-3.5 text-slate-500">{{ $loan->borrowed_at ? \Carbon\Carbon::parse($loan->borrowed_at)->format('M d, Y') : '—' }}</td>
                                <td class="p-3.5 text-slate-500">{{ $loan->due_at ? \Carbon\Carbon::parse($loan->due_at)->format('M d, Y') : '—' }}</td>
                                <td class="p-3.5 text-slate-500">
                                    {{ $loan->returned_at ? \Carbon\Carbon::parse($loan->returned_at)->format('M d, Y h:i A') : '—' }}
                                </td>
                                <td class="p-3.5 font-semibold {{ ($loan->fine_amount ?? 0) > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    ₱{{ number_format($loan->fine_amount ?? 0, 2) }}
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
                                    @elseif (($loan->due_at && \Carbon\Carbon::parse($loan->due_at)->isPast()) || $loan->status === 'overdue')
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
                                <td colspan="7" class="text-center py-8 text-slate-400">No circulation records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $myLoans->links() }}
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <div class="text-center text-[11px] text-slate-400 pt-4">
        &copy; {{ date('Y') }} Bicutan Parochial School, Inc. All rights reserved.
    </div>
</div>
