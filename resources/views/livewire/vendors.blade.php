<div class="p-6 space-y-4">
    {{-- Header & Top Action Bar --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Vendors Management</h2>
            <p class="text-xs text-gray-500">Manage vendor details and contact information for acquisitions.</p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search vendors..."
                    class="w-full pl-9 pr-4 py-2 text-xs lg:text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <button
                wire:click="openCreateModal"
                type="button"
                class="w-full sm:w-auto px-4 py-2 text-xs lg:text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Vendor</span>
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-left text-xs lg:text-sm text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-4 py-3">Company Name</th>
                    <th scope="col" class="px-4 py-3">Contact Person</th>
                    <th scope="col" class="px-4 py-3">Address</th>
                    <th scope="col" class="px-4 py-3">Contact Details</th>
                    <th scope="col" class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($vendors as $vendor)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">
                            {{ $vendor->company_name }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $vendor->contact_person ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">
                            {{ $vendor->address }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            <div>{{ $vendor->contact_number ?: '—' }}</div>
                            <div class="text-[11px] text-gray-400">{{ $vendor->email ?: '—' }}</div>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap gap-2">
                            <button
                                wire:click="openEditModal({{ $vendor->id }})"
                                class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded-md hover:bg-blue-50 transition cursor-pointer"
                            >
                                Edit
                            </button>
                            <button
                                wire:click="confirmDelete({{ $vendor->id }})"
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
    <div>
        {{ $vendors->links() }}
    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10 overflow-hidden my-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900">
                        {{ $vendorIdBeingEdited ? 'Edit Vendor' : 'Add New Vendor' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveVendor" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Company Name *</label>
                            <input type="text" wire:model="company_name" maxlength="50" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                            @error('company_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Contact Person</label>
                            <input type="text" wire:model="contact_person" maxlength="100" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                            @error('contact_person') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">Address *</label>
                        <input type="text" wire:model="address" maxlength="100" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                        @error('address') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Contact Number</label>
                            <input type="text" wire:model="contact_number" maxlength="20" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                            @error('contact_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Email Address</label>
                            <input type="email" wire:model="email" maxlength="50" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                            @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-3">
                        <button wire:click="$set('showModal', false)" type="button" class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 cursor-pointer">
                            {{ $vendorIdBeingEdited ? 'Save Changes' : 'Create Vendor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm z-10 p-6 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900">Delete Vendor</h3>
                    <p class="text-xs text-gray-500 mt-1">Are you sure you want to delete this vendor? This action cannot be undone.</p>
                </div>

                <div class="flex justify-center gap-3 pt-2">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                    <button wire:click="deleteVendor" type="button" class="px-4 py-2 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700 cursor-pointer">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
