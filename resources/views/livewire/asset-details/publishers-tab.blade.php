<div class="space-y-4">
    {{-- Top Action Bar: Search & Create --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative w-full sm:w-72 lg:w-80">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                id="publisher-search"
                placeholder="Search publishers..."
                class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            >
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <button
            wire:click="openCreateModal"
            type="button"
            class="w-full sm:w-auto px-4 py-2.5 text-xs sm:text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 touch-manipulation"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Publisher</span>
        </button>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-left text-xs sm:text-sm text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px]">
                <tr>
                    <th scope="col" class="px-4 py-3 font-semibold">Publisher Name</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Address</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($publishers as $publisher)
                    <tr wire:key="publisher-row-{{ $publisher->id }}" class="hover:bg-gray-50/75 transition-colors">
                        <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">
                            {{ $publisher->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-md truncate">
                            {{ $publisher->address }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <button
                                wire:click="openEditModal({{ $publisher->id }})"
                                type="button"
                                class="text-blue-600 hover:text-blue-800 font-semibold px-2.5 py-1.5 rounded-md hover:bg-blue-50 transition cursor-pointer"
                            >
                                Edit
                            </button>
                            <button
                                wire:click="confirmDelete({{ $publisher->id }})"
                                type="button"
                                class="text-red-600 hover:text-red-800 font-semibold px-2.5 py-1.5 rounded-md hover:bg-red-50 transition cursor-pointer"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                            No publishers found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    @if(method_exists($publishers, 'links'))
        <div class="pt-2">
            {{ $publishers->links() }}
        </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10 max-h-[90vh] flex flex-col my-auto overflow-hidden">
                <div class="bg-gray-50 px-5 sm:px-6 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-sm font-bold text-gray-900">
                        {{ $publisherIdBeingEdited ? 'Edit Publisher' : 'Add New Publisher' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" type="button" aria-label="Close modal" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer p-1">&times;</button>
                </div>

                <form wire:submit="savePublisher" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    <div>
                        <label for="publisher-name" class="block text-xs font-medium text-gray-700">Publisher Name *</label>
                        <input
                            id="publisher-name"
                            type="text"
                            wire:model="name"
                            maxlength="50"
                            class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="publisher-address" class="block text-xs font-medium text-gray-700">Address *</label>
                        <input
                            id="publisher-address"
                            type="text"
                            wire:model="address"
                            maxlength="100"
                            class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('address') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                        <button
                            wire:click="$set('showModal', false)"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
                        >
                            <span wire:loading wire:target="savePublisher" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                            <span>{{ $publisherIdBeingEdited ? 'Save Changes' : 'Create Publisher' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm z-10 p-5 sm:p-6 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900">Delete Publisher</h3>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Are you sure you want to delete this publisher? This action cannot be undone.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button
                        wire:click="$set('showDeleteModal', false)"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deletePublisher"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                        <span wire:loading wire:target="deletePublisher" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
