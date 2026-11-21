{{-- resources/views/livewire/vendors.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div
    class="space-y-6"
    x-data="{
        init() {
            window.addEventListener('toast', event => {
                console.log(event.detail.message);
            });
        }
    }"
>

    {{-- Top Bar & Header --}}
    <section aria-label="Vendors and donors header" class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-content-heading">Vendors &amp; Donors</h1>
            <p class="text-xs text-content-body mt-0.5">Manage library suppliers and item donors, contact personnel, and communication details.</p>
        </div>
        <button
            wire:click="openCreateModal"
            wire:loading.attr="disabled"
            wire:target="openCreateModal"
            type="button"
            class="w-full md:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl shadow-2xs transition cursor-pointer shrink-0 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
        >
            <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
            <span>Add New Vendor/Donor</span>
        </button>
    </section>

    {{-- Search Filter & Actions --}}
    <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="relative w-full md:w-96">
            <label for="vendor-search" class="sr-only">Search vendors or donors</label>
            <input
                id="vendor-search"
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search type, name, contact, phone, email..."
                class="w-full pl-9 pr-4 py-2 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
            />
            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
        </div>
    </div>

    {{-- Vendors Table --}}
    <div class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs overflow-hidden relative">
        <div wire:loading.flex wire:target="search,openCreateModal,openEditModal,confirmDelete,deleteVendor" class="absolute inset-0 bg-surface-card/70 backdrop-blur-2xs items-center justify-center z-10">
            <div class="text-xs font-semibold text-content-body flex items-center gap-2">
                <svg class="animate-spin w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Loading...</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs text-content-body">
                <thead class="bg-surface-canvas border-b border-surface-border text-content-muted font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th scope="col" class="p-3 whitespace-nowrap">Type</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Company / Organization</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Contact Person</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Address</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Contact #</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Email</th>
                        <th scope="col" class="p-3 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border/70">
                    @forelse ($vendors as $vendor)
                        <tr wire:key="vendor-row-{{ $vendor->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                            <td class="p-3 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full uppercase {{ $vendor->type === 'supplier' ? 'bg-brand-tint-light text-brand-primary' : 'bg-surface-canvas text-content-heading border border-surface-border' }}">
                                    {{ $vendor->type }}
                                </span>
                            </td>
                            <td class="p-3 font-semibold text-content-heading whitespace-nowrap">
                                {{ Str::upper($vendor->company_name) }}
                            </td>
                            <td class="p-3 text-content-heading font-medium whitespace-nowrap">
                                {{ Str::title($vendor->contact_full_name) }}
                            </td>
                            <td class="p-3 text-content-body max-w-xs truncate" title="{{ $vendor->company_address ? Str::title($vendor->company_address) : '—' }}">
                                {{ $vendor->company_address ? Str::title($vendor->company_address) : '—' }}
                            </td>
                            <td class="p-3 text-content-body whitespace-nowrap">
                                <div class="font-mono text-[11px] text-content-heading">{{ $vendor->contact_number ?: '—' }}</div>
                            </td>
                            <td class="p-3 text-content-body whitespace-nowrap">
                                <div class="text-[11px] text-content-muted font-sans">{{ $vendor->contact_email ?: '—' }}</div>
                            </td>
                            <td class="p-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button
                                        wire:click="openEditModal({{ $vendor->id }})"
                                        type="button"
                                        class="px-2.5 py-1.5 font-semibold text-brand-primary hover:bg-brand-tint-light rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $vendor->id }})"
                                        type="button"
                                        class="px-2.5 py-1.5 font-semibold text-brand-secondary hover:bg-brand-secondary/10 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center">
                                <p class="text-xs font-semibold text-content-heading">No vendors or donors found</p>
                                <p class="text-[11px] text-content-muted mt-1">No vendors or donors found matching your search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vendors->hasPages())
            <div class="p-3 border-t border-surface-border bg-surface-canvas">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div
            x-data
            @keydown.escape.window="$wire.set('showModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="vendor-modal-title"
        >
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-xl max-h-[90dvh] flex flex-col my-auto overflow-hidden border border-surface-border z-10">
                <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-surface-border bg-surface-canvas shrink-0">
                    <h3 id="vendor-modal-title" class="text-sm font-bold text-content-heading">
                        {{ $vendorIdBeingEdited ? 'Edit Vendor / Donor Profile' : 'Add New Vendor / Donor' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-lg font-bold cursor-pointer p-1 rounded-lg transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="saveVendor" class="p-5 sm:p-6 space-y-4 bg-surface-card overflow-y-auto">
                    <div>
                        <label for="type" class="block text-xs font-semibold text-content-heading mb-1">
                            Record Type <span class="text-brand-secondary">*</span>
                        </label>
                        <select
                            id="type"
                            wire:model.live="type"
                            class="w-full px-3 py-2.5 text-xs bg-surface-canvas text-content-heading border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                        >
                            <option value="supplier">Supplier (Sells items to library)</option>
                            <option value="donor">Donor (Gives items to library)</option>
                        </select>
                        @error('type') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="company_name" class="block text-xs font-semibold text-content-heading mb-1">
                            Company / Organization Name <span class="text-brand-secondary">*</span>
                        </label>
                        <input
                            id="company_name"
                            wire:model="company_name"
                            type="text"
                            class="w-full px-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition uppercase"
                            placeholder="Acme Corporation or Individual Donor Name"
                        />
                        @error('company_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="company_address" class="block text-xs font-semibold text-content-heading mb-1">
                            Address @if($type === 'supplier') <span class="text-brand-secondary">*</span> @else <span class="text-content-muted font-normal">(Optional)</span> @endif
                        </label>
                        <textarea
                            id="company_address"
                            wire:model="company_address"
                            rows="2"
                            class="w-full px-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            placeholder="123 Business Rd, City..."
                        ></textarea>
                        @error('company_address') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label for="contact_first_name" class="block text-xs font-semibold text-content-heading mb-1">
                                First Name <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="contact_first_name"
                                wire:model="contact_first_name"
                                type="text"
                                class="w-full px-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                                placeholder="John"
                            />
                            @error('contact_first_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="contact_middle_name" class="block text-xs font-semibold text-content-heading mb-1">Middle Name</label>
                            <input
                                id="contact_middle_name"
                                wire:model="contact_middle_name"
                                type="text"
                                class="w-full px-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                                placeholder="Optional"
                            />
                            @error('contact_middle_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="contact_last_name" class="block text-xs font-semibold text-content-heading mb-1">
                                Last Name <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="contact_last_name"
                                wire:model="contact_last_name"
                                type="text"
                                class="w-full px-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                                placeholder="Doe"
                            />
                            @error('contact_last_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_number" class="block text-xs font-semibold text-content-heading mb-1">
                                Contact Number @if($type === 'supplier') <span class="text-brand-secondary">*</span> @else <span class="text-content-muted font-normal">(Optional)</span> @endif
                            </label>
                            <input
                                id="contact_number"
                                wire:model="contact_number"
                                type="text"
                                class="w-full px-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition font-mono"
                                placeholder="09123456789"
                            />
                            @error('contact_number') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="contact_email" class="block text-xs font-semibold text-content-heading mb-1">
                                Email Address @if($type === 'supplier') <span class="text-brand-secondary">*</span> @else <span class="text-content-muted font-normal">(Optional)</span> @endif
                            </label>
                            <input
                                id="contact_email"
                                wire:model="contact_email"
                                type="email"
                                class="w-full px-3 py-2.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                                placeholder="vendor@example.com"
                            />
                            @error('contact_email') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-surface-border/70">
                        <button
                            type="button"
                            wire:click="$set('showModal', false)"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveVendor"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="saveVendor">Save Record</span>
                            <span wire:loading wire:target="saveVendor">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div
            x-data
            @keydown.escape.window="$wire.set('showDeleteModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="vendor-delete-title"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-sm p-5 sm:p-6 space-y-4 border border-surface-border text-center z-10">
                <div class="w-12 h-12 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center justify-center mx-auto shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>
                <div>
                    <h3 id="vendor-delete-title" class="text-base font-bold text-content-heading">Delete Record?</h3>
                    <p class="text-xs text-content-body mt-1">Are you sure you want to delete this vendor or donor? This action cannot be undone.</p>
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
                        type="button"
                        wire:click="deleteVendor"
                        wire:loading.attr="disabled"
                        wire:target="deleteVendor"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-secondary hover:opacity-90 rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="deleteVendor">Delete Permanently</span>
                        <span wire:loading wire:target="deleteVendor">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
