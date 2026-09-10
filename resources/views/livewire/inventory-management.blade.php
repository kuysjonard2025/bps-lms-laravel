<div class="p-6 space-y-6">
    {{-- Header Layout Aligned with Acquisitions View --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Inventory Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage title-level inventory stock counts, active loans, reserved items, and condition reports.</p>
        </div>

        <div class="flex items-center gap-2">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="px-3 py-2 text-xs font-semibold bg-white text-slate-700 border border-slate-200 rounded-xl hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export
                    <span class="text-[10px]" x-text="open ? '▲' : '▼'"></span>
                </button>
                <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50 text-xs">
                    <button wire:click="exportExcel" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-emerald-700 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Export Excel
                    </button>
                    <button wire:click="exportPdf" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-rose-700 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
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
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Damaged</span>
                <span class="text-xl font-bold text-rose-600">{{ number_format($stats['total_damaged']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center">⚠️</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Lost</span>
                <span class="text-xl font-bold text-rose-600">{{ number_format($stats['total_lost']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center">⚠️</div>
        </div>
    </div>

    {{-- Main Panel with Search and Filters --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative flex-1 w-full sm:max-w-md">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search Title, Author, ISBN, Call Number..."
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none"
                >
                <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
            </div>

            <select wire:model.live="assetTypeFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none w-full sm:w-auto capitalize">
                <option value="all">All Asset Types</option>
                @foreach ($assetTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="p-3 pl-4 whitespace-nowrap">Title</th>
                        <th class="p-3 whitespace-nowrap">Author</th>
                        <th class="p-3 whitespace-nowrap">Asset Type</th>
                        <th class="p-3 text-center whitespace-nowrap">Total</th>
                        <th class="p-3 text-center whitespace-nowrap">Available</th>
                        <th class="p-3 text-center whitespace-nowrap">On Loan</th>
                        <th class="p-3 text-center whitespace-nowrap">Reserved</th>
                        <th class="p-3 text-center whitespace-nowrap">Maintenance</th>
                        <th class="p-3 text-center whitespace-nowrap">Damaged</th>
                        <th class="p-3 text-center whitespace-nowrap">Lost</th>
                        <th class="p-3 pr-4 text-center whitespace-nowrap">Batches</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($catalogs as $catalog)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-3 pl-4 whitespace-nowrap font-bold text-slate-900 text-xs capitalize">
                                {{ $catalog->title }}
                                @if($catalog->isbn_issn)
                                    <div class="text-[10px] text-slate-400 font-mono font-normal">ISBN: {{ $catalog->isbn_issn }}</div>
                                @endif
                            </td>
                            <td class="p-3 whitespace-nowrap text-slate-700 capitalize">{{ $catalog->author->name ?? 'Unknown' }}</td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 capitalize">
                                    {{ $catalog->assetType->name ?? 'Standard' }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap text-center font-bold text-slate-900">{{ $catalog->total_copies }}</td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-700">{{ $catalog->available_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold bg-indigo-50 text-indigo-700">{{ $catalog->on_loan_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold bg-purple-50 text-purple-700">{{ $catalog->reserved_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold bg-amber-50 text-amber-700">{{ $catalog->maintenance_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold {{ $catalog->damaged_copies > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-50 text-slate-400' }}">{{ $catalog->damaged_copies }}</span></td>
                            <td class="p-3 whitespace-nowrap text-center"><span class="px-2 py-0.5 rounded-full font-bold {{ $catalog->lost_copies > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-50 text-slate-400' }}">{{ $catalog->lost_copies }}</span></td>
                            <td class="p-3 pr-4 whitespace-nowrap text-center font-mono text-slate-600">{{ $catalog->acquisition_batches }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-8 text-slate-400">No inventory entries found.</td>
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
