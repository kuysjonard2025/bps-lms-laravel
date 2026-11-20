<div class="space-y-4">
    {{-- Header Container --}}
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Accessions</h2>
            <p class="text-xs text-gray-500">Manage individual item barcodes, physical condition, and asset status.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
            <!-- Search Input -->
            <div class="relative w-full sm:w-56 lg:w-64">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search accession # or title..."
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

            <!-- Status Filter Dropdown -->
            <select wire:model.live="statusFilter" class="w-full sm:w-auto text-xs bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500 capitalize">
                <option value="">All Statuses</option>
                <option value="available">Available</option>
                <option value="on loan">On Loan</option>
                <option value="stored">Stored</option>
                <option value="under maintenance">Maintenance</option>
                <option value="dumped">Dumped</option>
            </select>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <button
                    wire:click="openBulkUpdateModal"
                    type="button"
                    class="px-3 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs"
                >
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Bulk Update Status</span>
                </button>

                <button
                    wire:click="openExportModal"
                    type="button"
                    class="px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs"
                >
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export Labels</span>
                </button>

                <button
                    wire:click="openCreateModal"
                    type="button"
                    class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Receive Accessions</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
        <table class="w-full text-left text-xs text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Accession #</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">ISBN/ISSN</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Title</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Author</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Publisher</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Gen Reference</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Asset Type</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Call Number</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">ACQ Ref</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Vendor</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Condition</th>
                    <th scope="col" class="px-3 sm:px-4 py-3 whitespace-nowrap">Status</th>
                    <th scope="col" class="px-3 sm:px-3 text-right whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($accessions as $item)
                    <tr wire:key="accession-row-{{ $item->id }}" class="hover:bg-gray-50/50 transition">
                        <!-- 1. Accession # -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-mono font-bold text-blue-600 uppercase">
                            {{ $item->accession_number }}
                        </td>

                        <!-- 2. ISBN/ISSN -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-mono">
                            {{ $item->acquisition->catalog->isbn_issn ?? 'N/A' }}
                        </td>

                        <!-- 3. Title -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap capitalize font-medium">
                            {{ $item->acquisition->catalog->title ?? '—' }}
                        </td>

                        <!-- 4. Author -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap capitalize">
                            {{ $item->acquisition->catalog->author->name ?? 'N/A' }}
                        </td>

                        <!-- 5. Publisher -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap capitalize">
                            {{ $item->acquisition->catalog->publisher->name ?? 'N/A' }}
                        </td>

                        <!-- 6. Gen Reference -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap capitalize">
                            {{ $item->acquisition->catalog->generalReference->name ?? 'N/A' }}
                        </td>

                        <!-- 7. Asset Type -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap capitalize">
                            {{ $item->acquisition->catalog->assetType->name ?? 'N/A' }}
                        </td>

                        <!-- 8. Call Number -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-mono text-indigo-600 font-semibold uppercase">
                            {{ $item->acquisition->catalog->call_number ?? 'N/A' }}
                        </td>

                        <!-- 9. ACQ Ref -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-mono text-gray-600 uppercase">
                            {{ $item->acquisition->acquisition_number ?? '—' }}
                        </td>

                        <!-- 10. Vendor -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap capitalize">
                            {{ $item->acquisition->vendor->company_name ?? 'N/A' }}
                        </td>

                        <!-- 11. Condition -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
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

                        <!-- 12. Status -->
                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
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

                        <!-- 14. Actions -->
                        <td class="px-3 sm:px-3 text-right whitespace-nowrap space-x-1">
                            <button wire:click="openEditModal({{ $item->id }})" type="button" class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded hover:bg-blue-50 transition cursor-pointer">Edit</button>
                            <button wire:click="confirmDelete({{ $item->id }})" type="button" class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded hover:bg-red-50 transition cursor-pointer">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="px-4 py-8 text-center text-gray-500">No accession items found.</td>
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
        <div wire:key="accession-create-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" x-data @keydown.escape.window="$wire.set('showModal', false)" role="dialog" aria-modal="true" aria-labelledby="accession-modal-title">
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden my-auto flex flex-col max-h-[90vh]">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 id="accession-modal-title" class="text-xs sm:text-sm font-bold text-gray-900">
                        {{ $accessionIdBeingEdited ? 'Edit Accession Item' : 'Receive Accession Batch' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveAccession" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Purchase Order / Acquisition <span class="text-red-500">*</span></label>
                        @if ($accessionIdBeingEdited)
                            <input type="text" disabled readonly value="{{ $this->selectedAcquisition ? strtoupper($this->selectedAcquisition->acquisition_number) . ' - ' . ($this->selectedAcquisition->catalog->title ?? '') : '' }}" class="w-full text-xs rounded-md border-gray-300 border p-2 bg-gray-100 text-gray-500 cursor-not-allowed uppercase">
                        @else
                            <select wire:model.live="acquisition_id" class="w-full text-xs rounded-md border-gray-300 border p-2 bg-white shadow-xs outline-none focus:border-blue-500 uppercase">
                                <option value="">-- Choose Arrived Acquisition Record --</option>
                                @foreach($acquisitions as $acq)
                                    <option value="{{ $acq->id }}">
                                        {{ strtoupper($acq->acquisition_number) }} (Txn: {{ strtoupper($acq->transaction_number) }}) — {{ $acq->catalog->title ?? 'N/A' }} [Received Qty: {{ $acq->quantity_received }}]
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('acquisition_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    @if ($this->selectedAcquisition)
                        <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-lg text-xs space-y-2">
                            <div class="font-bold text-blue-900 border-b border-blue-200 pb-1.5 flex justify-between items-center capitalize">
                                <span>Complete Acquisition & Catalog Summary</span>
                                <span class="text-[10px] font-semibold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded uppercase font-mono">
                                    Remaining Unaccessioned: {{ $this->getRemainingQty() }} / Received: {{ $this->selectedAcquisition->quantity_received }}
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-gray-700 text-[11px] pt-0.5 capitalize">
                                <div><strong class="text-gray-900">ACQ Reference:</strong> <span class="font-mono uppercase">{{ $this->selectedAcquisition->acquisition_number }}</span></div>
                                <div><strong class="text-gray-900">Transaction #:</strong> <span class="font-mono uppercase">{{ $this->selectedAcquisition->transaction_number }}</span></div>
                                <div><strong class="text-gray-900">Call Number:</strong> <span class="font-mono text-indigo-600 font-bold uppercase">{{ strtoupper($this->selectedAcquisition->catalog->call_number ?? 'N/A') }}</span></div>
                                <div><strong class="text-gray-900">ISBN/ISSN:</strong> <span class="font-mono">{{ $this->selectedAcquisition->catalog->isbn_issn ?? 'N/A' }}</span></div>
                                <div><strong class="text-gray-900">Title:</strong> {{ $this->selectedAcquisition->catalog->title ?? 'N/A' }}</div>
                                <div><strong class="text-gray-900">Author:</strong> {{ $this->selectedAcquisition->catalog->author->name ?? 'N/A' }}</div>
                                <div><strong class="text-gray-900">Publisher:</strong> {{ $this->selectedAcquisition->catalog->publisher->name ?? 'N/A' }}</div>
                                <div><strong class="text-gray-900">General Reference:</strong> {{ $this->selectedAcquisition->catalog->generalReference->name ?? 'N/A' }}</div>
                                <div><strong class="text-gray-900">Asset Type:</strong> {{ $this->selectedAcquisition->catalog->assetType->name ?? 'N/A' }}</div>
                                <div><strong class="text-gray-900">Vendor / Supplier:</strong> {{ $this->selectedAcquisition->vendor->company_name ?? 'N/A' }}</div>
                                <div><strong class="text-gray-900">Unit Cost:</strong> <span class="font-mono">₱{{ number_format($this->selectedAcquisition->unit_cost ?? 0, 2) }}</span></div>
                            </div>
                        </div>
                    @endif

                    @if ($accessionIdBeingEdited)
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Accession Number (Barcode) <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="accession_number" class="mt-1 w-full text-xs font-mono rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-blue-500 uppercase">
                            @error('accession_number') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-md text-xs text-gray-600">
                            <span class="font-semibold text-gray-900">Automatic Generation:</span> Selecting an acquisition will automatically generate sequential copy barcodes in the format <code class="font-mono text-blue-600">ACC-{year}-{batch}-{Copy #}</code> for all remaining unaccessioned received items.
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Condition <span class="text-red-500">*</span></label>
                            <select wire:model="condition" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white outline-none focus:border-blue-500 capitalize">
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="minor damage">Minor Damage</option>
                                <option value="severe damaged">Severe Damaged</option>
                                <option value="lost">Lost</option>
                                <option value="missing">Missing</option>
                            </select>
                            @error('condition') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                            <select wire:model="status" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white outline-none focus:border-blue-500 capitalize">
                                <option value="available">Available</option>
                                <option value="on loan">On Loan</option>
                                <option value="stored">Stored</option>
                                <option value="under maintenance">Under Maintenance</option>
                                <option value="dumped">Dumped</option>
                            </select>
                            @error('status') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">Remarks</label>
                        <textarea wire:model="remarks" rows="2" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-blue-500 capitalize-first" placeholder="Additional notes..."></textarea>
                        @error('remarks') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-gray-200 flex justify-end gap-2">
                        <button wire:click="$set('showModal', false)" type="button" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-xs transition cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove>{{ $accessionIdBeingEdited ? 'Save Changes' : 'Generate Accessions' }}</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Bulk Update Status Modal with Multi-Select Checkboxes --}}
    @if ($showBulkUpdateModal)
        <div wire:key="accession-bulk-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" x-data @keydown.escape.window="$wire.set('showBulkUpdateModal', false)" role="dialog" aria-modal="true">
            <div wire:click="$set('showBulkUpdateModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10 overflow-hidden my-auto flex flex-col max-h-[90vh]">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900">Bulk Update Accession Status by Acquisition Batch</h3>
                    <button wire:click="$set('showBulkUpdateModal', false)" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="bulkUpdateAccessions" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Select Accessioned Purchase Order / Acquisition <span class="text-red-500">*</span></label>
                        <select wire:model.live="bulkAcquisitionId" class="w-full text-xs rounded-md border-gray-300 border p-2 bg-white shadow-xs outline-none focus:border-indigo-500 uppercase">
                            <option value="">-- Choose Accessioned Acquisition Batch --</option>
                            @foreach($accessionedAcquisitions as $acq)
                                <option value="{{ $acq->id }}">
                                    {{ strtoupper($acq->acquisition_number) }} — {{ $acq->catalog->title ?? 'N/A' }} [{{ $acq->accessions->count() }} Accessioned Items]
                                </option>
                            @endforeach
                        </select>
                        @error('bulkAcquisitionId') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Multi-Select Accession Items Checkbox List --}}
                    @if ($this->bulkAcquisitionId)
                        <div class="space-y-1.5" wire:key="bulk-items-container-{{ $this->bulkAcquisitionId }}">
                            <div class="flex justify-between items-center">
                                <label class="block text-xs font-medium text-gray-700">Select Items to Update (<span class="font-bold text-indigo-600">{{ count($selectedAccessionIds) }}</span> of {{ $this->bulkSelectedAccessions->count() }} selected):</label>
                                <div class="space-x-2">
                                    <button
                                        type="button"
                                        wire:click="selectAllBulkItems"
                                        class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 cursor-pointer"
                                    >
                                        Select All
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deselectAllBulkItems"
                                        class="text-[11px] font-semibold text-gray-500 hover:text-gray-700 cursor-pointer"
                                    >
                                        Deselect All
                                    </button>
                                </div>
                            </div>

                            <div class="max-h-44 overflow-y-auto border border-gray-200 rounded-md p-2 bg-gray-50 text-[11px] font-mono space-y-1.5">
                                @forelse ($this->bulkSelectedAccessions as $accItem)
                                    <label wire:key="bulk-checkbox-row-{{ $accItem->id }}" class="flex items-center justify-between bg-white px-2.5 py-1.5 rounded border border-gray-200 shadow-2xs cursor-pointer hover:bg-indigo-50/50 transition">
                                        <div class="flex items-center space-x-2">
                                            <input
                                                type="checkbox"
                                                value="{{ (string) $accItem->id }}"
                                                wire:model.live="selectedAccessionIds"
                                                @if($accItem->status === 'on loan') disabled @endif
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5"
                                            >
                                            <span class="font-bold text-indigo-600 uppercase">{{ $accItem->accession_number }}</span>
                                        </div>
                                        <div class="space-x-2 text-[10px] text-gray-500 uppercase">
                                            <span>Cond: <strong>{{ $accItem->condition }}</strong></span>
                                            <span>Status: <strong class="{{ $accItem->status === 'on loan' ? 'text-red-600' : 'text-gray-800' }}">{{ $accItem->status }}</strong></span>
                                        </div>
                                    </label>
                                @empty
                                    <div class="text-center text-gray-400 py-2 font-sans text-xs">No accession items found for this batch.</div>
                                @endforelse
                            </div>
                            @error('selectedAccessionIds') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">New Condition <span class="text-red-500">*</span></label>
                            <select wire:model.live="bulkCondition" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white outline-none focus:border-indigo-500 capitalize">
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="minor damage">Minor Damage</option>
                                <option value="severe damaged">Severe Damaged</option>
                                <option value="lost">Lost</option>
                                <option value="missing">Missing</option>
                            </select>
                            @error('bulkCondition') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">New Status <span class="text-red-500">*</span></label>
                            <select wire:model.live="bulkStatus" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white outline-none focus:border-indigo-500 capitalize">
                                <option value="available">Available</option>
                                <option value="on loan">On Loan</option>
                                <option value="stored">Stored</option>
                                <option value="under maintenance">Under Maintenance</option>
                                <option value="dumped">Dumped</option>
                            </select>
                            @error('bulkStatus') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">Remarks (Appended to selected items)</label>
                        <textarea wire:model="bulkRemarks" rows="2" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-indigo-500 capitalize-first" placeholder="Optional batch update remarks..."></textarea>
                        @error('bulkRemarks') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-gray-200 flex justify-end gap-2">
                        <button wire:click="$set('showBulkUpdateModal', false)" type="button" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow-xs transition cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove>Apply Bulk Update</span>
                            <span wire:loading>Updating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Export Niimbot Labels Modal --}}
    @if ($showExportModal)
        <div wire:key="accession-export-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" x-data @keydown.escape.window="$wire.set('showExportModal', false)" role="dialog" aria-modal="true">
            <div wire:click="$set('showExportModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10 overflow-hidden p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900">Export Niimbot Barcode Labels</h3>
                <p class="text-xs text-gray-600">Specify an optional accession range or leave blank to export all matching current filters.</p>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Start Accession Number (Optional)</label>
                        <input type="text" wire:model="exportStartAccession" placeholder="e.g. ACC-2026-002-01" class="mt-1 w-full text-xs font-mono rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-blue-500 uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">End Accession Number (Optional)</label>
                        <input type="text" wire:model="exportEndAccession" placeholder="e.g. ACC-2026-002-12" class="mt-1 w-full text-xs font-mono rounded-md border-gray-300 border p-2 shadow-xs outline-none focus:border-blue-500 uppercase">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="$set('showExportModal', false)" type="button" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                        Cancel
                    </button>
                    <button wire:click="exportAccessionNumbers" type="button" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-xs transition cursor-pointer">
                        Download Excel Labels
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div wire:key="accession-delete-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" x-data @keydown.escape.window="$wire.set('showDeleteModal', false)" role="dialog" aria-modal="true">
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10 overflow-hidden p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900">Delete Accession Item</h3>
                <p class="text-xs text-gray-600">Are you sure you want to delete this accession item? This action cannot be undone.</p>

                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                        Cancel
                    </button>
                    <button wire:click="deleteAccession" type="button" class="px-4 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md shadow-xs transition cursor-pointer">
                        Delete Item
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
