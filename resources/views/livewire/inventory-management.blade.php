<div class="p-6 space-y-6">
    {{-- Header Layout --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight uppercase">INVENTORY MANAGEMENT</h1>
            <p class="text-xs text-slate-500 mt-0.5 uppercase">MANAGE TITLE-LEVEL INVENTORY STOCK COUNTS, ACCESSIONS STATUS, AND CONDITION METRICS.</p>
        </div>

        <div class="flex items-center gap-2">
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="px-3 py-2 text-xs font-semibold bg-white text-slate-700 border border-slate-200 rounded-xl hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm uppercase cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    EXPORT
                    <span class="text-[10px]" x-text="open ? '▲' : '▼'"></span>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50 text-xs">
                    <button type="button" wire:click="exportExcel" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-emerald-700 font-medium flex items-center gap-2 uppercase cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        EXPORT EXCEL
                    </button>
                    <button type="button" wire:click="exportPdf" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-rose-700 font-medium flex items-center gap-2 uppercase cursor-pointer">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        EXPORT PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">TOTAL ACCESSIONED</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($stats['total_accessioned']) }}</span>
                <span class="text-[10px] text-amber-600 font-semibold uppercase block mt-0.5">UNACCESSIONED: {{ number_format($stats['total_unaccessioned']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-bold flex items-center justify-center">📦</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">AVAILABLE</span>
                <span class="text-xl font-bold text-emerald-600">{{ number_format($stats['total_available']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center">✅</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">ON LOAN</span>
                <span class="text-xl font-bold text-indigo-600">{{ number_format($stats['total_on_loan']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center">📖</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">DAMAGED</span>
                <span class="text-xl font-bold text-rose-600">{{ number_format($stats['total_damaged']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center">⚠️</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">LOST</span>
                <span class="text-xl font-bold text-rose-600">{{ number_format($stats['total_lost']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center">⚠️</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">DUMPED</span>
                <span class="text-xl font-bold text-slate-600">{{ number_format($stats['total_dumped']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 font-bold flex items-center justify-center">🗑️</div>
        </div>
    </div>

    {{-- Main Panel with Search and Filters --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative flex-1 w-full sm:max-w-md">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="SEARCH TITLE, AUTHOR, ISBN, ACCESSION NO..."
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 text-slate-800 uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none"
                >
                <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
            </div>

            {{-- Combined Searchable Input + Select Field for Asset Types --}}
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <input
                    type="text"
                    list="asset-type-options"
                    wire:model.live.debounce.250ms="assetTypeSearch"
                    placeholder="TYPE OR PICK ASSET TYPE..."
                    class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 uppercase outline-none w-full sm:w-48 focus:border-blue-500"
                >
                <datalist id="asset-type-options">
                    <option value="ALL ASSET TYPES"></option>
                    @foreach ($assetTypes as $type)
                        <option value="{{ strtoupper($type->name) }}"></option>
                    @endforeach
                </datalist>

                <select wire:model.live="assetTypeFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 uppercase outline-none w-full sm:w-auto">
                    <option value="all">ALL ASSET TYPES</option>
                    @foreach ($assetTypes as $type)
                        <option value="{{ $type->id }}">{{ strtoupper($type->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="p-3 pl-4 whitespace-nowrap">TITLE</th>
                        <th class="p-3 whitespace-nowrap">AUTHOR</th>
                        <th class="p-3 whitespace-nowrap">ASSET TYPE</th>
                        <th class="p-3 text-center whitespace-nowrap">TOTAL</th>
                        <th class="p-3 text-center whitespace-nowrap">ACCESSIONED</th>
                        <th class="p-3 text-center whitespace-nowrap">UNACCESSIONED</th>
                        <th class="p-3 text-center whitespace-nowrap">AVAILABLE</th>
                        <th class="p-3 text-center whitespace-nowrap">ON LOAN</th>
                        <th class="p-3 text-center whitespace-nowrap">STORED</th>
                        <th class="p-3 text-center whitespace-nowrap">MAINTENANCE</th>
                        <th class="p-3 text-center whitespace-nowrap">DUMPED</th>
                        <th class="p-3 text-center whitespace-nowrap">DAMAGED</th>
                        <th class="p-3 text-center whitespace-nowrap">LOST</th>
                        <th class="p-3 pr-4 text-center whitespace-nowrap">BATCHES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($catalogs as $catalog)
                        @php
                            $accessionedCopies = (int) ($catalog->accessioned_copies ?? 0);
                            $receivedQty = (int) ($catalog->total_received_quantity ?? 0);
                            $totalStock = max($receivedQty, $accessionedCopies);
                            $unaccessionedCopies = max(0, $totalStock - $accessionedCopies);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-3 pl-4 whitespace-nowrap font-bold text-slate-900 text-xs uppercase">
                                {{ strtoupper($catalog->title) }}
                                @if ($catalog->isbn_issn)
                                    <div class="text-[10px] text-slate-400 font-mono font-normal uppercase">ISBN: {{ strtoupper($catalog->isbn_issn) }}</div>
                                @endif
                            </td>
                            <td class="p-3 whitespace-nowrap text-slate-700 uppercase">{{ strtoupper($catalog->author->name ?? 'UNKNOWN') }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 uppercase">
                                    {{ strtoupper($catalog->assetType->name ?? 'STANDARD') }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-center font-bold text-slate-900">{{ $totalStock }}</td>
                            <td class="p-3 whitespace-nowrap text-center text-slate-700 font-semibold">{{ $accessionedCopies }}</td>
                            <td class="p-3 whitespace-nowrap text-center">
                                <span class="px-2 py-0.5 rounded-full font-bold {{ $unaccessionedCopies > 0 ? 'bg-amber-50 text-amber-700' : 'bg-slate-50 text-slate-400' }}">
                                    {{ $unaccessionedCopies }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-700">{{ $catalog->available_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold bg-indigo-50 text-indigo-700">{{ $catalog->on_loan_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold bg-purple-50 text-purple-700">{{ $catalog->stored_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold bg-amber-50 text-amber-700">{{ $catalog->maintenance_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold {{ $catalog->dumped_copies > 0 ? 'bg-slate-200 text-slate-700' : 'bg-slate-50 text-slate-400' }}">{{ $catalog->dumped_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold {{ $catalog->damaged_copies > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-50 text-slate-400' }}">{{ $catalog->damaged_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold {{ $catalog->lost_copies > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-50 text-slate-400' }}">{{ $catalog->lost_copies }}</span></td>
                            <td class="p-3 pr-4 whitespace-nowrap text-center font-mono text-slate-600">{{ $catalog->acquisition_batches }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-8 text-slate-400 uppercase">NO INVENTORY ENTRIES FOUND.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $catalogs->links() }}
        </div>
    </div>
</div>
