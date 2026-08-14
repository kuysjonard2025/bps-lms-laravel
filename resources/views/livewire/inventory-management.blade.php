<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Inventory Stock Summary</h1>
            <p class="text-xs text-slate-500 mt-1">Title-level stock counts, active loans, reserved, and condition issues ready for auditing and export.</p>
        </div>

        {{-- Export Actions Placeholder for future implementation --}}
        <div class="flex items-center gap-2">
            <button class="px-3 py-2 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition flex items-center gap-1.5">
                <span>📊</span> Export Excel
            </button>
            <button class="px-3 py-2 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded-xl hover:bg-rose-100 transition flex items-center gap-1.5">
                <span>📄</span> Export PDF
            </button>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Physical Copies</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($stats['total_items']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-bold flex items-center justify-center">📦</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Available Copies</span>
                <span class="text-xl font-bold text-emerald-600">{{ number_format($stats['total_available']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center">✅</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Currently On Loan</span>
                <span class="text-xl font-bold text-indigo-600">{{ number_format($stats['total_on_loan']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center">📖</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Missing / Damaged</span>
                <span class="text-xl font-bold text-rose-600">{{ number_format($stats['total_issues']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center">⚠️</div>
        </div>
    </div>

    {{-- Main Inventory Panel --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        {{-- Filters Bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto flex-1">
                {{-- Search --}}
                <div class="relative flex-1 min-w-[240px]">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search Title, Author, ISBN, Call Number..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none"
                    >
                    <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
                </div>

                {{-- Asset Type Filter --}}
                <select wire:model.live="assetTypeFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none">
                    <option value="all">All Asset Types</option>
                    @foreach ($assetTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Inventory Summary Table --}}
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="p-3 pl-4 whitespace-nowrap">Title & Author</th>
                        <th class="p-3 whitespace-nowrap">Asset Type</th>
                        <th class="p-3 text-center whitespace-nowrap">Total</th>
                        <th class="p-3 text-center whitespace-nowrap">Available</th>
                        <th class="p-3 text-center whitespace-nowrap">On Loan</th>
                        <th class="p-3 text-center whitespace-nowrap">Reserved</th>
                        <th class="p-3 text-center whitespace-nowrap">Maintenance</th>
                        <th class="p-3 text-center whitespace-nowrap">Damaged/Lost</th>
                        <th class="p-3 pr-4 text-center whitespace-nowrap">Acquisitions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($catalogs as $catalog)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-3 pl-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $catalog->title }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                    <span>By <strong>{{ $catalog->author->name ?? 'Unknown Author' }}</strong></span>
                                    @if($catalog->isbn_issn)
                                        <span>•</span>
                                        <span class="font-mono">ISBN: {{ $catalog->isbn_issn }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="p-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $catalog->assetType->name ?? 'Standard' }}
                                </span>
                            </td>

                            <td class="p-3 text-center font-bold text-slate-900 whitespace-nowrap">
                                {{ number_format($catalog->total_copies) }}
                            </td>

                            <td class="p-3 text-center whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $catalog->available_copies }}
                                </span>
                            </td>

                            <td class="p-3 text-center whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $catalog->on_loan_copies }}
                                </span>
                            </td>

                            <td class="p-3 text-center whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                    {{ $catalog->reserved_copies }}
                                </span>
                            </td>

                            <td class="p-3 text-center whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    {{ $catalog->maintenance_copies }}
                                </span>
                            </td>

                            <td class="p-3 text-center whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-bold {{ $catalog->damaged_missing_copies > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-50 text-slate-400' }}">
                                    {{ $catalog->damaged_missing_copies }}
                                </span>
                            </td>

                            <td class="p-3 pr-4 text-center font-mono text-slate-600 whitespace-nowrap">
                                {{ $catalog->acquisition_batches }} batch(es)
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-400">
                                No inventory items match your search criteria.
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
