<div class="min-h-screen bg-slate-50 flex flex-col">
    {{-- Header Bar --}}
    <header class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo & Portal Title --}}
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('images/bps-logo.png') }}"
                        alt="BPS Logo"
                        class="w-9 h-9 object-contain"
                        onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'font-black text-blue-900 text-lg\'>BPS</span>';"
                    >
                    <div>
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">Library Patron Portal</h1>
                        <p class="text-[11px] text-slate-500 hidden sm:block">Bicutan Parochial School, Inc.</p>
                    </div>
                </div>

                {{-- User Info & Logout Button --}}
                <div class="flex items-center gap-4">
                    @if($patron)
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-semibold text-slate-800 capitalize">
                                {{ trim(implode(' ', array_filter([$patron->first_name, $patron->last_name]))) }}
                            </div>
                            <div class="text-[11px] text-slate-500">
                                {{ $patron->school_id ?? "-" }} | {{ $patron->patronType->name ?? "Borrower" }}
                            </div>
                        </div>
                    @endif

                    <button
                        wire:click="logout"
                        wire:confirm="Are you sure you want to log out?"
                        class="px-3 py-1.5 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition border border-rose-200/60 flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Logout</span>
                    </button>
                </div>

            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Navigation Tabs --}}
        <div class="flex border-b border-slate-200 gap-6 text-sm font-semibold">
            <button
                wire:click="setTab('opac')"
                class="pb-3 border-b-2 transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'opac' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>OPAC (Open Public Accession Catalog)</span>
            </button>

            <button
                wire:click="setTab('transactions')"
                class="pb-3 border-b-2 transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'transactions' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span>My Transactions & Loans</span>
            </button>
        </div>

        {{-- TAB 1: OPAC SEARCH --}}
        @if($activeTab === 'opac')
            <div class="space-y-6">
                {{-- Search Input --}}
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="opacSearch"
                            placeholder="Search by title, ISBN, or author name..."
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-slate-800"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Catalog Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @forelse($catalogItems as $item)
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                            <div class="space-y-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600 inline-block">
                                    {{ $item->assetType?->name ?? 'Book' }}
                                </span>
                                <h3 class="text-sm font-bold text-slate-900 line-clamp-2">
                                    {{ ucwords(strtolower($item->title)) }}
                                </h3>
                                <p class="text-xs text-slate-500 font-semibold">
                                    By {{ ucwords(strtolower($item->author?->name)) ?? 'Unknown Author' }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $item->isbn_issn ?? 'No ISBN' }}
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Availability:</span>
                                @if($item->available_copies > 0)
                                    <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-[11px]">
                                        {{ $item->available_copies }} / {{ $item->total_copies }} Available
                                    </span>
                                @else
                                    <span class="font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded text-[11px]">
                                        Out of Stock
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-slate-200/80 text-slate-400">
                            <p class="text-sm italic">No catalog items matching your search criteria.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($catalogItems->hasPages())
                    <div class="pt-2">
                        {{ $catalogItems->links() }}
                    </div>
                @endif
            </div>
        @endif

        {{-- TAB 2: TRANSACTIONS & LOANS --}}
        @if($activeTab === 'transactions')
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden space-y-4">

                {{-- Filter Buttons --}}
                <div class="p-4 border-b border-slate-200 flex items-center gap-2">
                    <button
                        wire:click="$set('transactionFilter', 'active')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition cursor-pointer {{ $transactionFilter === 'active' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Active Loans
                    </button>
                    <button
                        wire:click="$set('transactionFilter', 'history')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition cursor-pointer {{ $transactionFilter === 'history' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Borrowing History
                    </button>
                </div>

                {{-- Loans Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-bold text-[10px] tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 whitespace-nowrap">Accession #</th>
                                <th class="px-6 py-3.5 whitespace-nowrap">Title</th>
                                <th class="px-6 py-3.5 whitespace-nowrap">Author</th>
                                <th class="px-6 py-3.5 whitespace-nowrap">Borrowed Date</th>
                                <th class="px-6 py-3.5 whitespace-nowrap">Due Date</th>
                                <th class="px-6 py-3.5 whitespace-nowrap">Status</th>
                                @if($transactionFilter === 'history')
                                    <th class="px-6 py-3.5 whitespace-nowrap">Returned Date</th>
                                    <th class="px-6 py-3.5 whitespace-nowrap">Receipt #</th>
                                    <th class="px-6 py-3.5 whitespace-nowrap">Fine Amount</th>
                                    <th class="px-6 py-3.5 whitespace-nowrap">Condition</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($myLoans as $loan)
                                @php
                                    $isOverdue = !$loan->returned_at && $loan->due_at && $loan->due_at->isPast();
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    {{-- Common Columns (6 Total for Active) --}}
                                    <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                        {{ $loan->accession?->accession_number ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-800 whitespace-nowrap">
                                        {{ ucwords(strtolower($loan->accession?->catalog?->title ?? 'N/A')) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ ucwords($loan->accession?->catalog?->author?->name ?? 'N/A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $loan->borrowed_at ? $loan->borrowed_at->format('M d, Y h:i A') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap {{ $isOverdue ? 'font-bold text-rose-600' : '' }}">
                                        {{ $loan->due_at ? $loan->due_at->format('M d, Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($loan->returned_at)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                                Returned
                                            </span>
                                        @elseif($isOverdue)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200/60 animate-pulse">
                                                Overdue
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                                Borrowed
                                            </span>
                                        @endif
                                    </td>

                                    {{-- History Columns (4 Additional for History = 10 Total) --}}
                                    @if($transactionFilter === 'history')
                                        <td class="px-6 py-4 whitespace-nowrap text-emerald-600 font-medium">
                                            {{ $loan->returned_at ? $loan->returned_at->format('M d, Y h:i A') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-800">
                                            {{ $loan->receipt_number ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-800">
                                            &#8369;{{ number_format((float) $loan->fine_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap capitalize">
                                            {{ $loan->condition ?? $loan->accession?->condition ?? 'N/A' }}
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $transactionFilter === 'history' ? '10' : '6' }}" class="px-6 py-12 text-center text-slate-400">
                                        <p class="text-sm italic">No {{ $transactionFilter }} transactions found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                @if($myLoans->hasPages())
                    <div class="p-4 border-t border-slate-200 bg-slate-50">
                        {{ $myLoans->links() }}
                    </div>
                @endif

            </div>
        @endif

    </main>
</div>
