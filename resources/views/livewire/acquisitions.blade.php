{{-- resources/views/livewire/acquisitions.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-4">
    {{-- Header Container --}}
    <section aria-label="Acquisitions header" class="bg-surface-card p-4 sm:p-5 rounded-2xl border border-surface-border shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-content-heading tracking-tight">Acquisitions</h2>
            <p class="text-xs text-content-body mt-0.5">Manage purchase orders, multi-item invoices, pending shipments, and receiving records.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64 lg:w-72">
                <label for="acq-search" class="sr-only">Search acquisitions</label>
                <input
                    id="acq-search"
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search ACQ #, Txn #, Status, Catalog, Vendor..."
                    class="w-full pl-9 pr-8 py-2 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary outline-none transition"
                >
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
                @if(!empty($search))
                    <button
                        wire:click="$set('search', '')"
                        type="button"
                        class="absolute right-2.5 top-2 text-content-muted hover:text-content-heading text-xs cursor-pointer rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                        aria-label="Clear search"
                    >
                        &times;
                    </button>
                @endif
            </div>

            <!-- Export Actions -->
            <div class="flex items-center gap-2">
                <button
                    wire:click="exportExcel"
                    wire:loading.attr="disabled"
                    wire:target="exportExcel"
                    type="button"
                    class="px-3.5 py-2 text-xs font-semibold text-content-heading bg-surface-canvas hover:bg-brand-tint-light hover:text-brand-primary border border-surface-border rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0 whitespace-nowrap focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
                >
                    <x-heroicon-o-table-cells class="w-4 h-4" aria-hidden="true" />
                    <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                    <span wire:loading wire:target="exportExcel">Exporting...</span>
                </button>

                <button
                    wire:click="exportPdf"
                    wire:loading.attr="disabled"
                    wire:target="exportPdf"
                    type="button"
                    class="px-3.5 py-2 text-xs font-semibold text-content-heading bg-surface-canvas hover:bg-brand-tint-light hover:text-brand-primary border border-surface-border rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0 whitespace-nowrap focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
                >
                    <x-heroicon-o-document-arrow-down class="w-4 h-4" aria-hidden="true" />
                    <span wire:loading.remove wire:target="exportPdf">Export PDF</span>
                    <span wire:loading wire:target="exportPdf">Exporting...</span>
                </button>
            </div>

            <!-- Action Button -->
            <button
                wire:click="openCreateModal"
                wire:loading.attr="disabled"
                wire:target="openCreateModal"
                type="button"
                class="px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs shrink-0 whitespace-nowrap focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
            >
                <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
                <span>Receive Asset / PO</span>
            </button>
        </div>
    </section>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-surface-border rounded-2xl shadow-2xs bg-surface-card">
        <table class="w-full text-left text-xs text-content-body table-auto">
            <thead class="bg-surface-canvas text-content-muted uppercase tracking-wider text-[11px] font-semibold border-b border-surface-border">
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
            <tbody class="divide-y divide-surface-border/70 bg-surface-card">
                @forelse($acquisitions as $acq)
                    @php
                        $authorName = $acq->catalog?->author?->name;
                        $assetTypeName = $acq->catalog?->assetType?->name;
                        $catalogTitle = $acq->catalog?->title;
                        $vendorName = $acq->vendor?->company_name;
                    @endphp
                    <tr wire:key="acq-row-{{ $acq->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                        <td class="px-3 py-3 whitespace-nowrap">
                            <div class="font-mono font-bold text-brand-primary uppercase">{{ $acq->acquisition_number }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <div class="font-mono text-content-body uppercase">{{ $acq->transaction_number }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full uppercase inline-block {{ match($acq->status) {
                                'pending' => 'bg-brand-secondary/15 text-brand-secondary',
                                'partial' => 'bg-brand-tint text-brand-primary',
                                'delivered' => 'bg-brand-tint-light text-brand-primary',
                                default => 'bg-surface-canvas text-content-heading border border-surface-border'
                            } }}">
                                {{ $acq->status }}
                            </span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full uppercase inline-block {{ $acq->delivery_type === 'donation' ? 'bg-brand-tint text-brand-primary' : 'bg-brand-tint-light text-brand-primary' }}">
                                {{ $acq->delivery_type }}
                            </span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap font-mono text-content-body">
                            {{ $acq->catalog?->isbn_issn ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap font-semibold text-content-heading max-w-xs truncate" title="{{ $catalogTitle ? Str::title($catalogTitle) : '—' }}">
                            {{ $catalogTitle ? Str::title($catalogTitle) : '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-content-body max-w-xs truncate" title="{{ $authorName ? Str::title($authorName) : 'N/A' }}">
                            {{ $authorName ? Str::title($authorName) : 'N/A' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-content-body">
                            {{ $assetTypeName ? Str::title($assetTypeName) : 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-content-body whitespace-nowrap max-w-xs truncate" title="{{ $vendorName ? Str::title($vendorName) : '—' }}">
                            {{ $vendorName ? Str::title($vendorName) : '—' }}
                        </td>
                        <td class="px-2 py-3 text-center text-content-heading font-semibold whitespace-nowrap">{{ $acq->quantity_ordered }}</td>
                        <td class="px-2 py-3 text-center text-content-heading font-semibold whitespace-nowrap">{{ $acq->quantity_received }}</td>
                        <td class="px-3 py-3 text-right text-content-body font-mono whitespace-nowrap">₱{{ number_format((float) $acq->unit_cost, 2) }}</td>
                        <td class="px-3 py-3 text-right text-content-heading font-mono font-bold whitespace-nowrap">₱{{ number_format((float) $acq->total_cost, 2) }}</td>
                        <td class="px-3 py-3 text-content-muted whitespace-nowrap">{{ $acq->delivery_date ? $acq->delivery_date->format('M d, Y') : '—' }}</td>
                        <td class="px-3 py-3 text-content-muted whitespace-nowrap">{{ $acq->received_date ? $acq->received_date->format('M d, Y') : '— (Pending)' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <button wire:click="viewAccessions({{ $acq->id }})" type="button" class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2 py-1 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">View</button>
                            <button wire:click="addMoreFromExisting({{ $acq->id }})" type="button" class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2 py-1 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">Add</button>
                            <button wire:click="openEditModal({{ $acq->id }})" type="button" class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2 py-1 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">Edit</button>
                            <button wire:click="confirmDelete({{ $acq->id }})" type="button" class="text-brand-secondary hover:bg-brand-secondary/10 font-semibold px-2 py-1 rounded-lg transition cursor-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary">Del</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="px-4 py-10 text-center">
                            <p class="text-xs font-semibold text-content-heading">No acquisition records found.</p>
                            <p class="text-[11px] text-content-muted mt-1">Adjust your search or click Receive Asset / PO to create a record.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-3 py-2 bg-surface-card border border-surface-border rounded-2xl shadow-2xs">
        {{ $acquisitions->links() }}
    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            x-data="{ currentStatus: @entangle('status') }"
            @keydown.escape.window="$wire.set('showModal', false)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="acq-modal-title"
        >
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-xl z-10 overflow-hidden my-auto flex flex-col max-h-[90dvh] border border-surface-border">
                <div class="bg-surface-canvas px-5 py-3.5 border-b border-surface-border flex justify-between items-center shrink-0">
                    <h3 id="acq-modal-title" class="text-xs sm:text-sm font-bold text-content-heading">
                        {{ $acquisitionIdBeingEdited ? 'Edit Acquisition Record' : 'Receive New Asset / PO' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-xl font-bold cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="saveAcquisition" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="acq_number" class="block text-xs font-semibold text-content-heading mb-1">Acquisition Number</label>
                            <input
                                id="acq_number"
                                type="text"
                                wire:model="acquisition_number"
                                disabled
                                readonly
                                class="w-full text-xs font-mono rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-muted cursor-not-allowed uppercase"
                            >
                            @error('acquisition_number') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="acq_transaction_number" class="block text-xs font-semibold text-content-heading mb-1">
                                Transaction / Invoice # <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="acq_transaction_number"
                                type="text"
                                wire:model="transaction_number"
                                placeholder="e.g. PO-98214"
                                @if($acquisitionIdBeingEdited) disabled readonly @endif
                                class="w-full text-xs font-mono rounded-xl border border-surface-border p-2 shadow-2xs outline-none uppercase transition @if($acquisitionIdBeingEdited) bg-surface-canvas text-content-muted cursor-not-allowed @else bg-surface-canvas text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 @endif"
                            >
                            @error('transaction_number') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Delivery Type (Read-Only when editing) --}}
                        <div>
                            <label for="acq_delivery_type" class="block text-xs font-semibold text-content-heading mb-1">
                                Delivery Type <span class="text-brand-secondary">*</span>
                            </label>
                            @if($acquisitionIdBeingEdited)
                                <input
                                    id="acq_delivery_type"
                                    type="text"
                                    disabled
                                    readonly
                                    value="{{ Str::title((string) $delivery_type) }}"
                                    class="w-full text-xs rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-muted cursor-not-allowed"
                                >
                            @else
                                <select
                                    id="acq_delivery_type"
                                    wire:model.live="delivery_type"
                                    class="w-full text-xs rounded-xl border border-surface-border p-2 shadow-2xs bg-surface-canvas text-content-heading outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                                >
                                    <option value="purchase">Purchase</option>
                                    <option value="donation">Donation</option>
                                </select>
                            @endif
                            @error('delivery_type') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Fulfillment Status (Read-Only & Auto-calculated) --}}
                        <div>
                            <label for="acq_fulfillment_status" class="block text-xs font-semibold text-content-heading mb-1">
                                Fulfillment Status <span class="text-content-muted font-normal">(Auto)</span>
                            </label>
                            <input
                                id="acq_fulfillment_status"
                                type="text"
                                disabled
                                readonly
                                :value="(currentStatus || '').toUpperCase()"
                                class="w-full text-xs font-mono font-semibold rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-heading cursor-not-allowed uppercase"
                            >
                            @error('status') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Vendor Selection (Read-only when editing) --}}
                    <div>
                        <span class="block text-xs font-semibold text-content-heading mb-1">
                            Vendor <span class="text-brand-secondary">*</span>
                        </span>
                        @if ($acquisitionIdBeingEdited)
                            <input
                                type="text"
                                aria-label="Selected Vendor"
                                disabled
                                readonly
                                value="{{ $this->selectedVendor?->company_name ? Str::title($this->selectedVendor->company_name) : 'N/A' }}"
                                class="w-full text-xs rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-muted cursor-not-allowed"
                            >
                        @else
                            <div
                                x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: @entangle('vendor_id'),
                                    vendors: @js($vendors->map(fn($v) => ['id' => $v->id, 'name' => $v->company_name])),
                                    get filteredVendors() {
                                        if (this.search.trim() === '') return this.vendors;
                                        const term = this.search.trim().toLowerCase();
                                        return this.vendors.filter(v => String(v.name ?? '').toLowerCase().includes(term));
                                    },
                                    get selectedName() {
                                        const found = this.vendors.find(v => String(v.id) === String(this.selectedId));
                                        return found ? found.name : 'Select Vendor';
                                    }
                                }"
                                @click.outside="open = false"
                                class="relative"
                            >
                                <button
                                    type="button"
                                    @click="open = !open; if (open) { search = ''; $nextTick(() => $refs.vendorSearch?.focus()); }"
                                    class="w-full text-xs rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-heading shadow-2xs flex justify-between items-center gap-2 cursor-pointer select-none focus-visible:outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                                >
                                    <span :class="selectedId ? 'text-content-heading font-semibold capitalize truncate' : 'text-content-muted truncate'" x-text="selectedName"></span>
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-content-muted shrink-0 transition-transform" ::class="open ? 'rotate-180 text-brand-primary' : ''" aria-hidden="true" />
                                </button>

                                <div x-show="open" x-cloak class="absolute z-30 mt-1 w-full bg-surface-card border border-surface-border rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                    <div class="p-2 border-b border-surface-border sticky top-0 bg-surface-card">
                                        <input
                                            x-ref="vendorSearch"
                                            x-model="search"
                                            type="text"
                                            placeholder="Type to search vendor..."
                                            class="w-full text-xs px-2.5 py-1.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-lg outline-none focus:bg-surface-card focus:border-brand-primary"
                                            @click.stop
                                        >
                                    </div>
                                    <ul class="py-1">
                                        <template x-for="vendor in filteredVendors" :key="vendor.id">
                                            <li
                                                @click="selectedId = vendor.id; open = false; search = ''; $wire.set('vendor_id', vendor.id);"
                                                class="px-3 py-2 text-xs hover:bg-brand-tint-light hover:text-brand-primary cursor-pointer capitalize flex items-center justify-between transition-colors"
                                                :class="String(selectedId) === String(vendor.id) ? 'bg-brand-tint-light font-semibold text-brand-primary' : 'text-content-body'"
                                            >
                                                <span x-text="vendor.name"></span>
                                                <x-heroicon-o-check x-show="String(selectedId) === String(vendor.id)" class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                                            </li>
                                        </template>
                                        <li x-show="filteredVendors.length === 0" class="px-3 py-2 text-xs text-content-muted text-center italic">No vendors found</li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @error('vendor_id') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror

                        @if ($this->selectedVendor)
                            <div class="mt-2.5 p-2.5 sm:p-3 bg-brand-tint-light/70 border border-brand-primary/25 rounded-xl text-xs space-y-1 text-content-body">
                                <div class="font-bold text-content-heading flex items-center justify-between border-b border-brand-primary/20 pb-1">
                                    <span class="capitalize">{{ $this->selectedVendor->company_name }}</span>
                                    @if ($this->selectedVendor->contact_first_name || $this->selectedVendor->contact_last_name)
                                        <span class="text-[10px] font-semibold text-brand-primary bg-brand-tint px-1.5 py-0.5 rounded shrink-0 ml-2">
                                            {{ Str::title(trim($this->selectedVendor->contact_first_name . ' ' . ($this->selectedVendor->contact_middle_name ? substr($this->selectedVendor->contact_middle_name, 0, 1) . '. ' : '') . $this->selectedVendor->contact_last_name)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-[11px] pt-1">
                                    @if ($this->selectedVendor->contact_email)
                                        <div class="truncate">
                                            <strong class="text-content-heading">Email:</strong> <span class="lowercase">{{ $this->selectedVendor->contact_email }}</span>
                                        </div>
                                    @endif
                                    @if ($this->selectedVendor->contact_number)
                                        <div class="font-mono">
                                            <strong class="text-content-heading font-sans">Phone:</strong> {{ $this->selectedVendor->contact_number }}
                                        </div>
                                    @endif
                                    @if ($this->selectedVendor->company_address)
                                        <div class="col-span-1 sm:col-span-2 capitalize">
                                            <strong class="text-content-heading">Address:</strong> {{ $this->selectedVendor->company_address }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Catalog Item Selection (Read-only when editing) --}}
                    <div>
                        <span class="block text-xs font-semibold text-content-heading mb-1">
                            Catalog Item <span class="text-brand-secondary">*</span>
                        </span>
                        @if ($acquisitionIdBeingEdited)
                            <input
                                type="text"
                                aria-label="Selected Catalog Item"
                                disabled
                                readonly
                                value="{{ $this->selectedCatalog?->title ? Str::title($this->selectedCatalog->title) : 'N/A' }}"
                                class="w-full text-xs rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-muted cursor-not-allowed"
                            >
                        @else
                            <div
                                x-data="{
                                    open: false,
                                    search: '',
                                    selectedId: @entangle('catalog_id'),
                                    catalogs: @js($catalogs->map(fn($c) => ['id' => $c->id, 'title' => $c->title, 'isbn' => $c->isbn_issn])),
                                    get filteredCatalogs() {
                                        if (this.search.trim() === '') return this.catalogs;
                                        const term = this.search.trim().toLowerCase();
                                        return this.catalogs.filter(c => String(c.title ?? '').toLowerCase().includes(term) || (c.isbn && String(c.isbn).toLowerCase().includes(term)));
                                    },
                                    get selectedName() {
                                        const found = this.catalogs.find(c => String(c.id) === String(this.selectedId));
                                        return found ? found.title : 'Select Catalog';
                                    }
                                }"
                                @click.outside="open = false"
                                class="relative"
                            >
                                <button
                                    type="button"
                                    @click="open = !open; if (open) { search = ''; $nextTick(() => $refs.catalogSearch?.focus()); }"
                                    class="w-full text-xs rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-heading shadow-2xs flex justify-between items-center gap-2 cursor-pointer select-none focus-visible:outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                                >
                                    <span :class="selectedId ? 'text-content-heading font-semibold capitalize truncate' : 'text-content-muted truncate'" x-text="selectedName"></span>
                                    <x-heroicon-o-chevron-down class="w-4 h-4 text-content-muted shrink-0 transition-transform" ::class="open ? 'rotate-180 text-brand-primary' : ''" aria-hidden="true" />
                                </button>

                                <div x-show="open" x-cloak class="absolute z-30 mt-1 w-full bg-surface-card border border-surface-border rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                    <div class="p-2 border-b border-surface-border sticky top-0 bg-surface-card">
                                        <input
                                            x-ref="catalogSearch"
                                            x-model="search"
                                            type="text"
                                            placeholder="Type to search title or ISBN..."
                                            class="w-full text-xs px-2.5 py-1.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-lg outline-none focus:bg-surface-card focus:border-brand-primary"
                                            @click.stop
                                        >
                                    </div>
                                    <ul class="py-1">
                                        <template x-for="catalog in filteredCatalogs" :key="catalog.id">
                                            <li
                                                @click="selectedId = catalog.id; open = false; search = ''; $wire.set('catalog_id', catalog.id);"
                                                class="px-3 py-2 text-xs hover:bg-brand-tint-light cursor-pointer capitalize flex items-center justify-between gap-2 transition-colors"
                                                :class="String(selectedId) === String(catalog.id) ? 'bg-brand-tint-light font-semibold text-brand-primary' : 'text-content-body'"
                                            >
                                                <div>
                                                    <div class="font-medium text-content-heading" x-text="catalog.title"></div>
                                                    <div class="text-[10px] text-content-muted uppercase font-mono" x-text="catalog.isbn ? 'ISBN/ISSN: ' + catalog.isbn : 'N/A'"></div>
                                                </div>
                                                <x-heroicon-o-check x-show="String(selectedId) === String(catalog.id)" class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                                            </li>
                                        </template>
                                        <li x-show="filteredCatalogs.length === 0" class="px-3 py-2 text-xs text-content-muted text-center italic">No catalog items found</li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @error('catalog_id') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    @if ($this->selectedCatalog)
                        <div class="p-2.5 sm:p-3 bg-brand-tint-light/70 border border-brand-primary/25 rounded-xl text-xs space-y-1.5">
                            <div class="font-bold text-content-heading border-b border-brand-primary/20 pb-1 flex justify-between items-center gap-2">
                                <span>Catalog Reference</span>
                                <span class="text-[10px] font-semibold text-brand-primary bg-brand-tint px-1.5 py-0.5 rounded uppercase font-mono shrink-0">
                                    ISBN/ISSN: {{ $this->selectedCatalog->isbn_issn ?: 'N/A' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5 text-content-body text-[11px] pt-1 capitalize">
                                <div>
                                    <strong class="text-content-heading">Author:</strong> {{ $this->selectedCatalog->author?->name ?? 'N/A' }}
                                </div>
                                <div>
                                    <strong class="text-content-heading">Type:</strong> {{ $this->selectedCatalog->assetType?->name ?? 'N/A' }}
                                </div>
                                <div>
                                    <strong class="text-content-heading">General Reference:</strong> {{ $this->selectedCatalog->generalReference?->name ?? ($this->selectedCatalog->general_reference ?? 'N/A') }}
                                </div>
                                <div>
                                    <strong class="text-content-heading">Publisher:</strong> {{ $this->selectedCatalog->publisher?->name ?? 'N/A' }}
                                </div>
                                <div class="col-span-2">
                                    <strong class="text-content-heading">Edition:</strong> {{ $this->selectedCatalog->edition ?: 'N/A' }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        {{-- Quantity Ordered --}}
                        <div>
                            <label for="acq_quantity_ordered" class="block text-xs font-semibold text-content-heading mb-1">
                                Quantity Ordered <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="acq_quantity_ordered"
                                type="number"
                                wire:model.live.debounce.300ms="quantity_ordered"
                                min="1"
                                class="w-full text-xs font-mono rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-heading shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                                placeholder="1"
                            >
                            @error('quantity_ordered') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Quantity Received --}}
                        <div>
                            <label for="acq_quantity_received" class="block text-xs font-semibold text-content-heading mb-1">
                                Quantity Received <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="acq_quantity_received"
                                type="number"
                                wire:model.live.debounce.300ms="quantity_received"
                                min="0"
                                class="w-full text-xs font-mono rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-heading shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                                placeholder="0"
                            >
                            @error('quantity_received') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Unit Cost (Read-Only when editing) --}}
                        <div>
                            <label for="acq_unit_cost" class="block text-xs font-semibold text-content-heading mb-1">
                                Unit Cost <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="acq_unit_cost"
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model.live.debounce.300ms="unit_cost"
                                @if($acquisitionIdBeingEdited) disabled readonly @endif
                                class="w-full text-xs font-mono rounded-xl border border-surface-border p-2 shadow-2xs outline-none transition @if($acquisitionIdBeingEdited) bg-surface-canvas text-content-muted cursor-not-allowed @else bg-surface-canvas text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 @endif"
                                placeholder="0.00"
                            >
                            @error('unit_cost') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Expected Delivery Date (Editable) --}}
                        <div>
                            <label for="acq_delivery_date" class="block text-xs font-semibold text-content-heading mb-1">
                                Expected Delivery Date <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="acq_delivery_date"
                                type="date"
                                wire:model="delivery_date"
                                min="{{ now()->format('Y-m-d') }}"
                                max="{{ now()->addMonths(2)->format('Y-m-d') }}"
                                class="w-full text-xs font-mono rounded-xl border border-surface-border p-2 bg-surface-canvas text-content-heading shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                            >
                            @error('delivery_date') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Received Date (Editable) --}}
                        <div>
                            <label for="acq_received_date" class="block text-xs font-semibold text-content-heading mb-1">
                                Received Date
                                <span class="text-brand-secondary" x-show="currentStatus !== 'pending'" x-cloak>*</span>
                            </label>
                            <input
                                id="acq_received_date"
                                type="date"
                                wire:model="received_date"
                                max="{{ now()->format('Y-m-d') }}"
                                :disabled="currentStatus === 'pending'"
                                :class="currentStatus === 'pending' ? 'bg-surface-canvas/70 text-content-muted cursor-not-allowed opacity-70' : 'bg-surface-canvas text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15'"
                                class="w-full text-xs font-mono rounded-xl border border-surface-border p-2 shadow-2xs outline-none transition"
                            >
                            @error('received_date') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Live Calculated Total Cost Preview --}}
                    <div class="p-3 bg-brand-tint-light/70 border border-brand-primary/25 rounded-xl flex justify-between items-center text-xs">
                        <span class="font-semibold text-content-heading">Calculated Total Cost:</span>
                        <span class="text-xs sm:text-sm font-bold font-mono text-brand-primary">₱{{ number_format((float) $this->calculatedTotalCost, 2) }}</span>
                    </div>

                    <div>
                        <label for="acq_remarks" class="block text-xs font-semibold text-content-heading mb-1">Remarks</label>
                        <textarea
                            id="acq_remarks"
                            wire:model="remarks"
                            rows="2"
                            class="w-full text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                            placeholder="Additional notes or receiving status..."
                        ></textarea>
                        @error('remarks') <span class="text-[11px] text-brand-secondary mt-0.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-surface-border/70 flex flex-wrap justify-end gap-2">
                        <button
                            wire:click="$set('showModal', false)"
                            type="button"
                            class="px-3.5 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl transition cursor-pointer"
                        >
                            Cancel
                        </button>

                        @if(!$acquisitionIdBeingEdited)
                            <button
                                wire:click="saveAndCreateAnother"
                                wire:target="saveAndCreateAnother"
                                type="button"
                                wire:loading.attr="disabled"
                                class="px-3.5 py-2 text-xs font-semibold text-brand-primary bg-brand-tint-light hover:bg-brand-tint border border-brand-primary/25 rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="saveAndCreateAnother">Save &amp; Add Another</span>
                                <span wire:loading wire:target="saveAndCreateAnother">Saving...</span>
                            </button>
                        @endif

                        <button
                            type="submit"
                            wire:target="saveAcquisition"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50 flex items-center gap-2"
                        >
                            <span wire:loading wire:target="saveAcquisition" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                            <span wire:loading.remove wire:target="saveAcquisition">{{ $acquisitionIdBeingEdited ? 'Save Changes' : 'Create Acquisition' }}</span>
                            <span wire:loading wire:target="saveAcquisition">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Accessions Inspection Modal --}}
    @if ($showAccessionsModal && $viewingAcquisition)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            x-data
            @keydown.escape.window="$wire.set('showAccessionsModal', false)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="acq-accessions-modal-title"
        >
            <div wire:click="$set('showAccessionsModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-4xl z-10 overflow-hidden my-auto flex flex-col max-h-[90dvh] border border-surface-border">

                {{-- Modal Header --}}
                <div class="bg-surface-canvas px-5 py-3.5 border-b border-surface-border flex justify-between items-start gap-3 shrink-0">
                    <div>
                        <h3 id="acq-accessions-modal-title" class="text-xs sm:text-sm font-bold text-content-heading uppercase">
                            Accession Items for: <span class="text-brand-primary font-mono">{{ $viewingAcquisition->acquisition_number }}</span>
                        </h3>
                        <p class="text-[11px] text-content-body mt-0.5">
                            Title: <span class="font-semibold text-content-heading capitalize">{{ $viewingAcquisition->catalog?->title ?? 'N/A' }}</span>
                            <span class="text-content-muted">(Ordered: {{ $viewingAcquisition->quantity_ordered }} | Received: {{ $viewingAcquisition->quantity_received }} | Accessioned: {{ $viewingAcquisition->accessions->count() }})</span>
                        </p>
                    </div>
                    <button
                        wire:click="$set('showAccessionsModal', false)"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-xl font-bold cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                {{-- Modal Body / Table of Generated Accessions --}}
                <div class="p-4 sm:p-6 overflow-y-auto space-y-4">
                    <div class="overflow-x-auto border border-surface-border rounded-xl shadow-2xs">
                        <table class="w-full text-left text-xs text-content-body">
                            <thead class="bg-surface-canvas text-content-muted uppercase tracking-wider text-[10px] font-semibold border-b border-surface-border">
                                <tr>
                                    <th scope="col" class="px-3 py-2.5 whitespace-nowrap">#</th>
                                    <th scope="col" class="px-3 py-2.5 whitespace-nowrap">Accession Number (Barcode)</th>
                                    <th scope="col" class="px-3 py-2.5 whitespace-nowrap">Condition</th>
                                    <th scope="col" class="px-3 py-2.5 whitespace-nowrap">Status</th>
                                    <th scope="col" class="px-3 py-2.5 whitespace-nowrap">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-border/70 bg-surface-card">
                                @forelse($viewingAcquisition->accessions as $index => $item)
                                    <tr wire:key="acq-acc-item-{{ $item->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                                        <td class="px-3 py-2 font-mono text-content-muted whitespace-nowrap">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2 font-mono font-bold text-content-heading whitespace-nowrap">{{ $item->accession_number }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full uppercase inline-block
                                                {{ match($item->condition) {
                                                    'new', 'good' => 'bg-brand-tint-light text-brand-primary',
                                                    'minor damage', 'severe damaged' => 'bg-brand-tint text-brand-primary',
                                                    'lost', 'missing' => 'bg-brand-secondary/15 text-brand-secondary',
                                                    default => 'bg-surface-canvas text-content-heading border border-surface-border'
                                                } }}">
                                                {{ $item->condition }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full uppercase inline-block
                                                {{ match($item->status) {
                                                    'available' => 'bg-brand-tint-light text-brand-primary',
                                                    'on loan', 'stored', 'under maintenance' => 'bg-brand-tint text-brand-primary',
                                                    'dumped' => 'bg-brand-secondary/15 text-brand-secondary',
                                                    default => 'bg-surface-canvas text-content-heading border border-surface-border'
                                                } }}">
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-content-body">{{ $item->remarks ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center">
                                            <p class="text-xs font-semibold text-content-heading">No accession items generated yet</p>
                                            <p class="text-[11px] text-content-muted mt-1">Items appear here once received copies are accessioned.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-surface-canvas px-5 py-3 border-t border-surface-border flex justify-end shrink-0">
                    <button
                        wire:click="$set('showAccessionsModal', false)"
                        type="button"
                        class="px-4 py-1.5 text-xs font-semibold text-content-heading bg-surface-card border border-surface-border rounded-xl hover:bg-surface-canvas transition cursor-pointer"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            x-data
            @keydown.escape.window="$wire.set('showDeleteModal', false)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="acq-delete-modal-title"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-md z-10 overflow-hidden p-6 border border-surface-border space-y-4 text-center">
                <div class="w-12 h-12 bg-brand-secondary/15 text-brand-secondary rounded-full flex items-center justify-center mx-auto shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>

                <div>
                    <h3 id="acq-delete-modal-title" class="text-sm font-bold text-content-heading">Delete Acquisition Record</h3>
                    <p class="text-xs text-content-body mt-1">Are you sure you want to delete this acquisition record? This action cannot be undone.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 pt-2">
                    <button
                        wire:click="$set('showDeleteModal', false)"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteAcquisition"
                        wire:target="deleteAcquisition"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-secondary hover:opacity-90 rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <span wire:loading wire:target="deleteAcquisition" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Delete Record</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
