<div class="space-y-4">
    {{-- Header Container --}}
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Acquisitions</h2>
            <p class="text-xs text-gray-500">Manage purchase orders, multi-item invoices, pending shipments, and receiving records.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64 lg:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search ACQ #, Txn #, Status, Catalog, Vendor..."
                    class="w-full pl-9 pr-8 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(!empty($search))
                    <button wire:click="$set('search', '')" type="button" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs cursor-pointer" aria-label="Clear search">
                        ✕
                    </button>
                @endif
            </div>

            <!-- Action Button -->
            <button
                wire:click="openCreateModal"
                type="button"
                class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 whitespace-nowrap"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Receive Asset / PO</span>
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
        <table class="w-full text-left text-xs text-gray-700 table-auto">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">ACQ #</th>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">Txn #</th>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">Status</th>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">Delivery Type</th>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">ISBN/ISSN</th>
                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Title</th>
                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Author</th>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">Asset Type</th>
                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Vendor</th>
                    <th scope="col" class="px-2 py-3 text-center whitespace-nowrap">Ordered</th>
                    <th scope="col" class="px-2 py-3 text-center whitespace-nowrap">Received</th>
                    <th scope="col" class="px-3 py-3 text-right whitespace-nowrap">Unit Cost</th>
                    <th scope="col" class="px-3 py-3 text-right whitespace-nowrap">Total Cost</th>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">Expected Delivery</th>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">Received Date</th>
                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($acquisitions as $acq)
                    <tr wire:key="acq-row-{{ $acq->id }}" class="hover:bg-gray-50/50 transition">
                        <td class="px-3 py-3 whitespace-nowrap">
                            <div class="font-mono font-bold text-blue-600 uppercase">{{ $acq->acquisition_number }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <div class="font-mono text-gray-600 uppercase">{{ $acq->transaction_number }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full uppercase inline-block {{ match($acq->status) {
                                'pending' => 'bg-amber-100 text-amber-700',
                                'partial' => 'bg-indigo-100 text-indigo-700',
                                'delivered' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-gray-100 text-gray-700'
                            } }}">
                                {{ $acq->status }}
                            </span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full uppercase inline-block {{ $acq->delivery_type === 'donation' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $acq->delivery_type }}
                            </span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap font-mono text-gray-600">
                            {{ $acq->catalog->isbn_issn ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap capitalize font-medium text-gray-900 max-w-xs truncate" title="{{ $acq->catalog->title ?? '—' }}">
                            {{ $acq->catalog->title ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap capitalize text-gray-600 max-w-xs truncate" title="{{ $acq->catalog->author->name ?? 'N/A' }}">
                            {{ $acq->catalog->author->name ?? 'N/A' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap capitalize text-gray-600">
                            {{ $acq->catalog->assetType->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 capitalize whitespace-nowrap max-w-xs truncate" title="{{ $acq->vendor->company_name ?? '—' }}">
                            {{ $acq->vendor->company_name ?? '—' }}
                        </td>
                        <td class="px-2 py-3 text-center text-gray-900 font-semibold whitespace-nowrap">{{ $acq->quantity_ordered }}</td>
                        <td class="px-2 py-3 text-center text-gray-900 font-semibold whitespace-nowrap">{{ $acq->quantity_received }}</td>
                        <td class="px-3 py-3 text-right text-gray-600 font-mono whitespace-nowrap">₱{{ number_format($acq->unit_cost, 2) }}</td>
                        <td class="px-3 py-3 text-right text-gray-900 font-mono font-bold whitespace-nowrap">₱{{ number_format($acq->total_cost, 2) }}</td>
                        <td class="px-3 py-3 text-gray-500 whitespace-nowrap">{{ $acq->delivery_date ? $acq->delivery_date->format('M d, Y') : '—' }}</td>
                        <td class="px-3 py-3 text-gray-500 whitespace-nowrap">{{ $acq->received_date ? $acq->received_date->format('M d, Y') : '— (Pending)' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <button wire:click="viewAccessions({{ $acq->id }})" type="button" class="text-emerald-600 hover:text-emerald-800 font-semibold px-1.5 py-1 rounded hover:bg-emerald-50 transition cursor-pointer">View</button>
                            <button wire:click="addMoreFromExisting({{ $acq->id }})" type="button" class="text-indigo-600 hover:text-indigo-800 font-semibold px-1.5 py-1 rounded hover:bg-indigo-50 transition cursor-pointer">Add</button>
                            <button wire:click="openEditModal({{ $acq->id }})" type="button" class="text-blue-600 hover:text-blue-800 font-semibold px-1.5 py-1 rounded hover:bg-blue-50 transition cursor-pointer">Edit</button>
                            <button wire:click="confirmDelete({{ $acq->id }})" type="button" class="text-red-600 hover:text-red-800 font-semibold px-1.5 py-1 rounded hover:bg-red-50 transition cursor-pointer">Del</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="px-4 py-8 text-center text-gray-500">No acquisition records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $acquisitions->links() }}
    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" x-data="{ currentStatus: @entangle('status') }" @keydown.escape.window="$wire.set('showModal', false)" role="dialog" aria-modal="true" aria-labelledby="acq-modal-title">
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-xl z-10 overflow-hidden my-auto flex flex-col max-h-[90vh]">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 id="acq-modal-title" class="text-xs sm:text-sm font-bold text-gray-900">
                        {{ $acquisitionIdBeingEdited ? 'Edit Acquisition Record' : 'Receive New Asset / PO' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveAcquisition" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Acquisition Number</label>
                            <input
                                type="text"
                                wire:model="acquisition_number"
                                disabled
                                readonly
                                class="mt-1 w-full text-xs font-mono rounded-md border-gray-300 border p-2 bg-gray-100 text-gray-500 cursor-not-allowed uppercase"
                            >
                            @error('acquisition_number') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Transaction / Invoice # <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                wire:model="transaction_number"
                                placeholder="e.g. PO-98214"
                                @if($acquisitionIdBeingEdited) disabled readonly @endif
                                class="mt-1 w-full text-xs font-mono rounded-md border-gray-300 border p-2 shadow-xs outline-none @if($acquisitionIdBeingEdited) bg-gray-100 text-gray-500 cursor-not-allowed @else focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @endif uppercase"
                            >
                            @error('transaction_number') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Delivery Type (Read-Only when editing) --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Delivery Type <span class="text-red-500">*</span></label>
                            @if($acquisitionIdBeingEdited)
                                <input
                                    type="text"
                                    disabled
                                    readonly
                                    value="{{ ucfirst($delivery_type) }}"
                                    class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 bg-gray-100 text-gray-500 cursor-not-allowed capitalize"
                                >
                            @else
                                <select wire:model.live="delivery_type" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 capitalize">
                                    <option value="purchase">Purchase</option>
                                    <option value="donation">Donation</option>
                                </select>
                            @endif
                            @error('delivery_type') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Fulfillment Status (Read-Only & Auto-calculated) --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Fulfillment Status <span class="text-gray-400 font-normal">(Auto)</span></label>
                            <input
                                type="text"
                                disabled
                                readonly
                                :value="currentStatus.toUpperCase()"
                                class="mt-1 w-full text-xs font-semibold rounded-md border-gray-300 border p-2 bg-gray-100 text-gray-600 cursor-not-allowed uppercase"
                            >
                            @error('status') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Vendor Selection (Read-only when editing) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Vendor <span class="text-red-500">*</span></label>
                        @if ($acquisitionIdBeingEdited)
                            <input
                                type="text"
                                disabled
                                readonly
                                value="{{ $this->selectedVendor->company_name ?? 'N/A' }}"
                                class="w-full text-xs rounded-md border border-gray-300 p-2 bg-gray-100 text-gray-500 cursor-not-allowed capitalize"
                            >
                        @else
                            <div
                                x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: @entangle('vendor_id'),
                                    vendors: @js($vendors->map(fn($v) => ['id' => $v->id, 'name' => $v->company_name])),
                                    get filteredVendors() {
                                        if (this.search === '') return this.vendors;
                                        return this.vendors.filter(v => v.name.toLowerCase().includes(this.search.toLowerCase()));
                                    },
                                    get selectedName() {
                                        let found = this.vendors.find(v => v.id == this.selectedId);
                                        return found ? found.name : 'Select Vendor';
                                    }
                                }"
                                @click.outside="open = false"
                                class="relative"
                            >
                                <input type="hidden" wire:model="vendor_id">
                                <div
                                    @click="open = !open"
                                    class="w-full text-xs rounded-md border border-gray-300 p-2 bg-white shadow-xs flex justify-between items-center cursor-pointer select-none"
                                >
                                    <span :class="selectedId ? 'text-gray-900 font-medium capitalize' : 'text-gray-400'" x-text="selectedName"></span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>

                                <div x-show="open" style="display: none;" class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <div class="p-2 border-b border-gray-100 sticky top-0 bg-white">
                                        <input
                                            x-model="search"
                                            type="text"
                                            placeholder="Type to search vendor..."
                                            class="w-full text-xs px-2.5 py-1.5 border border-gray-200 rounded outline-none focus:border-blue-500"
                                            @click.stop
                                        >
                                    </div>
                                    <ul class="py-1">
                                        <template x-for="vendor in filteredVendors" :key="vendor.id">
                                            <li
                                                @click="selectedId = vendor.id; open = false; search = ''; $wire.set('vendor_id', vendor.id);"
                                                class="px-3 py-2 text-xs hover:bg-blue-50 cursor-pointer capitalize"
                                                :class="{'bg-blue-50 font-semibold text-blue-600': selectedId == vendor.id}"
                                                x-text="vendor.name"
                                            ></li>
                                        </template>
                                        <li x-show="filteredVendors.length === 0" class="px-3 py-2 text-xs text-gray-400 text-center">No vendors found</li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @error('vendor_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror

                        @if ($this->selectedVendor)
                            <div class="mt-2.5 p-2.5 sm:p-3 bg-blue-50/70 border border-blue-200 rounded-lg text-xs space-y-1 text-gray-700">
                                <div class="font-bold text-blue-900 flex items-center justify-between border-b border-blue-200 pb-1 capitalize">
                                    <span class="capitalize">{{ $this->selectedVendor->company_name }}</span>
                                    @if ($this->selectedVendor->contact_first_name || $this->selectedVendor->contact_last_name)
                                        <span class="text-[10px] font-semibold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded shrink-0 ml-2">
                                            {{ trim($this->selectedVendor->contact_first_name . ' ' . ($this->selectedVendor->contact_middle_name ? substr($this->selectedVendor->contact_middle_name, 0, 1) . '. ' : '') . $this->selectedVendor->contact_last_name) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-[11px] pt-1 capitalize">
                                    @if ($this->selectedVendor->contact_email)
                                        <div class="lowercase">
                                            <strong class="text-gray-900 capitalize">Email:</strong> {{ $this->selectedVendor->contact_email }}
                                        </div>
                                    @endif
                                    @if ($this->selectedVendor->contact_number)
                                        <div>
                                            <strong class="text-gray-900">Phone:</strong> {{ $this->selectedVendor->contact_number }}
                                        </div>
                                    @endif
                                    @if ($this->selectedVendor->company_address)
                                        <div class="col-span-1 sm:col-span-2">
                                            <strong class="text-gray-900">Address:</strong> {{ $this->selectedVendor->company_address }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Catalog Item Selection (Read-only when editing) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Catalog Item <span class="text-red-500">*</span></label>
                        @if ($acquisitionIdBeingEdited)
                            <input
                                type="text"
                                disabled
                                readonly
                                value="{{ $this->selectedCatalog->title ?? 'N/A' }}"
                                class="w-full text-xs rounded-md border border-gray-300 p-2 bg-gray-100 text-gray-500 cursor-not-allowed capitalize"
                            >
                        @else
                            <div
                                x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: @entangle('catalog_id'),
                                    catalogs: @js($catalogs->map(fn($c) => ['id' => $c->id, 'title' => $c->title, 'isbn' => $c->isbn_issn])),
                                    get filteredCatalogs() {
                                        if (this.search === '') return this.catalogs;
                                        let term = this.search.toLowerCase();
                                        return this.catalogs.filter(c => c.title.toLowerCase().includes(term) || (c.isbn && c.isbn.toLowerCase().includes(term)));
                                    },
                                    get selectedName() {
                                        let found = this.catalogs.find(c => c.id == this.selectedId);
                                        return found ? found.title : 'Select Catalog';
                                    }
                                }"
                                @click.outside="open = false"
                                class="relative"
                            >
                                <input type="hidden" wire:model="catalog_id">
                                <div
                                    @click="open = !open"
                                    class="w-full text-xs rounded-md border border-gray-300 p-2 bg-white shadow-xs flex justify-between items-center cursor-pointer select-none"
                                >
                                    <span :class="selectedId ? 'text-gray-900 font-medium capitalize' : 'text-gray-400'" x-text="selectedName"></span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>

                                <div x-show="open" style="display: none;" class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <div class="p-2 border-b border-gray-100 sticky top-0 bg-white">
                                        <input
                                            x-model="search"
                                            type="text"
                                            placeholder="Type to search title or ISBN..."
                                            class="w-full text-xs px-2.5 py-1.5 border border-gray-200 rounded outline-none focus:border-blue-500"
                                            @click.stop
                                        >
                                    </div>
                                    <ul class="py-1">
                                        <template x-for="catalog in filteredCatalogs" :key="catalog.id">
                                            <li
                                                @click="selectedId = catalog.id; open = false; search = ''; $wire.set('catalog_id', catalog.id);"
                                                class="px-3 py-2 text-xs hover:bg-blue-50 cursor-pointer capitalize"
                                                :class="{'bg-blue-50 font-semibold text-blue-600': selectedId == catalog.id}"
                                            >
                                                <div class="font-medium text-gray-900" x-text="catalog.title"></div>
                                                <div class="text-[10px] text-gray-400 uppercase font-mono" x-text="catalog.isbn ? 'ISBN/ISSN: ' + catalog.isbn : 'N/A'"></div>
                                            </li>
                                        </template>
                                        <li x-show="filteredCatalogs.length === 0" class="px-3 py-2 text-xs text-gray-400 text-center">No catalog items found</li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @error('catalog_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    @if ($this->selectedCatalog)
                        <div class="p-2.5 sm:p-3 bg-blue-50/70 border border-blue-200 rounded-lg text-xs space-y-1.5">
                            <div class="font-bold text-blue-900 border-b border-blue-200 pb-1 flex justify-between items-center capitalize">
                                <span>Catalog Reference</span>
                                <span class="text-[10px] font-semibold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded uppercase font-mono">
                                    ISBN/ISSN: {{ $this->selectedCatalog->isbn_issn ?: 'N/A' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5 text-gray-700 text-[11px] pt-1 capitalize">
                                <div>
                                    <strong class="text-gray-900">Author:</strong> {{ $this->selectedCatalog->author->name ?? 'N/A' }}
                                </div>
                                <div>
                                    <strong class="text-gray-900">Type:</strong> {{ $this->selectedCatalog->assetType->name ?? 'N/A' }}
                                </div>
                                <div>
                                    <strong class="text-gray-900">General Reference:</strong> {{ $this->selectedCatalog->generalReference->name ?? ($this->selectedCatalog->general_reference ?? 'N/A') }}
                                </div>
                                <div>
                                    <strong class="text-gray-900">Publisher:</strong> {{ $this->selectedCatalog->publisher->name ?? 'N/A' }}
                                </div>
                                <div class="col-span-2">
                                    <strong class="text-gray-900">Edition:</strong> {{ $this->selectedCatalog->edition ?: 'N/A' }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        {{-- Quantity Ordered --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Quantity Ordered <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.live="quantity_ordered" min="1" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="1">
                            @error('quantity_ordered') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Quantity Received --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Quantity Received <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.live="quantity_received" min="0" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="0">
                            @error('quantity_received') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Unit Cost (Read-Only when editing) --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Unit Cost <span class="text-red-500">*</span></label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model.live.debounce.300ms="unit_cost"
                                @if($acquisitionIdBeingEdited) disabled readonly @endif
                                class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs outline-none @if($acquisitionIdBeingEdited) bg-gray-100 text-gray-500 cursor-not-allowed @else focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @endif"
                                placeholder="0.00"
                            >
                            @error('unit_cost') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Expected Delivery Date (Editable) --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Expected Delivery Date <span class="text-red-500">*</span></label>
                            <input
                                type="date"
                                wire:model="delivery_date"
                                min="{{ now()->format('Y-m-d') }}"
                                max="{{ now()->addMonths(2)->format('Y-m-d') }}"
                                class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                            >
                            @error('delivery_date') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Received Date (Editable) --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700">
                                Received Date
                                <span class="text-red-500" x-show="currentStatus !== 'pending'">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model="received_date"
                                max="{{ now()->format('Y-m-d') }}"
                                :disabled="currentStatus === 'pending'"
                                :class="currentStatus === 'pending' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white'"
                                class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                            @error('received_date') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Live Calculated Total Cost Preview --}}
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-md flex justify-between items-center text-xs">
                        <span class="font-medium text-gray-600">Calculated Total Cost:</span>
                        <span class="text-xs sm:text-sm font-bold font-mono text-gray-900">₱{{ number_format($this->calculatedTotalCost, 2) }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">Remarks</label>
                        <textarea wire:model="remarks" rows="2" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 capitalize-first" placeholder="Additional notes or receiving status..."></textarea>
                        @error('remarks') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-gray-200 flex flex-wrap justify-end gap-2">
                        <button wire:click="$set('showModal', false)" type="button" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                            Cancel
                        </button>

                        @if(!$acquisitionIdBeingEdited)
                            <button wire:click="saveAndCreateAnother" type="button" wire:loading.attr="disabled" class="px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md shadow-xs transition cursor-pointer disabled:opacity-50">
                                <span wire:loading.remove>Save & Add Another</span>
                                <span wire:loading>Saving...</span>
                            </button>
                        @endif

                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-xs transition cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove>{{ $acquisitionIdBeingEdited ? 'Save Changes' : 'Create Acquisition' }}</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Accessions Inspection Modal --}}
    @if ($showAccessionsModal && $viewingAcquisition)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" x-data @keydown.escape.window="$wire.set('showAccessionsModal', false)" role="dialog" aria-modal="true">
            <div wire:click="$set('showAccessionsModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-4xl z-10 overflow-hidden my-auto flex flex-col max-h-[90vh]">

                {{-- Modal Header --}}
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-gray-900 uppercase">
                            Accession Items for: <span class="text-blue-600 font-mono">{{ $viewingAcquisition->acquisition_number }}</span>
                        </h3>
                        <p class="text-[11px] text-gray-500 capitalize">
                            Title: <span class="font-medium text-gray-700">{{ $viewingAcquisition->catalog->title ?? 'N/A' }}</span> (Ordered: {{ $viewingAcquisition->quantity_ordered }} | Received: {{ $viewingAcquisition->quantity_received }} | Accessioned: {{ $viewingAcquisition->accessions->count() }})
                        </p>
                    </div>
                    <button wire:click="$set('showAccessionsModal', false)" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                {{-- Modal Body / Table of Generated Accessions --}}
                <div class="p-4 sm:p-6 overflow-y-auto space-y-4">
                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs">
                        <table class="w-full text-left text-xs text-gray-700">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[10px] border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-3 py-2.5">#</th>
                                    <th scope="col" class="px-3 py-2.5">Accession Number (Barcode)</th>
                                    <th scope="col" class="px-3 py-2.5">Condition</th>
                                    <th scope="col" class="px-3 py-2.5">Status</th>
                                    <th scope="col" class="px-3 py-2.5">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($viewingAcquisition->accessions as $index => $item)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-3 py-2 font-mono text-gray-400">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2 font-mono font-bold text-gray-900">{{ $item->accession_number }}</td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full uppercase
                                                {{ match($item->condition) {
                                                    'new' => 'bg-emerald-100 text-emerald-700',
                                                    'good' => 'bg-blue-100 text-blue-700',
                                                    'minor damage', 'severe damaged' => 'bg-amber-100 text-amber-700',
                                                    'lost', 'missing' => 'bg-red-100 text-red-700',
                                                    default => 'bg-gray-100 text-gray-700'
                                                } }}">
                                                {{ $item->condition }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full uppercase
                                                {{ match($item->status) {
                                                    'available' => 'bg-emerald-100 text-emerald-700',
                                                    'on loan' => 'bg-blue-100 text-blue-700',
                                                    'stored' => 'bg-gray-100 text-gray-700',
                                                    'under maintenance' => 'bg-purple-100 text-purple-700',
                                                    'dumped' => 'bg-red-100 text-red-700',
                                                    default => 'bg-gray-100 text-gray-700'
                                                } }}">
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-500">{{ $item->remarks ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">No accession items generated yet for this acquisition.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-gray-50 px-5 py-3 border-t border-gray-200 flex justify-end shrink-0">
                    <button wire:click="$set('showAccessionsModal', false)" type="button" class="px-4 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 transition cursor-pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" x-data @keydown.escape.window="$wire.set('showDeleteModal', false)" role="dialog" aria-modal="true">
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10 overflow-hidden p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900">Delete Acquisition Record</h3>
                <p class="text-xs text-gray-600">Are you sure you want to delete this acquisition record? This action cannot be undone.</p>

                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                        Cancel
                    </button>
                    <button wire:click="deleteAcquisition" type="button" class="px-4 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md shadow-xs transition cursor-pointer">
                        Delete Record
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
