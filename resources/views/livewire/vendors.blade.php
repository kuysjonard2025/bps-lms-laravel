<div class="p-4 sm:p-6 space-y-4 max-w-7xl mx-auto">
    {{-- Header & Top Action Bar --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Vendors Management</h2>
            <p class="text-xs text-gray-500">Manage vendor details and contact information for acquisitions.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            {{-- Search Bar --}}
            <div class="relative w-full sm:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    id="vendor-search"
                    placeholder="Search vendors..."
                    class="w-full pl-9 pr-9 py-2 text-xs lg:text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
                {{-- Search Icon --}}
                <svg wire:loading.remove wire:target="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 lg:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{-- Loading Spinner --}}
                <svg wire:loading wire:target="search" class="animate-spin w-4 h-4 text-blue-600 absolute left-3 top-2.5 lg:top-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <button
                wire:click="openCreateModal"
                type="button"
                class="w-full sm:w-auto px-4 py-2 text-xs lg:text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 touch-manipulation"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Vendor</span>
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
        <table class="w-full text-left text-xs lg:text-sm text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">Company Name</th>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">Contact Person</th>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap hidden md:table-cell">Address</th>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">Contact Details</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($vendors as $vendor)
                    <tr wire:key="vendor-row-{{ $vendor->id }}" class="hover:bg-gray-50/75 transition-colors">
                        <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">
                            {{ $vendor->company_name }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $vendor->contact_person ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate whitespace-nowrap hidden md:table-cell" title="{{ $vendor->address }}">
                            {{ $vendor->address }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            <div>{{ $vendor->contact_number ?: '—' }}</div>
                            <div class="text-[11px] text-gray-400">{{ $vendor->email ?: '—' }}</div>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <button
                                wire:click="openEditModal({{ $vendor->id }})"
                                type="button"
                                class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded-md hover:bg-blue-50 transition cursor-pointer"
                            >
                                Edit
                            </button>
                            <button
                                wire:click="confirmDelete({{ $vendor->id }})"
                                type="button"
                                class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded-md hover:bg-red-50 transition cursor-pointer"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            No vendors found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    @if(method_exists($vendors, 'links'))
        <div class="pt-1">
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
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10 overflow-hidden my-auto max-h-[90vh] flex flex-col">
                <div class="bg-gray-50 px-5 sm:px-6 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-sm font-bold text-gray-900">
                        {{ $vendorIdBeingEdited ? 'Edit Vendor' : 'Add New Vendor' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer p-1">&times;</button>
                </div>

                <form wire:submit="saveVendor" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="vendor-company-name" class="block text-xs font-medium text-gray-700">Company Name *</label>
                            <input
                                id="vendor-company-name"
                                type="text"
                                wire:model="company_name"
                                maxlength="50"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('company_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="vendor-contact-person" class="block text-xs font-medium text-gray-700">Contact Person</label>
                            <input
                                id="vendor-contact-person"
                                type="text"
                                wire:model="contact_person"
                                maxlength="100"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('contact_person') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="vendor-address" class="block text-xs font-medium text-gray-700">Address *</label>
                        <input
                            id="vendor-address"
                            type="text"
                            wire:model="address"
                            maxlength="100"
                            class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('address') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="vendor-contact-number" class="block text-xs font-medium text-gray-700">Contact Number</label>
                            <input
                                id="vendor-contact-number"
                                type="text"
                                wire:model="contact_number"
                                maxlength="20"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('contact_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="vendor-email" class="block text-xs font-medium text-gray-700">Email Address</label>
                            <input
                                id="vendor-email"
                                type="email"
                                wire:model="email"
                                maxlength="50"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                        <button
                            wire:click="$set('showModal', false)"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
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
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm z-10 p-5 sm:p-6 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900">Delete Vendor</h3>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Are you sure you want to delete this vendor? This action cannot be undone.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button
                        wire:click="$set('showDeleteModal', false)"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteVendor"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                        <span wire:loading wire:target="deleteVendor" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
