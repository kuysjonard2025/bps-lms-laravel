<div class="space-y-6">

    {{-- Top Bar & Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Vendors</h1>
            <p class="text-xs text-slate-500 mt-1">Manage vendor profiles, contact personnel, and communication details.</p>
        </div>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg shadow hover:bg-slate-800 transition focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add New Vendor
        </button>
    </div>

    {{-- Search Filter & Actions --}}
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search vendor, contact, phone, email..."
                class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition"
            />
        </div>
    </div>

    {{-- Vendors Table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="p-3">Company Name</th>
                        <th class="p-3">Contact Person</th>
                        <th class="p-3">Address</th>
                        <th class="p-3">Contact Details</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($vendors as $vendor)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-3 font-semibold text-slate-900 whitespace-nowrap">
                                {{ ucwords($vendor->company_name) }}
                            </td>
                            <td class="p-3 text-slate-700 font-medium whitespace-nowrap">
                                {{ ucwords($vendor->contact_person) }}
                            </td>
                            <td class="p-3 text-slate-600 max-w-xs truncate" title="{{ $vendor->address }}">
                                {{ ucwords($vendor->address) }}
                            </td>
                            <td class="p-3 text-slate-600 whitespace-nowrap">
                                <div class="font-mono text-[11px]">{{ $vendor->contact_number }}</div>
                                <div class="text-[10px] text-slate-400 font-sans">{{ strtolower($vendor->email) }}</div>
                            </td>
                            <td class="p-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <button
                                        wire:click="openEditModal({{ $vendor->id }})"
                                        class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-md transition"
                                        title="Edit Vendor"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $vendor->id }})"
                                        class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-md transition"
                                        title="Delete Vendor"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">
                                No vendors found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vendors->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $vendorIdBeingEdited ? 'Edit Vendor Profile' : 'Add New Vendor' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveVendor" class="p-6 space-y-4">
                    <div>
                        <label for="vendor-company-name" class="block text-xs font-semibold text-slate-700">Company Name *</label>
                        <input
                            id="vendor-company-name"
                            wire:model="company_name"
                            type="text"
                            class="mt-1 w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900"
                            placeholder="Acme Corporation"
                        />
                        @error('company_name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="vendor-contact-person" class="block text-xs font-semibold text-slate-700">Contact Person *</label>
                        <input
                            id="vendor-contact-person"
                            wire:model="contact_person"
                            type="text"
                            class="mt-1 w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900"
                            placeholder="John Doe"
                        />
                        @error('contact_person') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="vendor-address" class="block text-xs font-semibold text-slate-700">Address *</label>
                        <textarea
                            id="vendor-address"
                            wire:model="address"
                            rows="2"
                            class="mt-1 w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900"
                            placeholder="123 Business Rd, Suite 100..."
                        ></textarea>
                        @error('address') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="vendor-contact-number" class="block text-xs font-semibold text-slate-700">Contact Number *</label>
                            <input
                                id="vendor-contact-number"
                                wire:model="contact_number"
                                type="text"
                                class="mt-1 w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900"
                                placeholder="+1 (555) 000-0000"
                            />
                            @error('contact_number') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="vendor-email" class="block text-xs font-semibold text-slate-700">Email Address *</label>
                            <input
                                id="vendor-email"
                                wire:model="email"
                                type="email"
                                class="mt-1 w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900"
                                placeholder="vendor@example.com"
                            />
                            @error('email') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="$set('showModal', false)"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg shadow hover:bg-slate-800 transition"
                        >
                            Save Vendor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4 border border-slate-200 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Delete Vendor Profile?</h3>
                    <p class="text-xs text-slate-500 mt-1">Are you sure you want to delete this vendor? This action cannot be undone.</p>
                </div>
                <div class="flex justify-center gap-3 pt-2">
                    <button
                        wire:click="$set('showDeleteModal', false)"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteVendor"
                        class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 rounded-lg shadow hover:bg-rose-700 transition"
                    >
                        Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
