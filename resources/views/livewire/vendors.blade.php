<div class="space-y-6 bg-white text-slate-900 min-h-full p-4 md:p-6" x-data="{
    init() {
        window.addEventListener('toast', event => {
            console.log(event.detail.message);
        });
    }
}">

    {{-- Top Bar & Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Vendors & Donors</h1>
            <p class="text-xs text-slate-500 mt-1">Manage library suppliers and item donors, contact personnel, and communication details.</p>
        </div>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add New Vendor/Donor
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
                placeholder="Search type, name, contact, phone, email..."
                class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 text-slate-900 placeholder-slate-400 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition"
            />
        </div>
    </div>

    {{-- Vendors Table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden relative">
        <div wire:loading.flex class="absolute inset-0 bg-white/50 backdrop-blur-[1px] items-center justify-center z-10">
            <div class="text-xs font-semibold text-slate-600 flex items-center gap-2">
                <svg class="animate-spin w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading...
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="p-3">Type</th>
                        <th class="p-3">Company / Organization</th>
                        <th class="p-3">Contact Person</th>
                        <th class="p-3">Address</th>
                        <th class="p-3">Contact #</th>
                        <th class="p-3">Email</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($vendors as $vendor)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full uppercase
                                    {{ $vendor->type === 'supplier' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $vendor->type }}
                                </span>
                            </td>
                            <td class="p-3 font-semibold text-slate-900 whitespace-nowrap uppercase">
                                {{ $vendor->company_name }}
                            </td>
                            <td class="p-3 text-slate-700 font-medium whitespace-nowrap capitalize">
                                {{ $vendor->contact_full_name }}
                            </td>
                            <td class="p-3 text-slate-600 max-w-xs truncate capitalize" title="{{ $vendor->company_address }}">
                                {{ $vendor->company_address ?: '—' }}
                            </td>
                            <td class="p-3 text-slate-600 whitespace-nowrap">
                                <div class="font-mono text-[11px] text-slate-700">{{ $vendor->contact_number ?: '—' }}</div>
                            </td>
                            <td class="p-3 text-slate-600 whitespace-nowrap">
                                <div class="text-[11px] text-slate-500 font-sans">{{ $vendor->contact_email ?: '—' }}</div>
                            </td>
                            <td class="p-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button
                                        wire:click="openEditModal({{ $vendor->id }})"
                                        class="px-2 py-0.5 font-bold text-blue-700 hover:text-blue-800 hover:bg-blue-100 rounded-md transition"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $vendor->id }})"
                                        class="px-2 py-0.5 font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-100 rounded-md transition"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 bg-white">
                                No vendors or donors found matching your search.
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
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-xl overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-white">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $vendorIdBeingEdited ? 'Edit Vendor / Donor Profile' : 'Add New Vendor / Donor' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveVendor" class="p-6 space-y-4 bg-white">
                    <div>
                        <label for="type" class="block text-xs font-semibold text-slate-700">Record Type *</label>
                        <select
                            id="type"
                            wire:model.live="type"
                            class="mt-1 w-full px-3 py-2 text-xs bg-white text-slate-900 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 capitalize"
                        >
                            <option value="supplier">Supplier (Sells items to library)</option>
                            <option value="donor">Donor (Gives items to library)</option>
                        </select>
                        @error('type') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="company_name" class="block text-xs font-semibold text-slate-700">Company / Organization Name *</label>
                        <input
                            id="company_name"
                            wire:model="company_name"
                            type="text"
                            class="mt-1 w-full px-3 py-2 text-xs bg-white text-slate-900 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 uppercase"
                            placeholder="Acme Corporation or Individual Donor Name"
                        />
                        @error('company_name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="company_address" class="block text-xs font-semibold text-slate-700">
                            Address @if($type === 'supplier') * @else <span class="text-slate-400 font-normal">(Optional)</span> @endif
                        </label>
                        <textarea
                            id="company_address"
                            wire:model="company_address"
                            rows="2"
                            class="mt-1 w-full px-3 py-2 text-xs bg-white text-slate-900 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 capitalize"
                            placeholder="123 Business Rd, City..."
                        ></textarea>
                        @error('company_address') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label for="contact_first_name" class="block text-xs font-semibold text-slate-700">First Name *</label>
                            <input
                                id="contact_first_name"
                                wire:model="contact_first_name"
                                type="text"
                                class="mt-1 w-full px-3 py-2 text-xs bg-white text-slate-900 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 capitalize"
                                placeholder="John"
                            />
                            @error('contact_first_name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="contact_middle_name" class="block text-xs font-semibold text-slate-700">Middle Name</label>
                            <input
                                id="contact_middle_name"
                                wire:model="contact_middle_name"
                                type="text"
                                class="mt-1 w-full px-3 py-2 text-xs bg-white text-slate-900 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 capitalize"
                                placeholder="Optional"
                            />
                            @error('contact_middle_name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="contact_last_name" class="block text-xs font-semibold text-slate-700">Last Name *</label>
                            <input
                                id="contact_last_name"
                                wire:model="contact_last_name"
                                type="text"
                                class="mt-1 w-full px-3 py-2 text-xs bg-white text-slate-900 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 capitalize"
                                placeholder="Doe"
                            />
                            @error('contact_last_name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_number" class="block text-xs font-semibold text-slate-700">
                                Contact Number @if($type === 'supplier') * @else <span class="text-slate-400 font-normal">(Optional)</span> @endif
                            </label>
                            <input
                                id="contact_number"
                                wire:model="contact_number"
                                type="text"
                                class="mt-1 w-full px-3 py-2 text-xs bg-white text-slate-900 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900 font-mono"
                                placeholder="09123456789"
                            />
                            @error('contact_number') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="contact_email" class="block text-xs font-semibold text-slate-700">
                                Email Address @if($type === 'supplier') * @else <span class="text-slate-400 font-normal">(Optional)</span> @endif
                            </label>
                            <input
                                id="contact_email"
                                wire:model="contact_email"
                                type="email"
                                class="mt-1 w-full px-3 py-2 text-xs bg-white text-slate-900 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900"
                                placeholder="vendor@example.com"
                            />
                            @error('contact_email') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
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
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg shadow hover:bg-slate-800 transition disabled:opacity-50"
                        >
                            <span wire:loading.remove>Save Record</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4 border border-slate-200 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Delete Record?</h3>
                    <p class="text-xs text-slate-500 mt-1">Are you sure you want to delete this vendor or donor? This action cannot be undone.</p>
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
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 rounded-lg shadow hover:bg-rose-700 transition disabled:opacity-50"
                    >
                        <span wire:loading.remove>Delete Permanently</span>
                        <span wire:loading>Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
