<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Inventory Management</h1>
            <p class="text-xs text-slate-500 mt-1">Track physical accession copies, book conditions, and shelf stock status.</p>
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
                <div class="relative flex-1 min-w-[200px]">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search Accession #, Batch #, Call #, Title, Author..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none"
                    >
                    <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
                </div>

                {{-- Status Filter --}}
                <select wire:model.live="statusFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none">
                    <option value="all">All Statuses</option>
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

        {{-- Accession Table --}}
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="p-3 pl-4 whitespace-nowrap">Accession / Batch</th>
                        <th class="p-3 whitespace-nowrap">Book Title & Call No.</th>
                        <th class="p-3 whitespace-nowrap">Asset Type</th>
                        <th class="p-3 whitespace-nowrap">Condition</th>
                        <th class="p-3 whitespace-nowrap">Status</th>
                        <th class="p-3 whitespace-nowrap">Acquired Date</th>
                        <th class="p-3 pr-4 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($accessions as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-3 pl-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-800">{{ $item->accession_number }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">Batch: {{ $item->batch_number }}</div>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $item->catalog->title ?? 'Untitled' }}</div>
                                <div class="text-[10px] text-slate-500 flex items-center gap-2">
                                    <span class="font-mono font-semibold">Call #: {{ $item->call_number ?? 'N/A' }}</span>
                                    <span>•</span>
                                    <span>By {{ $item->catalog->author->name ?? 'Unknown Author' }}</span>
                                </div>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $item->catalog->assetType->name ?? 'Standard' }}
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                @switch($item->condition)
                                    @case('New')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">NEW</span>
                                        @break
                                    @case('Good')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">GOOD</span>
                                        @break
                                    @case('Fair')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">FAIR</span>
                                        @break
                                    @case('Damaged')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">DAMAGED</span>
                                        @break
                                    @case('Missing')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-800">MISSING</span>
                                        @break
                                    @default
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">{{ $item->condition }}</span>
                                @endswitch
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                @switch($item->status)
                                    @case('Available')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">AVAILABLE</span>
                                        @break
                                    @case('On Loan')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">ON LOAN</span>
                                        @break
                                    @case('Reserved')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">RESERVED</span>
                                        @break
                                    @case('Under Maintenance')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">MAINTENANCE</span>
                                        @break
                                    @case('Lost')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">LOST</span>
                                        @break
                                    @case('Withdrawn')
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">WITHDRAWN</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="p-3 text-slate-500 whitespace-nowrap">
                                {{ $item->acquired_date ? $item->acquired_date->format('M d, Y') : '—' }}
                            </td>
                            <td class="p-3 pr-4 text-right whitespace-nowrap space-x-1">
                                {{-- Condition Select --}}
                                <select
                                    wire:change="updateItemCondition({{ $item->id }}, $event.target.value)"
                                    class="text-[11px] font-medium py-1 px-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none cursor-pointer"
                                >
                                    <option value="" disabled selected>Condition</option>
                                    <option value="New" {{ $item->condition === 'New' ? 'disabled' : '' }}>New</option>
                                    <option value="Good" {{ $item->condition === 'Good' ? 'disabled' : '' }}>Good</option>
                                    <option value="Fair" {{ $item->condition === 'Fair' ? 'disabled' : '' }}>Fair</option>
                                    <option value="Damaged" {{ $item->condition === 'Damaged' ? 'disabled' : '' }}>Damaged</option>
                                    <option value="Missing" {{ $item->condition === 'Missing' ? 'disabled' : '' }}>Missing</option>
                                </select>

                                {{-- Status Select --}}
                                <select
                                    wire:change="updateItemStatus({{ $item->id }}, $event.target.value)"
                                    class="text-[11px] font-medium py-1 px-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none cursor-pointer"
                                >
                                    <option value="" disabled selected>Status</option>
                                    <option value="Available" {{ $item->status === 'Available' ? 'disabled' : '' }}>Available</option>
                                    <option value="Under Maintenance" {{ $item->status === 'Under Maintenance' ? 'disabled' : '' }}>Maintenance</option>
                                    <option value="Lost" {{ $item->status === 'Lost' ? 'disabled' : '' }}>Lost</option>
                                    <option value="Withdrawn" {{ $item->status === 'Withdrawn' ? 'disabled' : '' }}>Withdrawn</option>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">
                                No inventory accessions match your search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $accessions->links() }}
        </div>
    </div>
</div>
