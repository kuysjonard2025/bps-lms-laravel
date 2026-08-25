<div class="space-y-6">

    {{-- Header & Quick Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Accessions Management</h1>
            <p class="text-xs text-slate-500 mt-1">Manage individual copies, batch generation, and circulation status.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            {{-- Status Filter --}}
            <select
                wire:model.live="statusFilter"
                class="px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer text-slate-700"
            >
                <option value="">All Statuses</option>
                <option value="Available">Available</option>
                <option value="On Loan">On Loan</option>
                <option value="Reserved">Reserved</option>
                <option value="Under Maintenance">Under Maintenance</option>
                <option value="Lost">Lost</option>
                <option value="Withdrawn">Withdrawn</option>
            </select>

            {{-- Search Bar --}}
            <div class="relative w-full sm:w-60 lg:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    id="accession-search"
                    placeholder="Search Acc #, Batch #, Call #, Title..."
                    class="w-full pl-9 pr-9 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-slate-900 placeholder:text-slate-400"
                >
                {{-- Search Icon --}}
                <svg wire:loading.remove wire:target="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{-- Loading Spinner --}}
                <svg wire:loading wire:target="search" class="animate-spin w-4 h-4 text-blue-600 absolute left-3 top-2.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            {{-- Export Dropdown --}}
            <div x-data="{ open: false }" class="relative w-full sm:w-auto">
                <button
                    @click="open = !open"
                    @click.away="open = false"
                    type="button"
                    class="w-full sm:w-auto px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 active:bg-slate-100 rounded-xl transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0"
                >
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-30"
                    style="display: none;"
                >
                    <button
                        wire:click="exportExcel"
                        @click="open = false"
                        type="button"
                        class="w-full px-4 py-2 text-left text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Export Excel</span>
                    </button>
                    <button
                        wire:click="exportPdf"
                        @click="open = false"
                        type="button"
                        class="w-full px-4 py-2 text-left text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-rose-600 flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span>Export PDF</span>
                    </button>
                </div>
            </div>

            <button
                wire:click="openCreateModal"
                type="button"
                class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-xl transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 touch-manipulation whitespace-nowrap"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Batch Process Accessions</span>
            </button>
        </div>
    </div>

    {{-- Data Table Container --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs text-slate-600">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                        <th scope="col" class="p-3 pl-4 whitespace-nowrap">Accession #</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Batch #</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Catalog Item & ACQ #</th>
                        <th scope="col" class="hidden md:table-cell p-3 text-center whitespace-nowrap">Call Number</th>
                        <th scope="col" class="hidden sm:table-cell p-3 text-center whitespace-nowrap">Condition</th>
                        <th scope="col" class="p-3 text-center whitespace-nowrap">Status</th>
                        <th scope="col" class="p-3 pr-4 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accessions as $item)
                        <tr wire:key="accession-row-{{ $item->id }}" class="hover:bg-slate-50/60 transition-colors">
                            <td class="p-3 pl-4 font-mono font-bold text-blue-600 whitespace-nowrap">
                                {{ ucwords($item->accession_number) }}
                            </td>
                            <td class="p-3 font-mono text-slate-600 whitespace-nowrap">
                                {{ ucwords($item->batch_number) }}
                            </td>
                            <td class="p-3 max-w-[220px] sm:max-w-none">
                                <div class="font-bold text-slate-900 truncate">{{ ucwords($item->catalog->title) ?? '—' }}</div>
                                <div class="text-[11px] text-slate-500 truncate mt-0.5">
                                    <span class="font-mono text-blue-600 font-semibold">{{ ucwords($item->acquisition->acquisition_number) ?? 'N/A' }}</span>
                                    <span class="hidden sm:inline"> &bull; Author: <span class="text-slate-700">{{ ucwords($item->catalog->author->name) ?? 'N/A' }}</span></span>
                                </div>
                            </td>
                            <td class="hidden md:table-cell p-3 text-center font-mono text-slate-800 whitespace-nowrap">
                                {{ ucwords($item->call_number) }}
                            </td>
                            <td class="hidden sm:table-cell p-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-200/60">
                                    {{ ucwords($item->condition) }}
                                </span>
                            </td>
                            <td class="p-3 text-center whitespace-nowrap">
                                @php
                                    $statusClasses = match($item->status) {
                                        'Available' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                        'On Loan' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                                        'Reserved' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                        'Under Maintenance' => 'bg-purple-50 text-purple-700 border-purple-200/80',
                                        'Lost', 'Withdrawn' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200/80',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full border {{ $statusClasses }}">
                                    {{ ucwords($item->status) }}
                                </span>
                            </td>
                            <td class="p-3 pr-4 text-right whitespace-nowrap space-x-1">
                                @if($item->status === 'On Loan')
                                    <button
                                        type="button"
                                        disabled
                                        title="Item is on loan and cannot be modified"
                                        class="text-slate-300 cursor-not-allowed font-bold px-2 py-1 text-xs"
                                    >Edit</button>
                                    <button
                                        type="button"
                                        disabled
                                        title="Item is on loan and cannot be deleted"
                                        class="text-slate-300 cursor-not-allowed font-bold px-2 py-1 text-xs"
                                    >Delete</button>
                                @else
                                    <button
                                        wire:click="openEditModal({{ $item->id }})"
                                        type="button"
                                        class="text-blue-600 hover:text-blue-800 font-bold px-2 py-1 rounded-lg hover:bg-blue-50 transition cursor-pointer text-xs"
                                    >Edit</button>
                                    <button
                                        wire:click="confirmDelete({{ $item->id }})"
                                        type="button"
                                        class="text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 transition cursor-pointer text-xs"
                                    >Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400">
                                No accession records found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination Links --}}
    @if(method_exists($accessions, 'links'))
        <div class="pt-2">
            {{ $accessions->links() }}
        </div>
    @endif

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div
            x-data
            @keydown.window.escape="$wire.set('showModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl z-10 max-h-[90vh] flex flex-col my-auto overflow-hidden border border-slate-100">
                <div class="bg-slate-50 px-5 sm:px-6 py-4 border-b border-slate-200/80 flex justify-between items-center shrink-0">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $accessionIdBeingEdited ? 'Edit Accession Record' : 'Bulk Batch Accession Insertion' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer p-1">&times;</button>
                </div>

                <form wire:submit="saveAccession" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    {{-- Acquisition Selection --}}
                    <div>
                        <label for="acquisition-source" class="block text-xs font-semibold text-slate-700">Acquisition Source *</label>
                        <select
                            id="acquisition-source"
                            wire:model.live="acquisition_id"
                            @disabled((bool)$accessionIdBeingEdited)
                            class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all bg-white disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed"
                        >
                            <option value="">Select Acquisition Log</option>
                            @foreach($acquisitions as $acq)
                                <option value="{{ $acq->id }}">
                                    {{ $acq->acquisition_number }} &mdash; {{ $acq->catalog->title ?? 'N/A' }} (Txn: {{ $acq->transaction_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('acquisition_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Acquisition Metadata Preview --}}
                    @if ($this->selectedAcquisition)
                        @php
                            $totalQty = $this->selectedAcquisition->quantity;
                            $remainingCount = $this->getRemainingQty();
                            $accessionedCount = max(0, $totalQty - $remainingCount);
                            $cat = $this->selectedAcquisition->catalog;
                        @endphp
                        <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl text-xs space-y-3">
                            <div class="flex justify-between items-start border-b border-slate-200/80 pb-2.5">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Catalog & Asset Details</span>
                                    <h4 class="text-sm font-bold text-slate-900 mt-0.5">{{ $cat->title ?? 'N/A' }}</h4>
                                </div>
                                <span class="text-[10px] font-semibold text-blue-700 bg-blue-50 border border-blue-200/80 px-2.5 py-0.5 rounded-full shrink-0">
                                    {{ $cat->assetType->name ?? 'Standard Asset' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-[11px] text-slate-600">
                                <div>
                                    <span class="block text-slate-400 text-[10px]">Author</span>
                                    <strong class="text-slate-800">{{ $cat->author->name ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span class="block text-slate-400 text-[10px]">Publisher</span>
                                    <strong class="text-slate-800">{{ $cat->publisher->name ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span class="block text-slate-400 text-[10px]">ISBN / ISSN</span>
                                    <strong class="text-slate-800 font-mono">{{ $cat->isbn_issn ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span class="block text-slate-400 text-[10px]">Vendor</span>
                                    <strong class="text-slate-800">{{ $this->selectedAcquisition->vendor->company_name ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span class="block text-slate-400 text-[10px]">Transaction #</span>
                                    <strong class="text-slate-800 font-mono">{{ $this->selectedAcquisition->transaction_number ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span class="block text-slate-400 text-[10px]">Unit Cost</span>
                                    <strong class="text-slate-800">{{ number_format($this->selectedAcquisition->unit_cost ?? 0, 2) }}</strong>
                                </div>
                            </div>

                            {{-- Quantity Summary Metrics --}}
                            <div class="pt-3 border-t border-slate-200/80 grid grid-cols-3 gap-2 text-center">
                                <div class="bg-white p-2 rounded-xl border border-slate-200/80">
                                    <span class="block text-[10px] text-slate-400 uppercase font-bold">Total Acquired</span>
                                    <span class="text-xs sm:text-sm font-bold text-slate-800 font-mono mt-0.5 block">{{ $totalQty }}</span>
                                </div>
                                <div class="bg-emerald-50/60 p-2 rounded-xl border border-emerald-100">
                                    <span class="block text-[10px] text-emerald-700 uppercase font-bold">Accessioned</span>
                                    <span class="text-xs sm:text-sm font-bold text-emerald-800 font-mono mt-0.5 block">{{ $accessionedCount }}</span>
                                </div>
                                <div class="bg-blue-50/60 p-2 rounded-xl border border-blue-100">
                                    <span class="block text-[10px] text-blue-700 uppercase font-bold">Remaining</span>
                                    <span class="text-xs sm:text-sm font-bold text-blue-800 font-mono mt-0.5 block">{{ $remainingCount }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!$accessionIdBeingEdited)
                        @php $remainingQty = $this->getRemainingQty(); @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 p-4 bg-blue-50/40 border border-blue-100/80 rounded-2xl">
                            <div>
                                <label for="batch-qty" class="block text-xs font-bold text-blue-900">Batch Quantity to Create *</label>
                                <input
                                    id="batch-qty"
                                    type="number"
                                    wire:model="batch_qty"
                                    min="{{ $remainingQty > 0 ? 1 : 0 }}"
                                    max="{{ $remainingQty }}"
                                    @disabled($remainingQty === 0)
                                    class="mt-1 w-full text-xs font-bold rounded-xl border border-blue-200 p-2.5 bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                >
                                @if ($remainingQty === 0)
                                    <span class="text-[10px] text-rose-600 font-semibold block mt-1">All copies for this acquisition are fully accessioned.</span>
                                @else
                                    <span class="text-[10px] text-slate-500 block mt-1">Generates sequential barcodes automatically.</span>
                                @endif
                                @error('batch_qty') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="batch-number" class="block text-xs font-semibold text-slate-500">Batch Reference Number (Auto)</label>
                                <input
                                    id="batch-number"
                                    type="text"
                                    wire:model="batch_number"
                                    disabled
                                    readonly
                                    class="mt-1 w-full text-xs font-mono rounded-xl border border-slate-200 p-2.5 bg-slate-100 text-slate-500 cursor-not-allowed select-none"
                                >
                                <span class="text-[10px] text-slate-400 block mt-1">System-generated timestamp identifier.</span>
                                @error('batch_number') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label for="accession-number" class="block text-xs font-semibold text-slate-500">Accession Number (Auto-Generated)</label>
                                <input
                                    id="accession-number"
                                    type="text"
                                    wire:model="accession_number"
                                    disabled
                                    readonly
                                    class="mt-1 w-full text-xs font-mono rounded-xl border border-slate-200 p-2.5 bg-slate-100 text-slate-500 cursor-not-allowed select-none"
                                >
                                <span class="text-[10px] text-slate-400 block mt-1">System identifier cannot be modified.</span>
                                @error('accession_number') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="edit-batch-number" class="block text-xs font-semibold text-slate-500">Batch Reference Number (Auto)</label>
                                <input
                                    id="edit-batch-number"
                                    type="text"
                                    wire:model="batch_number"
                                    disabled
                                    readonly
                                    class="mt-1 w-full text-xs font-mono rounded-xl border border-slate-200 p-2.5 bg-slate-100 text-slate-500 cursor-not-allowed select-none"
                                >
                                @error('batch_number') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    {{-- Call Number --}}
                    <div>
                        <label for="call-number" class="block text-xs font-semibold text-slate-700">Call Number *</label>
                        <input
                            id="call-number"
                            type="text"
                            wire:model="call_number"
                            maxlength="50"
                            placeholder="e.g. 823.912 R59"
                            class="mt-1 w-full text-xs font-mono rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"
                        >
                        @error('call_number') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror

                        @if ($accessionIdBeingEdited)
                            <div class="mt-3 p-3 bg-blue-50/60 border border-blue-100/80 rounded-xl flex items-center gap-2.5">
                                <input
                                    type="checkbox"
                                    id="updateBatchCallNumber"
                                    wire:model="updateBatchCallNumber"
                                    class="rounded border-blue-300 text-blue-600 focus:ring-blue-500/20 h-4 w-4 cursor-pointer"
                                >
                                <label for="updateBatchCallNumber" class="text-xs font-medium text-blue-900 cursor-pointer select-none">
                                    Apply this Call Number to all items in batch (<span class="font-mono font-bold text-blue-700">{{ $batch_number }}</span>)
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label for="accession-condition" class="block text-xs font-semibold text-slate-700">Condition *</label>
                            <select
                                id="accession-condition"
                                wire:model="condition"
                                class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all bg-white"
                            >
                                <option value="New">New</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                            @error('condition') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="accession-status" class="block text-xs font-semibold text-slate-700">Circulation Status *</label>
                            <select
                                id="accession-status"
                                wire:model="status"
                                class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all bg-white"
                            >
                                <option value="Available">Available</option>
                                <option value="On Loan" disabled class="bg-slate-100 text-slate-400">On Loan (Auto-set via Circulation)</option>
                                <option value="Reserved">Reserved</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                                <option value="Lost">Lost</option>
                                <option value="Withdrawn">Withdrawn</option>
                            </select>
                            @error('status') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="acquired-date" class="block text-xs font-semibold text-slate-700">Acquired Date *</label>
                            <input
                                id="acquired-date"
                                type="date"
                                wire:model="acquired_date"
                                class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"
                            >
                            @error('acquired_date') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-4 border-t border-slate-200/80 flex justify-end gap-2">
                        <button
                            wire:click="$set('showModal', false)"
                            type="button"
                            class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition cursor-pointer flex items-center gap-2"
                        >
                            <span wire:loading.remove wire:target="saveAccession">
                                {{ $accessionIdBeingEdited ? 'Update Accession' : 'Generate Accession Copies' }}
                            </span>
                            <span wire:loading wire:target="saveAccession">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Confirmation Modal for Deletion --}}
    @if ($showDeleteModal)
        <div
            x-data
            @keydown.window.escape="$wire.set('showDeleteModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md z-10 p-6 border border-slate-100">
                <div class="flex items-center gap-3 text-rose-600 mb-3">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-base font-bold text-slate-900">Confirm Deletion</h3>
                </div>

                <p class="text-xs text-slate-600">
                    Are you sure you want to delete this accession record? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        wire:click="$set('showDeleteModal', false)"
                        type="button"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteAccession"
                        type="button"
                        class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition cursor-pointer"
                    >
                        Delete Record
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
