<div class="p-4 sm:p-6 space-y-4 max-w-full">
    {{-- Header wrapped in white card container --}}
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Accessions Management</h2>
            <p class="text-xs text-gray-500">Manage individual copies, batch generation, and circulation status.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
            {{-- Status Filter --}}
            <select wire:model.live="statusFilter" class="px-3 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
                    placeholder="Search Acc #, Batch #, Call #, Title..."
                    class="w-full pl-9 pr-4 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <button
                wire:click="openCreateModal"
                type="button"
                class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 whitespace-nowrap"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Batch Process Accessions</span>
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
        <table class="w-full text-left text-xs text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-3 sm:px-4 py-3">Accession #</th>
                    <th scope="col" class="px-3 sm:px-4 py-3">Batch #</th>
                    <th scope="col" class="px-3 sm:px-4 py-3">Catalog Item & ACQ #</th>
                    <th scope="col" class="hidden md:table-cell px-4 py-3">Call Number</th>
                    <th scope="col" class="hidden sm:table-cell px-4 py-3">Condition</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 text-center">Status</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($accessions as $item)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                            <div class="font-mono font-bold text-blue-600">{{ $item->accession_number }}</div>
                        </td>
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                            <div class="font-mono text-xs text-gray-600">{{ $item->batch_number }}</div>
                        </td>
                        <td class="px-3 sm:px-4 py-3 max-w-[200px] sm:max-w-none truncate sm:whitespace-normal">
                            <div class="font-semibold text-gray-900 truncate">{{ $item->catalog->title ?? '—' }}</div>
                            <div class="text-[10px] sm:text-[11px] text-gray-500 truncate">
                                <span class="font-mono text-blue-600 font-semibold">{{ $item->acquisition->acquisition_number ?? 'N/A' }}</span>
                                <span class="hidden sm:inline"> | Author: <span class="text-gray-700">{{ $item->catalog->author->name ?? 'N/A' }}</span></span>
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-4 py-3 whitespace-nowrap">
                            <div class="font-mono text-gray-800">{{ $item->call_number }}</div>
                        </td>
                        <td class="hidden sm:table-cell px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-gray-100 text-gray-700">
                                {{ $item->condition }}
                            </span>
                        </td>
                        <td class="px-3 sm:px-4 py-3 text-center whitespace-nowrap">
                            @php
                                $statusClasses = match($item->status) {
                                    'Available' => 'bg-green-100 text-green-800',
                                    'On Loan' => 'bg-blue-100 text-blue-800',
                                    'Reserved' => 'bg-amber-100 text-amber-800',
                                    'Under Maintenance' => 'bg-purple-100 text-purple-800',
                                    'Lost', 'Withdrawn' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $statusClasses }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="px-3 sm:px-4 py-3 text-right whitespace-nowrap">
                            @if($item->status === 'On Loan')
                                <button
                                    type="button"
                                    disabled
                                    title="Item is on loan and cannot be modified"
                                    class="text-gray-300 cursor-not-allowed font-semibold px-1.5 py-1"
                                >Edit</button>
                                <button
                                    type="button"
                                    disabled
                                    title="Item is on loan and cannot be deleted"
                                    class="text-gray-300 cursor-not-allowed font-semibold px-1.5 py-1"
                                >Delete</button>
                            @else
                                <button
                                    wire:click="openEditModal({{ $item->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-semibold px-1.5 py-1 rounded hover:bg-blue-50 transition cursor-pointer"
                                >Edit</button>
                                <button
                                    wire:click="confirmDelete({{ $item->id }})"
                                    class="text-red-600 hover:text-red-800 font-semibold px-1.5 py-1 rounded hover:bg-red-50 transition cursor-pointer"
                                >Delete</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No accession records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $accessions->links() }}</div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden my-auto max-h-[90vh] flex flex-col">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900">{{ $accessionIdBeingEdited ? 'Edit Accession Record' : 'Bulk Batch Accession Insertion' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveAccession" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    {{-- Acquisition Selection --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Acquisition Source *</label>
                        <select
                            wire:model.live="acquisition_id"
                            @disabled((bool)$accessionIdBeingEdited)
                            class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                        >
                            <option value="">Select Acquisition Log</option>
                            @foreach($acquisitions as $acq)
                                <option value="{{ $acq->id }}">
                                    {{ $acq->acquisition_number }} — {{ $acq->catalog->title ?? 'N/A' }} (Txn: {{ $acq->transaction_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('acquisition_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Acquisition Metadata & Quantity Summary Preview --}}
                    @if ($this->selectedAcquisition)
                        @php
                            $totalQty = $this->selectedAcquisition->quantity;
                            $remainingCount = $this->getRemainingQty();
                            $accessionedCount = max(0, $totalQty - $remainingCount);
                            $cat = $this->selectedAcquisition->catalog;
                        @endphp
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-3">
                            <div class="flex justify-between items-start border-b border-slate-200 pb-2">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Catalog & Asset Details</span>
                                    <h4 class="text-sm font-bold text-slate-800">{{ $cat->title ?? 'N/A' }}</h4>
                                </div>
                                <span class="text-[10px] font-semibold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">
                                    {{ $cat->assetType->name ?? 'Standard Asset' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-[11px] text-slate-600">
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
                            <div class="pt-2.5 border-t border-slate-200 grid grid-cols-3 gap-2 text-center">
                                <div class="bg-white p-2 rounded-lg border border-slate-200">
                                    <span class="block text-[10px] text-slate-400 uppercase font-bold">Total Acquired</span>
                                    <span class="text-xs sm:text-sm font-bold text-slate-800 font-mono">{{ $totalQty }}</span>
                                </div>
                                <div class="bg-emerald-50/60 p-2 rounded-lg border border-emerald-100">
                                    <span class="block text-[10px] text-emerald-600 uppercase font-bold">Accessioned</span>
                                    <span class="text-xs sm:text-sm font-bold text-emerald-700 font-mono">{{ $accessionedCount }}</span>
                                </div>
                                <div class="bg-blue-50/60 p-2 rounded-lg border border-blue-100">
                                    <span class="block text-[10px] text-blue-600 uppercase font-bold">Remaining</span>
                                    <span class="text-xs sm:text-sm font-bold text-blue-700 font-mono">{{ $remainingCount }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (!$accessionIdBeingEdited)
                        @php $remainingQty = $this->getRemainingQty(); @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 p-3 bg-blue-50/40 border border-blue-100 rounded-lg">
                            <div>
                                <label class="block text-xs font-bold text-blue-900">Batch Quantity to Create *</label>
                                <input
                                    type="number"
                                    wire:model="batch_qty"
                                    min="{{ $remainingQty > 0 ? 1 : 0 }}"
                                    max="{{ $remainingQty }}"
                                    @disabled($remainingQty === 0)
                                    class="mt-1 w-full text-xs sm:text-sm font-bold rounded-md border-blue-300 border p-2 shadow-xs bg-white disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
                                >
                                @if ($remainingQty === 0)
                                    <span class="text-[10px] text-rose-600 font-semibold block mt-1">All copies for this acquisition are fully accessioned.</span>
                                @else
                                    <span class="text-[10px] text-gray-500">Generates sequential barcodes automatically.</span>
                                @endif
                                @error('batch_qty') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500">Batch Reference Number (Auto)</label>
                                <input type="text" wire:model="batch_number" disabled readonly class="mt-1 w-full text-xs sm:text-sm font-mono rounded-md border-gray-200 border p-2 bg-gray-100 text-gray-500 cursor-not-allowed select-none">
                                <span class="text-[10px] text-gray-400">System-generated timestamp identifier.</span>
                                @error('batch_number') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500">Accession Number (Auto-Generated)</label>
                                <input
                                    type="text"
                                    wire:model="accession_number"
                                    disabled
                                    readonly
                                    class="mt-1 w-full text-xs sm:text-sm font-mono rounded-md border-gray-200 border p-2 bg-gray-100 text-gray-500 cursor-not-allowed select-none"
                                >
                                <span class="text-[10px] text-gray-400">System identifier cannot be modified.</span>
                                @error('accession_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500">Batch Reference Number (Auto)</label>
                                <input type="text" wire:model="batch_number" disabled readonly class="mt-1 w-full text-xs sm:text-sm font-mono rounded-md border-gray-200 border p-2 bg-gray-100 text-gray-500 cursor-not-allowed select-none">
                                @error('batch_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    {{-- Call Number with Batch Update Toggle --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Call Number *</label>
                        <input type="text" wire:model="call_number" maxlength="50" placeholder="e.g. 823.912 R59" class="mt-1 w-full text-xs sm:text-sm font-mono rounded-md border-gray-300 border p-2 shadow-xs">
                        @error('call_number') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror

                        @if ($accessionIdBeingEdited)
                            <div class="mt-2.5 p-2 bg-blue-50/60 border border-blue-100 rounded-md flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="updateBatchCallNumber"
                                    wire:model="updateBatchCallNumber"
                                    class="rounded border-blue-300 text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer"
                                >
                                <label for="updateBatchCallNumber" class="text-xs font-medium text-blue-900 cursor-pointer select-none">
                                    Apply this Call Number to all items in batch (<span class="font-mono font-bold text-blue-700">{{ $batch_number }}</span>)
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Condition *</label>
                            <select wire:model="condition" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="New">New</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                            @error('condition') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Circulation Status *</label>
                            <select wire:model="status" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="Available">Available</option>
                                <option value="On Loan" disabled class="bg-gray-100 text-gray-400">On Loan (Auto-set via Circulation)</option>
                                <option value="Reserved">Reserved</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                                <option value="Lost">Lost</option>
                                <option value="Withdrawn">Withdrawn</option>
                            </select>
                            @error('status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Acquired Date *</label>
                            <input type="date" wire:model="acquired_date" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('acquired_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">Remarks</label>
                        <textarea wire:model="remarks" rows="2" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-xs"></textarea>
                        @error('remarks') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-4 flex justify-end gap-2.5 pt-2 border-t border-gray-100">
                        <button wire:click="$set('showModal', false)" type="button" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                        <button
                            type="submit"
                            @disabled(!$accessionIdBeingEdited && $this->getRemainingQty() === 0)
                            class="px-3.5 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition"
                        >
                            {{ $accessionIdBeingEdited ? 'Save Changes' : 'Process Batch' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10 p-6 space-y-4 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Delete Accession Record?</h3>
                    <p class="text-xs text-gray-500 mt-1">Are you sure you want to delete this accession item? This action cannot be undone.</p>
                </div>
                <div class="flex justify-center gap-3 pt-2">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 cursor-pointer">Cancel</button>
                    <button wire:click="deleteAccession" type="button" class="px-4 py-2 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 cursor-pointer shadow-xs">Delete Record</button>
                </div>
            </div>
        </div>
    @endif
</div>
