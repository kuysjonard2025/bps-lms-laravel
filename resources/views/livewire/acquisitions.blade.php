<div class="p-4 sm:p-6 space-y-4 max-w-full">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Acquisitions Management</h2>
            <p class="text-xs text-gray-500">Log incoming receiving records for cataloged assets from registered vendors.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64 lg:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search ACQ #, Txn #, Catalog, Vendor..."
                    class="w-full pl-9 pr-8 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(!empty($search))
                    <button wire:click="$set('search', '')" type="button" class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Action Button -->
            <button
                wire:click="openCreateModal"
                type="button"
                class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-sm shrink-0 whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Receive Asset</span>
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
        <table class="w-full text-left text-xs text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-3 sm:px-4 py-3">ACQ / Txn</th>
                    <th scope="col" class="px-3 sm:px-4 py-3">Catalog Item Details</th>
                    <th scope="col" class="hidden md:table-cell px-4 py-3">Vendor</th>
                    <th scope="col" class="hidden sm:table-cell px-3 py-3 text-center">Qty</th>
                    <th scope="col" class="hidden sm:table-cell px-4 py-3 text-right">Unit Cost</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 text-right">Total Cost</th>
                    <th scope="col" class="hidden lg:table-cell px-4 py-3">Received Date</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($acquisitions as $acq)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                            <div class="font-mono font-bold text-blue-600">{{ $acq->acquisition_number }}</div>
                            <div class="text-[10px] sm:text-[11px] font-mono text-gray-500">Txn: {{ $acq->transaction_number }}</div>
                        </td>
                        <td class="px-3 sm:px-4 py-3 max-w-[180px] sm:max-w-none truncate sm:whitespace-normal">
                            <div class="font-semibold text-gray-900 truncate">{{ $acq->catalog->title ?? '—' }}</div>
                            <div class="text-[10px] sm:text-[11px] text-gray-500 truncate">
                                Author: <span class="text-gray-700">{{ $acq->catalog->author->name ?? 'N/A' }}</span>
                                <span class="hidden sm:inline"> | Type: <span class="text-gray-700">{{ $acq->catalog->assetType->name ?? 'N/A' }}</span></span>
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-4 py-3 text-gray-600 whitespace-nowrap">{{ $acq->vendor->company_name ?? '—' }}</td>
                        <td class="hidden sm:table-cell px-3 py-3 text-center text-gray-900 font-semibold">{{ $acq->quantity }}</td>
                        <td class="hidden sm:table-cell px-4 py-3 text-right text-gray-600 font-mono">₱{{ number_format($acq->unit_cost, 2) }}</td>
                        <td class="px-3 sm:px-4 py-3 text-right text-gray-900 font-mono font-bold whitespace-nowrap">₱{{ number_format($acq->quantity * $acq->unit_cost, 2) }}</td>
                        <td class="hidden lg:table-cell px-4 py-3 text-gray-500 whitespace-nowrap">{{ $acq->received_date ? $acq->received_date->format('M d, Y') : '—' }}</td>
                        <td class="px-3 sm:px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <button wire:click="openEditModal({{ $acq->id }})" class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded hover:bg-blue-50 transition cursor-pointer">Edit</button>
                            <button wire:click="confirmDelete({{ $acq->id }})" class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded hover:bg-red-50 transition cursor-pointer">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No acquisition records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $acquisitions->links() }}</div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" x-data @keydown.escape.window="$wire.set('showModal', false)">
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-xl z-10 overflow-hidden my-auto max-h-[90vh] flex flex-col border border-gray-100">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900">{{ $acquisitionIdBeingEdited ? 'Edit Acquisition Record' : 'Receive New Asset' }}</h3>
                    <button wire:click="$set('showModal', false)" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveAcquisition" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Acquisition Number</label>
                            <input
                                type="text"
                                wire:model="acquisition_number"
                                disabled
                                readonly
                                class="mt-1 w-full text-xs sm:text-sm font-mono rounded-md border-gray-300 border p-2 bg-gray-100 text-gray-500 cursor-not-allowed"
                            >
                            @error('acquisition_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Transaction / Invoice # *</label>
                            <input
                                type="text"
                                wire:model="transaction_number"
                                placeholder="e.g. PO-98214 / INV-001"
                                class="mt-1 w-full text-xs sm:text-sm font-mono rounded-md border-gray-300 border p-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                            @error('transaction_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Vendor Selection --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Vendor *</label>
                        <select wire:model.live="vendor_id" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->company_name }}</option>
                            @endforeach
                        </select>
                        @error('vendor_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror

                        @if ($this->selectedVendor)
                            <div class="mt-2.5 p-2.5 sm:p-3 bg-blue-50/70 border border-blue-200 rounded-lg text-xs space-y-1 text-gray-700">
                                <div class="font-bold text-blue-900 flex items-center justify-between border-b border-blue-200 pb-1">
                                    <span class="truncate">{{ $this->selectedVendor->company_name }}</span>
                                    @if ($this->selectedVendor->contact_person)
                                        <span class="text-[10px] font-semibold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded shrink-0 ml-2">
                                            {{ $this->selectedVendor->contact_person }}
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-[11px] pt-1">
                                    @if ($this->selectedVendor->email)
                                        <div class="truncate"><strong class="text-gray-900">Email:</strong> {{ $this->selectedVendor->email }}</div>
                                    @endif
                                    @if ($this->selectedVendor->phone)
                                        <div><strong class="text-gray-900">Phone:</strong> {{ $this->selectedVendor->phone }}</div>
                                    @endif
                                    @if ($this->selectedVendor->address)
                                        <div class="col-span-1 sm:col-span-2 truncate"><strong class="text-gray-900">Address:</strong> {{ $this->selectedVendor->address }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Catalog Selection --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Catalog Item *</label>
                        <select wire:model.live="catalog_id" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Select Catalog</option>
                            @foreach($catalogs as $catalog)
                                <option value="{{ $catalog->id }}">{{ $catalog->title }}</option>
                            @endforeach
                        </select>
                        @error('catalog_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if ($this->selectedCatalog)
                        <div class="p-2.5 sm:p-3 bg-blue-50/70 border border-blue-200 rounded-lg text-xs space-y-1.5">
                            <div class="font-bold text-blue-900 border-b border-blue-200 pb-1 flex justify-between items-center">
                                <span>Catalog Reference</span>
                                <span class="text-[10px] font-semibold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded">
                                    ISBN/ISSN: {{ $this->selectedCatalog->isbn_issn ?: 'N/A' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5 text-gray-700 text-[11px] pt-1">
                                <div class="truncate"><strong class="text-gray-900">Author:</strong> {{ $this->selectedCatalog->author->name ?? 'N/A' }}</div>
                                <div class="truncate"><strong class="text-gray-900">Type:</strong> {{ $this->selectedCatalog->assetType->name ?? 'N/A' }}</div>
                                <div class="truncate"><strong class="text-gray-900">Publisher:</strong> {{ $this->selectedCatalog->publisher->name ?? 'N/A' }}</div>
                                <div class="truncate"><strong class="text-gray-900">Edition:</strong> {{ $this->selectedCatalog->edition ?: 'N/A' }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Quantity *</label>
                            <input type="number" wire:model.live="quantity" min="1" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @error('quantity') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Unit Cost *</label>
                            <input type="number" step="0.01" wire:model.live="unit_cost" min="0" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            @error('unit_cost') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Received Date *</label>
                            <input
                                type="date"
                                wire:model="received_date"
                                max="{{ now()->format('Y-m-d') }}"
                                class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                            @error('received_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Live Calculated Total Cost Preview --}}
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-md flex justify-between items-center text-xs">
                        <span class="font-medium text-gray-600">Calculated Total Cost:</span>
                        <span class="text-xs sm:text-sm font-bold font-mono text-gray-900">₱{{ number_format($this->calculatedTotalCost, 2) }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">Remarks</label>
                        <textarea wire:model="remarks" rows="2" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                        @error('remarks') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-4 flex justify-end gap-2.5 pt-2">
                        <button wire:click="$set('showModal', false)" type="button" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-3.5 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 cursor-pointer">
                            <span wire:loading.remove>{{ $acquisitionIdBeingEdited ? 'Save Changes' : 'Record Acquisition' }}</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data @keydown.escape.window="$wire.set('showDeleteModal', false)">
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm z-10 p-5 text-center space-y-4 border border-gray-100">
                <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Delete Acquisition</h3>
                    <p class="text-xs text-gray-500 mt-1">Are you sure you want to delete this receiving record?</p>
                </div>
                <div class="flex justify-center gap-2.5 pt-1">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                    <button wire:click="deleteAcquisition" wire:loading.attr="disabled" type="button" class="px-3.5 py-1.5 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50 cursor-pointer">
                        <span wire:loading.remove>Delete</span>
                        <span wire:loading>Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
