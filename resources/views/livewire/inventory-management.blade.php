<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Inventory Stock View</h1>
            <p class="text-xs text-slate-500 mt-1">Grouped stock management by title, acquisition records, and individual physical copies.</p>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Items</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($stats['total']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-bold flex items-center justify-center">📦</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Available</span>
                <span class="text-xl font-bold text-emerald-600">{{ number_format($stats['available']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center">✅</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">On Loan</span>
                <span class="text-xl font-bold text-indigo-600">{{ number_format($stats['on_loan']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center">📖</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Attention Needed</span>
                <span class="text-xl font-bold text-rose-600">{{ number_format($stats['needs_attention']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center">⚠️</div>
        </div>
    </div>

    {{-- Main Inventory Panel --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        {{-- Filters Bar --}}
        <div class="flex flex-col lg:flex-row items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full">
                {{-- Search --}}
                <div class="relative flex-1 min-w-[220px]">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search Title, Author, ISBN, Acquisition #, Accession #..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none"
                    >
                    <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
                </div>

                {{-- Status Filter --}}
                <select wire:model.live="statusFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none">
                    <option value="all">All Copy Statuses</option>
                    <option value="Available">Available</option>
                    <option value="On Loan">On Loan</option>
                    <option value="Reserved">Reserved</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                    <option value="Lost">Lost</option>
                    <option value="Withdrawn">Withdrawn</option>
                </select>

                {{-- Condition Filter --}}
                <select wire:model.live="conditionFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none">
                    <option value="all">All Conditions</option>
                    <option value="New">New</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Missing">Missing</option>
                </select>

                {{-- Asset Type Filter --}}
                <select wire:model.live="assetTypeFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none">
                    <option value="all">All Asset Types</option>
                    @foreach ($assetTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Grouped Inventory Table --}}
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="p-3 pl-4 w-8"></th>
                        <th class="p-3 whitespace-nowrap">Catalog Title & Metadata</th>
                        <th class="p-3 whitespace-nowrap">Asset Type</th>
                        <th class="p-3 whitespace-nowrap">Stock Breakdown</th>
                        <th class="p-3 pr-4 whitespace-nowrap text-right">Total Copies</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($catalogs as $catalog)
                        <tr x-data="{ open: false }" class="hover:bg-slate-50/60 transition group">
                            <td class="p-3 pl-4 align-top">
                                <button @click="open = !open" class="p-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                                    <span x-show="!open" class="text-xs font-bold block w-4 text-center">+</span>
                                    <span x-show="open" class="text-xs font-bold block w-4 text-center" x-cloak>−</span>
                                </button>
                            </td>

                            <td class="p-3 align-top">
                                <div class="font-bold text-slate-900 text-sm">{{ $catalog->title }}</div>
                                <div class="text-[11px] text-slate-500 flex flex-wrap items-center gap-2 mt-0.5">
                                    <span>By <strong>{{ $catalog->author->name ?? 'Unknown Author' }}</strong></span>
                                    @if($catalog->isbn_issn)
                                        <span>•</span>
                                        <span class="font-mono">ISBN/ISSN: {{ $catalog->isbn_issn }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="p-3 align-top whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $catalog->assetType->name ?? 'Standard' }}
                                </span>
                            </td>

                            {{-- Stock Summary Badges --}}
                            <td class="p-3 align-top">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        {{ $catalog->available_copies }} Avail
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                        {{ $catalog->on_loan_copies }} Loaned
                                    </span>
                                    @if($catalog->maintenance_copies > 0)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                            {{ $catalog->maintenance_copies }} Issue
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="p-3 pr-4 align-top text-right whitespace-nowrap">
                                <span class="font-bold text-slate-900 text-sm">{{ $catalog->total_copies }}</span>
                            </td>
                        </tr>

                        {{-- Expanded Copies Sub-Table --}}
                        <tr x-show="open" x-cloak class="bg-slate-50/70 border-t-0">
                            <td colspan="5" class="p-4 pl-12">
                                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3">
                                    <div class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">
                                        Physical Accession Copies & Acquisition Details
                                    </div>
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 text-[10px] font-bold uppercase text-slate-400 border-b border-slate-100">
                                            <tr>
                                                <th class="p-2">Accession / Batch #</th>
                                                <th class="p-2">Call Number</th>
                                                <th class="p-2">Acquisition #</th>
                                                <th class="p-2">Vendor</th>
                                                <th class="p-2">Condition</th>
                                                <th class="p-2">Status</th>
                                                <th class="p-2">Acquired Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($catalog->accessions as $copy)
                                                <tr class="hover:bg-slate-50/50">
                                                    <td class="p-2 font-mono font-bold text-slate-800">
                                                        {{ $copy->accession_number }}
                                                        <div class="text-[10px] text-slate-400 font-normal">Batch: {{ $copy->batch_number }}</div>
                                                    </td>
                                                    <td class="p-2 font-mono text-slate-600">{{ $copy->call_number ?? 'N/A' }}</td>
                                                    <td class="p-2 font-medium text-slate-700">{{ $copy->acquisition->acquisition_number ?? 'N/A' }}</td>
                                                    <td class="p-2 text-slate-600">{{ $copy->acquisition->vendor->company_name ?? 'N/A' }}</td>
                                                    <td class="p-2">
                                                        @switch($copy->condition)
                                                            @case('New') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">NEW</span> @break
                                                            @case('Good') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-800">GOOD</span> @break
                                                            @case('Fair') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">FAIR</span> @break
                                                            @case('Damaged') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800">DAMAGED</span> @break
                                                            @case('Missing') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-800">MISSING</span> @break
                                                            @default <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600">{{ $copy->condition }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td class="p-2">
                                                        @switch($copy->status)
                                                            @case('Available') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">AVAILABLE</span> @break
                                                            @case('On Loan') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">ON LOAN</span> @break
                                                            @case('Reserved') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-50 text-purple-700 border border-purple-200">RESERVED</span> @break
                                                            @case('Under Maintenance') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">MAINTENANCE</span> @break
                                                            @case('Lost') <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200">LOST</span> @break
                                                            @default <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600">{{ $copy->status }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td class="p-2 text-slate-500">{{ $copy->acquired_date ? $copy->acquired_date->format('M d, Y') : '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="p-3 text-center text-slate-400">No physical copy records match the current filters.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-400">
                                No inventory titles match your search criteria.
                            </td>
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
