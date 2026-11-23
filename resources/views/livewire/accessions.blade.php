{{-- resources/views/livewire/accessions.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-4">
    {{-- Header Container --}}
    <section aria-label="Accessions header" class="bg-surface-card p-4 sm:p-5 rounded-2xl border border-surface-border shadow-2xs flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-content-heading tracking-tight">Accessions</h2>
            <p class="text-xs text-content-body mt-0.5">Manage individual item barcodes, physical condition, and asset status.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full xl:w-auto">
            <!-- Search Input -->
            <div class="relative w-full sm:w-56">
                <label for="accession-search" class="sr-only">Search accessions</label>
                <input
                    id="accession-search"
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search accession # or title..."
                    class="w-full pl-9 pr-8 py-2 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                >
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
                @if(!empty($search))
                    <button
                        wire:click="$set('search', '')"
                        type="button"
                        class="absolute right-2.5 top-2 text-content-muted hover:text-content-heading text-xs cursor-pointer rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                        aria-label="Clear search"
                    >
                        ✕
                    </button>
                @endif
            </div>

            <!-- Status Filter Dropdown -->
            <div>
                <label for="accession-status-filter" class="sr-only">Filter by status</label>
                <select
                    id="accession-status-filter"
                    wire:model.live="statusFilter"
                    class="w-full sm:w-auto text-xs bg-surface-canvas text-content-heading border border-surface-border rounded-xl px-3 py-2 outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                >
                    <option value="">All Statuses</option>
                    <option value="available">Available</option>
                    <option value="on loan">On Loan</option>
                    <option value="stored">Stored</option>
                    <option value="under maintenance">Maintenance</option>
                    <option value="dumped">Dumped</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <!-- Export Dropdown -->
                <div
                    x-data="{ open: false }"
                    @click.outside="open = false"
                    @keydown.escape.window="open = false"
                    class="relative"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                        wire:loading.attr="disabled"
                        wire:target="exportExcel,exportPdf,exportAccessionNumbers"
                        class="px-3 py-2 text-xs font-semibold text-content-heading bg-surface-card border border-surface-border rounded-xl hover:bg-surface-canvas transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary disabled:opacity-50"
                    >
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-content-muted" aria-hidden="true" />
                        <span>Export</span>
                        <x-heroicon-m-chevron-down class="w-3.5 h-3.5 text-content-muted" aria-hidden="true" />
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        role="menu"
                        class="absolute right-0 mt-1.5 w-48 rounded-xl border border-surface-border bg-surface-card shadow-lg py-1 z-30 text-xs"
                    >
                        <button
                            type="button"
                            role="menuitem"
                            wire:click="exportExcel"
                            @click="open = false"
                            class="w-full px-3.5 py-2 text-left text-content-heading hover:bg-surface-canvas flex items-center gap-2 transition cursor-pointer"
                        >
                            <x-heroicon-o-table-cells class="w-4 h-4 text-content-muted shrink-0" aria-hidden="true" />
                            <span>Export Excel (.xlsx)</span>
                        </button>

                        <button
                            type="button"
                            role="menuitem"
                            wire:click="exportPdf"
                            @click="open = false"
                            class="w-full px-3.5 py-2 text-left text-content-heading hover:bg-surface-canvas flex items-center gap-2 transition cursor-pointer"
                        >
                            <x-heroicon-o-document-arrow-down class="w-4 h-4 text-content-muted shrink-0" aria-hidden="true" />
                            <span>Export PDF (.pdf)</span>
                        </button>

                        <div class="my-1 border-t border-surface-border/70"></div>

                        <button
                            type="button"
                            role="menuitem"
                            wire:click="openExportModal"
                            @click="open = false"
                            class="w-full px-3.5 py-2 text-left text-content-heading hover:bg-surface-canvas flex items-center gap-2 transition cursor-pointer"
                        >
                            <x-heroicon-o-qr-code class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                            <span>Export Niimbot Labels</span>
                        </button>
                    </div>
                </div>

                <button
                    wire:click="openBulkUpdateModal"
                    wire:loading.attr="disabled"
                    wire:target="openBulkUpdateModal"
                    type="button"
                    class="px-3 py-2 text-xs font-semibold text-brand-primary bg-brand-tint-light border border-brand-primary/25 rounded-xl hover:bg-brand-primary/15 transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                >
                    <x-heroicon-o-arrow-path class="w-4 h-4 text-brand-primary" aria-hidden="true" />
                    <span>Bulk Update</span>
                </button>

                <button
                    wire:click="openCreateModal"
                    wire:loading.attr="disabled"
                    wire:target="openCreateModal"
                    type="button"
                    class="px-4 py-2 text-xs font-semibold text-white bg-brand-primary rounded-xl hover:bg-brand-primary-hover transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
                >
                    <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
                    <span>Receive Accessions</span>
                </button>
            </div>
        </div>
    </section>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-surface-border rounded-2xl shadow-2xs bg-surface-card">
        <table class="min-w-full text-left text-xs text-content-body">
            <thead class="bg-surface-canvas text-content-muted uppercase tracking-wider text-[11px] border-b border-surface-border">
                <tr>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Accession #</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">ISBN/ISSN</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Title</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Author</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Publisher</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Gen Reference</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Asset Type</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Call Number</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">ACQ Ref</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Vendor</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Condition</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 font-semibold text-right whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border/70 bg-surface-card">
                @forelse($accessions as $item)
                    <tr wire:key="accession-row-{{ $item->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                        <!-- 1. Accession # -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-mono font-bold text-brand-primary uppercase">
                            {{ $item->accession_number }}
                        </td>

                        <!-- 2. ISBN/ISSN -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-mono text-content-body">
                            {{ $item->acquisition->catalog->isbn_issn ?? 'N/A' }}
                        </td>

                        <!-- 3. Title -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-semibold text-content-heading">
                            {{ isset($item->acquisition->catalog->title) ? Str::title($item->acquisition->catalog->title) : '—' }}
                        </td>

                        <!-- 4. Author -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-content-heading">
                            {{ isset($item->acquisition->catalog->author->name) ? Str::title($item->acquisition->catalog->author->name) : 'N/A' }}
                        </td>

                        <!-- 5. Publisher -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-content-body">
                            {{ isset($item->acquisition->catalog->publisher->name) ? Str::title($item->acquisition->catalog->publisher->name) : 'N/A' }}
                        </td>

                        <!-- 6. Gen Reference -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-content-body">
                            {{ isset($item->acquisition->catalog->generalReference->name) ? Str::title($item->acquisition->catalog->generalReference->name) : 'N/A' }}
                        </td>

                        <!-- 7. Asset Type -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-content-body">
                            {{ isset($item->acquisition->catalog->assetType->name) ? Str::title($item->acquisition->catalog->assetType->name) : 'N/A' }}
                        </td>

                        <!-- 8. Call Number -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-mono text-brand-primary font-semibold uppercase">
                            {{ $item->acquisition->catalog->call_number ?? 'N/A' }}
                        </td>

                        <!-- 9. ACQ Ref -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-mono text-content-body uppercase">
                            {{ $item->acquisition->acquisition_number ?? '—' }}
                        </td>

                        <!-- 10. Vendor -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-content-body">
                            {{ isset($item->acquisition->vendor->company_name) ? Str::title($item->acquisition->vendor->company_name) : 'N/A' }}
                        </td>

                        <!-- 11. Condition -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full uppercase {{ match($item->condition) {
                                'new', 'good' => 'bg-brand-tint-light text-brand-primary',
                                'minor damage', 'severe damaged', 'lost', 'missing' => 'bg-brand-secondary/15 text-brand-secondary',
                                default => 'bg-surface-canvas text-content-heading border border-surface-border'
                            } }}">
                                {{ Str::title($item->condition) }}
                            </span>
                        </td>

                        <!-- 12. Status -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full uppercase {{ match($item->status) {
                                'available', 'on loan' => 'bg-brand-tint-light text-brand-primary',
                                'under maintenance', 'dumped' => 'bg-brand-secondary/15 text-brand-secondary',
                                'stored' => 'bg-surface-canvas text-content-heading border border-surface-border',
                                default => 'bg-surface-canvas text-content-heading border border-surface-border'
                            } }}">
                                {{ Str::title($item->status) }}
                            </span>
                        </td>

                        <!-- 13. Actions -->
                        <td class="px-3 sm:px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <button
                                wire:click="openEditModal({{ $item->id }})"
                                type="button"
                                class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                            >Edit</button>
                            <button
                                wire:click="confirmDelete({{ $item->id }})"
                                type="button"
                                class="text-brand-secondary hover:bg-brand-secondary/10 font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                            >Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="px-4 py-10 text-center">
                            <p class="text-xs font-semibold text-content-heading">No accession items found</p>
                            <p class="text-[11px] text-content-muted mt-1">Try adjusting your search or status filter.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $accessions->links() }}
    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div
            wire:key="accession-create-modal"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            x-data
            @keydown.escape.window="$wire.set('showModal', false)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="accession-modal-title"
        >
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-2xl z-10 overflow-hidden my-auto flex flex-col max-h-[90dvh] border border-surface-border">
                <div class="bg-surface-canvas px-5 py-3.5 border-b border-surface-border flex justify-between items-center shrink-0">
                    <h3 id="accession-modal-title" class="text-xs sm:text-sm font-bold text-content-heading">
                        {{ $accessionIdBeingEdited ? 'Edit Accession Item' : 'Receive Accession Batch' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-xl font-bold cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="saveAccession" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div>
                        <label for="accession-acquisition-select" class="block text-xs font-semibold text-content-heading mb-1">
                            Purchase Order / Acquisition <span class="text-brand-secondary">*</span>
                        </label>
                        @if ($accessionIdBeingEdited)
                            <input
                                id="accession-acquisition-select"
                                type="text"
                                disabled
                                readonly
                                value="{{ $this->selectedAcquisition ? strtoupper($this->selectedAcquisition->acquisition_number) . ' - ' . Str::title($this->selectedAcquisition->catalog->title ?? '') : '' }}"
                                class="w-full text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-muted cursor-not-allowed"
                            >
                        @else
                            <select
                                id="accession-acquisition-select"
                                wire:model.live="acquisition_id"
                                class="w-full text-xs rounded-xl border p-2.5 bg-surface-canvas text-content-heading shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition @error('acquisition_id') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                                <option value="">-- Choose Arrived Acquisition Record --</option>
                                @foreach($acquisitions as $acq)
                                    <option value="{{ $acq->id }}">
                                        {{ strtoupper($acq->acquisition_number) }} (Txn: {{ strtoupper($acq->transaction_number) }}) — {{ Str::title($acq->catalog->title ?? 'N/A') }} [Received Qty: {{ $acq->quantity_received }}]
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('acquisition_id') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    @if ($this->selectedAcquisition)
                        <div class="p-3.5 bg-brand-tint-light/70 border border-brand-primary/25 rounded-xl text-xs space-y-2">
                            <div class="font-bold text-content-heading border-b border-brand-primary/20 pb-1.5 flex flex-wrap justify-between items-center gap-2">
                                <span>Complete Acquisition &amp; Catalog Summary</span>
                                <span class="text-[10px] font-semibold text-brand-primary bg-surface-card px-2 py-0.5 rounded-md border border-brand-primary/20 uppercase font-mono">
                                    Remaining Unaccessioned: {{ $this->getRemainingQty() }} / Received: {{ $this->selectedAcquisition->quantity_received }}
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-content-body text-[11px] pt-0.5">
                                <div><strong class="text-content-heading">ACQ Reference:</strong> <span class="font-mono uppercase">{{ $this->selectedAcquisition->acquisition_number }}</span></div>
                                <div><strong class="text-content-heading">Transaction #:</strong> <span class="font-mono uppercase">{{ $this->selectedAcquisition->transaction_number }}</span></div>
                                <div><strong class="text-content-heading">Call Number:</strong> <span class="font-mono text-brand-primary font-bold uppercase">{{ strtoupper($this->selectedAcquisition->catalog->call_number ?? 'N/A') }}</span></div>
                                <div><strong class="text-content-heading">ISBN/ISSN:</strong> <span class="font-mono">{{ $this->selectedAcquisition->catalog->isbn_issn ?? 'N/A' }}</span></div>
                                <div><strong class="text-content-heading">Title:</strong> {{ isset($this->selectedAcquisition->catalog->title) ? Str::title($this->selectedAcquisition->catalog->title) : 'N/A' }}</div>
                                <div><strong class="text-content-heading">Author:</strong> {{ isset($this->selectedAcquisition->catalog->author->name) ? Str::title($this->selectedAcquisition->catalog->author->name) : 'N/A' }}</div>
                                <div><strong class="text-content-heading">Publisher:</strong> {{ isset($this->selectedAcquisition->catalog->publisher->name) ? Str::title($this->selectedAcquisition->catalog->publisher->name) : 'N/A' }}</div>
                                <div><strong class="text-content-heading">General Reference:</strong> {{ isset($this->selectedAcquisition->catalog->generalReference->name) ? Str::title($this->selectedAcquisition->catalog->generalReference->name) : 'N/A' }}</div>
                                <div><strong class="text-content-heading">Asset Type:</strong> {{ isset($this->selectedAcquisition->catalog->assetType->name) ? Str::title($this->selectedAcquisition->catalog->assetType->name) : 'N/A' }}</div>
                                <div><strong class="text-content-heading">Vendor / Supplier:</strong> {{ isset($this->selectedAcquisition->vendor->company_name) ? Str::title($this->selectedAcquisition->vendor->company_name) : 'N/A' }}</div>
                                <div><strong class="text-content-heading">Unit Cost:</strong> <span class="font-mono">₱{{ number_format($this->selectedAcquisition->unit_cost ?? 0, 2) }}</span></div>
                            </div>
                        </div>
                    @endif

                    @if ($accessionIdBeingEdited)
                        <div>
                            <label for="accession-number-input" class="block text-xs font-semibold text-content-heading mb-1">
                                Accession Number (Barcode) <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="accession-number-input"
                                type="text"
                                wire:model="accession_number"
                                class="w-full text-xs font-mono rounded-xl border p-2.5 bg-surface-canvas text-content-heading shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 uppercase transition @error('accession_number') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                            @error('accession_number') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="p-3 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-body">
                            <span class="font-semibold text-content-heading">Automatic Generation:</span> Selecting an acquisition will automatically generate sequential copy barcodes in the format <code class="font-mono text-brand-primary">ACC-{year}-{batch}-{Copy #}</code> for all remaining unaccessioned received items.
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="accession-condition-select" class="block text-xs font-semibold text-content-heading mb-1">
                                Condition <span class="text-brand-secondary">*</span>
                            </label>
                            <select
                                id="accession-condition-select"
                                wire:model="condition"
                                class="w-full text-xs rounded-xl border p-2.5 shadow-2xs bg-surface-canvas text-content-heading outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition @error('condition') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="minor damaged">Minor Damage</option>
                                <option value="severe damaged">Severe Damaged</option>
                                <option value="lost">Lost</option>
                                <option value="missing">Missing</option>
                            </select>
                            @error('condition') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="accession-status-select" class="block text-xs font-semibold text-content-heading mb-1">
                                Status <span class="text-brand-secondary">*</span>
                            </label>
                            <select
                                id="accession-status-select"
                                wire:model="status"
                                class="w-full text-xs rounded-xl border p-2.5 shadow-2xs bg-surface-canvas text-content-heading outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition @error('status') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                                <option value="available">Available</option>
                                <option value="on loan">On Loan</option>
                                <option value="stored">Stored</option>
                                <option value="under maintenance">Under Maintenance</option>
                                <option value="dumped">Dumped</option>
                            </select>
                            @error('status') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="accession-remarks-input" class="block text-xs font-semibold text-content-heading mb-1">Remarks</label>
                        <textarea
                            id="accession-remarks-input"
                            wire:model="remarks"
                            rows="2"
                            class="w-full text-xs rounded-xl border p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition @error('remarks') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            placeholder="Additional notes..."
                        ></textarea>
                        @error('remarks') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-surface-border/70 flex flex-col-reverse sm:flex-row justify-end gap-2">
                        <button
                            wire:click="$set('showModal', false)"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveAccession"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="saveAccession">{{ $accessionIdBeingEdited ? 'Save Changes' : 'Generate Accessions' }}</span>
                            <span wire:loading wire:target="saveAccession">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Bulk Update Status Modal with Multi-Select Checkboxes --}}
    @if ($showBulkUpdateModal)
        <div
            wire:key="accession-bulk-modal"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            x-data
            @keydown.escape.window="$wire.set('showBulkUpdateModal', false)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="bulk-update-modal-title"
        >
            <div wire:click="$set('showBulkUpdateModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-lg z-10 overflow-hidden my-auto flex flex-col max-h-[90dvh] border border-surface-border">
                <div class="bg-surface-canvas px-5 py-3.5 border-b border-surface-border flex justify-between items-center shrink-0">
                    <h3 id="bulk-update-modal-title" class="text-xs sm:text-sm font-bold text-content-heading">Bulk Update Accession Status by Acquisition Batch</h3>
                    <button
                        wire:click="$set('showBulkUpdateModal', false)"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-xl font-bold cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="bulkUpdateAccessions" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div>
                        <label for="bulk-acquisition-select" class="block text-xs font-semibold text-content-heading mb-1">
                            Select Accessioned Purchase Order / Acquisition <span class="text-brand-secondary">*</span>
                        </label>
                        <select
                            id="bulk-acquisition-select"
                            wire:model.live="bulkAcquisitionId"
                            class="w-full text-xs rounded-xl border p-2.5 bg-surface-canvas text-content-heading shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition @error('bulkAcquisitionId') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                        >
                            <option value="">-- Choose Accessioned Acquisition Batch --</option>
                            @foreach($accessionedAcquisitions as $acq)
                                <option value="{{ $acq->id }}">
                                    {{ strtoupper($acq->acquisition_number) }} — {{ Str::title($acq->catalog->title ?? 'N/A') }} [{{ $acq->accessions->count() }} Accessioned Items]
                                </option>
                            @endforeach
                        </select>
                        @error('bulkAcquisitionId') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    {{-- Multi-Select Accession Items Checkbox List --}}
                    @if ($this->bulkAcquisitionId)
                        <div class="space-y-1.5" wire:key="bulk-items-container-{{ $this->bulkAcquisitionId }}">
                            <div class="flex flex-wrap justify-between items-center gap-2">
                                <span class="block text-xs font-semibold text-content-heading">
                                    Select Items to Update (<span class="font-bold text-brand-primary">{{ count($selectedAccessionIds) }}</span> of {{ $this->bulkSelectedAccessions->count() }} selected):
                                </span>
                                <div class="space-x-2">
                                    <button
                                        type="button"
                                        wire:click="selectAllBulkItems"
                                        class="text-[11px] font-semibold text-brand-primary hover:underline cursor-pointer"
                                    >
                                        Select All
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deselectAllBulkItems"
                                        class="text-[11px] font-semibold text-content-muted hover:text-content-heading cursor-pointer"
                                    >
                                        Deselect All
                                    </button>
                                </div>
                            </div>

                            <div class="max-h-44 overflow-y-auto border border-surface-border rounded-xl p-2 bg-surface-canvas text-[11px] font-mono space-y-1.5">
                                @forelse ($this->bulkSelectedAccessions as $accItem)
                                    <label wire:key="bulk-checkbox-row-{{ $accItem->id }}" class="flex items-center justify-between gap-2 bg-surface-card px-2.5 py-1.5 rounded-lg border border-surface-border shadow-2xs cursor-pointer hover:bg-brand-tint-light/50 transition">
                                        <div class="flex items-center space-x-2">
                                            <input
                                                type="checkbox"
                                                value="{{ (string) $accItem->id }}"
                                                wire:model.live="selectedAccessionIds"
                                                @if($accItem->status === 'on loan') disabled @endif
                                                class="rounded border-surface-border text-brand-primary focus:ring-brand-primary w-3.5 h-3.5"
                                            >
                                            <span class="font-bold text-brand-primary uppercase">{{ $accItem->accession_number }}</span>
                                        </div>
                                        <div class="space-x-2 text-[10px] text-content-muted uppercase">
                                            <span>Cond: <strong class="text-content-heading">{{ $accItem->condition }}</strong></span>
                                            <span>Status: <strong class="{{ $accItem->status === 'on loan' ? 'text-brand-secondary' : 'text-content-heading' }}">{{ $accItem->status }}</strong></span>
                                        </div>
                                    </label>
                                @empty
                                    <div class="text-center text-content-muted py-2 font-sans text-xs">No accession items found for this batch.</div>
                                @endforelse
                            </div>
                            @error('selectedAccessionIds') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="bulk-condition-select" class="block text-xs font-semibold text-content-heading mb-1">
                                New Condition <span class="text-brand-secondary">*</span>
                            </label>
                            <select
                                id="bulk-condition-select"
                                wire:model.live="bulkCondition"
                                class="w-full text-xs rounded-xl border p-2.5 shadow-2xs bg-surface-canvas text-content-heading outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition @error('bulkCondition') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="minor damaged">Minor Damage</option>
                                <option value="severe damaged">Severe Damaged</option>
                                <option value="lost">Lost</option>
                                <option value="missing">Missing</option>
                            </select>
                            @error('bulkCondition') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="bulk-status-select" class="block text-xs font-semibold text-content-heading mb-1">
                                New Status <span class="text-brand-secondary">*</span>
                            </label>
                            <select
                                id="bulk-status-select"
                                wire:model.live="bulkStatus"
                                class="w-full text-xs rounded-xl border p-2.5 shadow-2xs bg-surface-canvas text-content-heading outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition @error('bulkStatus') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                                <option value="available">Available</option>
                                <option value="on loan">On Loan</option>
                                <option value="stored">Stored</option>
                                <option value="under maintenance">Under Maintenance</option>
                                <option value="dumped">Dumped</option>
                            </select>
                            @error('bulkStatus') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="bulk-remarks-input" class="block text-xs font-semibold text-content-heading mb-1">Remarks (Appended to selected items)</label>
                        <textarea
                            id="bulk-remarks-input"
                            wire:model="bulkRemarks"
                            rows="2"
                            class="w-full text-xs rounded-xl border p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition @error('bulkRemarks') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            placeholder="Optional batch update remarks..."
                        ></textarea>
                        @error('bulkRemarks') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-surface-border/70 flex flex-col-reverse sm:flex-row justify-end gap-2">
                        <button
                            wire:click="$set('showBulkUpdateModal', false)"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="bulkUpdateAccessions"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="bulkUpdateAccessions">Apply Bulk Update</span>
                            <span wire:loading wire:target="bulkUpdateAccessions">Updating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Export Niimbot Labels Modal --}}
    @if ($showExportModal)
        <div
            wire:key="accession-export-modal"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            x-data
            @keydown.escape.window="$wire.set('showExportModal', false)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="export-labels-modal-title"
        >
            <div wire:click="$set('showExportModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-md z-10 overflow-hidden p-5 sm:p-6 space-y-4 border border-surface-border">
                <div class="flex justify-between items-center border-b border-surface-border pb-3">
                    <h3 id="export-labels-modal-title" class="text-sm font-bold text-content-heading">Export Niimbot Barcode Labels</h3>
                    <button
                        wire:click="$set('showExportModal', false)"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-xl font-bold cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <p class="text-xs text-content-body">Specify an optional accession range or leave blank to export all matching current filters.</p>

                <div class="space-y-3">
                    <div>
                        <label for="export-start-accession" class="block text-xs font-semibold text-content-heading mb-1">Start Accession Number (Optional)</label>
                        <input
                            id="export-start-accession"
                            type="text"
                            wire:model="exportStartAccession"
                            placeholder="e.g. ACC-2026-002-01"
                            class="w-full text-xs font-mono rounded-xl border p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 uppercase transition @error('exportStartAccession') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                        >
                        @error('exportStartAccession') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="export-end-accession" class="block text-xs font-semibold text-content-heading mb-1">End Accession Number (Optional)</label>
                        <input
                            id="export-end-accession"
                            type="text"
                            wire:model="exportEndAccession"
                            placeholder="e.g. ACC-2026-002-12"
                            class="w-full text-xs font-mono rounded-xl border p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted shadow-2xs outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 uppercase transition @error('exportEndAccession') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                        >
                        @error('exportEndAccession') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-3 border-t border-surface-border/70">
                    <button
                        wire:click="$set('showExportModal', false)"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="exportAccessionNumbers"
                        wire:loading.attr="disabled"
                        wire:target="exportAccessionNumbers"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="exportAccessionNumbers">Download Excel Labels</span>
                        <span wire:loading wire:target="exportAccessionNumbers">Exporting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div
            wire:key="accession-delete-modal"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            x-data
            @keydown.escape.window="$wire.set('showDeleteModal', false)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="accession-delete-modal-title"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-sm z-10 overflow-hidden p-5 sm:p-6 space-y-4 border border-surface-border text-center">
                <div class="w-12 h-12 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center justify-center mx-auto shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>

                <div>
                    <h3 id="accession-delete-modal-title" class="text-sm sm:text-base font-bold text-content-heading">Delete Accession Item</h3>
                    <p class="text-xs text-content-body mt-1">Are you sure you want to delete this accession item? This action cannot be undone.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="$set('showDeleteModal', false)"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteAccession"
                        wire:loading.attr="disabled"
                        wire:target="deleteAccession"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-secondary hover:opacity-90 rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="deleteAccession">Delete Item</span>
                        <span wire:loading wire:target="deleteAccession">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
