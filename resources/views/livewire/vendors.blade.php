<div class="space-y-6">

    {{-- Header & Quick Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Vendors Management</h1>
            <p class="text-xs text-slate-500 mt-1">Manage vendor details and contact information for acquisitions.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            {{-- Search Bar --}}
            <div class="relative w-full sm:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    id="vendor-search"
                    placeholder="Search vendors..."
                    class="w-full pl-9 pr-9 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"
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

            <button
                wire:click="openCreateModal"
                type="button"
                class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-xl transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 touch-manipulation"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Vendor</span>
            </button>
        </div>
    </div>

    {{-- Data Table Container --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs text-slate-600">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                        <th scope="col" class="p-3 pl-4 whitespace-nowrap">Company Name</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Contact Person</th>
                        <th scope="col" class="p-3 whitespace-nowrap hidden md:table-cell">Address</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Contact Details</th>
                        <th scope="col" class="p-3 pr-4 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vendors as $vendor)
                        <tr wire:key="vendor-row-{{ $vendor->id }}" class="hover:bg-slate-50/60 transition-colors">
                            <td class="p-3 pl-4 font-bold text-slate-900 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 font-bold flex items-center justify-center shrink-0 text-xs border border-slate-200/60">
                                        {{ strtoupper(substr($vendor->company_name, 0, 1)) }}
                                    </div>
                                    <span class="truncate">{{ $vendor->company_name }}</span>
                                </div>
                            </td>
                            <td class="p-3 text-slate-700 font-medium whitespace-nowrap">
                                {{ $vendor->contact_person ?: '—' }}
                            </td>
                            <td class="p-3 text-slate-500 max-w-xs truncate whitespace-nowrap hidden md:table-cell" title="{{ $vendor->address }}">
                                {{ $vendor->address ?: '—' }}
                            </td>
                            <td class="p-3 text-slate-600 whitespace-nowrap">
                                <div class="font-mono text-[11px]">{{ $vendor->contact_number ?: '—' }}</div>
                                <div class="text-[10px] text-slate-400 font-sans">{{ $vendor->email ?: '—' }}</div>
                            </td>
                            <td class="p-3 pr-4 text-right whitespace-nowrap space-x-1">
                                <button
                                    wire:click="openEditModal({{ $vendor->id }})"
                                    type="button"
                                    class="text-blue-600 hover:text-blue-800 font-bold px-2 py-1 rounded-lg hover:bg-blue-50 transition cursor-pointer"
                                >
                                    Edit
                                </button>
                                <button
                                    wire:click="confirmDelete({{ $vendor->id }})"
                                    type="button"
                                    class="text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-400">
                                No vendors found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination Links --}}
    @if(method_exists($vendors, 'links'))
        <div class="pt-2">
            {{ $vendors->links() }}
        </div>
    @endif

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div
            x-data
            @keydown.escape.window="$wire.set('showModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg z-10 max-h-[90vh] flex flex-col my-auto overflow-hidden border border-slate-100">
                <div class="bg-slate-50 px-5 sm:px-6 py-4 border-b border-slate-200/80 flex justify-between items-center shrink-0">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $vendorIdBeingEdited ? 'Edit Vendor' : 'Add New Vendor' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer p-1">&times;</button>
                </div>

                <form wire:submit="saveVendor" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label for="vendor-company-name" class="block text-xs font-semibold text-slate-700">Company Name *</label>
                            <input
                                id="vendor-company-name"
                                type="text"
                                wire:model="company_name"
                                maxlength="50"
                                class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"
                            >
                            @error('company_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="vendor-contact-person" class="block text-xs font-semibold text-slate-700">Contact Person</label>
                            <input
                                id="vendor-contact-person"
                                type="text"
                                wire:model="contact_person"
                                maxlength="100"
                                class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"
                            >
                            @error('contact_person') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="vendor-address" class="block text-xs font-semibold text-slate-700">Address *</label>
                        <input
                            id="vendor-address"
                            type="text"
                            wire:model="address"
                            maxlength="100"
                            class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"
                        >
                        @error('address') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label for="vendor-contact-number" class="block text-xs font-semibold text-slate-700">Contact Number</label>
                            <input
                                id="vendor-contact-number"
                                type="text"
                                wire:model="contact_number"
                                maxlength="20"
                                class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"
                            >
                            @error('contact_number') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="vendor-email" class="block text-xs font-semibold text-slate-700">Email Address</label>
                            <input
                                id="vendor-email"
                                type="email"
                                wire:model="email"
                                maxlength="50"
                                class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"
                            >
                            @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-2 border-t border-slate-100">
                        <button
                            wire:click="$set('showModal', false)"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 cursor-pointer transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition shadow-xs"
                        >
                            <span wire:loading wire:target="saveVendor" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                            <span>{{ $vendorIdBeingEdited ? 'Save Changes' : 'Create Vendor' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div
            x-data
            @keydown.escape.window="$wire.set('showDeleteModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm z-10 p-5 sm:p-6 text-center space-y-4 border border-slate-100">
                <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto shrink-0 border border-rose-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900">Delete Vendor</h3>
                    <p class="text-xs text-slate-500 mt-1">Are you sure you want to delete this vendor? This action cannot be undone.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button
                        wire:click="$set('showDeleteModal', false)"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 cursor-pointer transition"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteVendor"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition shadow-xs"
                    >
                        <span wire:loading wire:target="deleteVendor" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
