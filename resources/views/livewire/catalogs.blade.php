<div class="p-4 sm:p-6 space-y-4 max-w-7xl mx-auto">
    {{-- Header & Actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Catalogs Management</h2>
            <p class="text-xs text-gray-500">Master bibliographic entries linked to authors, publishers, asset types, and references.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
            {{-- Search Bar with Instant Loading State --}}
            <div class="relative w-full sm:w-64 lg:w-80">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    id="catalog-search"
                    placeholder="Search title, ISBN, author..."
                    class="w-full pl-9 pr-9 py-2 text-xs sm:text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
                {{-- Search Icon --}}
                <svg wire:loading.remove wire:target="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{-- Searching Spinner --}}
                <svg wire:loading wire:target="search" class="animate-spin w-4 h-4 text-blue-600 absolute left-3 top-2.5 sm:top-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <button
                wire:click="openCreateModal"
                type="button"
                class="w-full sm:w-auto px-4 py-2.5 text-xs sm:text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:bg-blue-800 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 touch-manipulation"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Catalog</span>
            </button>
        </div>
    </div>

    {{-- Data Table Container --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-left text-xs sm:text-sm text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px]">
                <tr>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">Title</th>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">Author</th>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap hidden md:table-cell">Asset Type</th>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap hidden lg:table-cell">Publisher</th>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap hidden lg:table-cell">Reference</th>
                    <th scope="col" class="px-4 py-3 font-semibold whitespace-nowrap">ISBN / Year</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($catalogs as $catalog)
                    <tr wire:key="catalog-row-{{ $catalog->id }}" class="hover:bg-gray-50/75 transition-colors">
                        <td class="px-4 py-3 font-semibold text-gray-900 max-w-[180px] sm:max-w-xs truncate">
                            <div class="truncate" title="{{ $catalog->title }}">{{ $catalog->title }}</div>
                            @if($catalog->edition)
                                <div class="text-[11px] font-normal text-gray-400">{{ $catalog->edition }} Edition</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $catalog->author->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap hidden md:table-cell">{{ $catalog->assetType->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap hidden lg:table-cell">{{ $catalog->publisher->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap hidden lg:table-cell">{{ $catalog->generalReference->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            <div>{{ $catalog->isbn_issn ?: '—' }}</div>
                            <div class="text-[11px] text-gray-400">{{ $catalog->publication_year }}</div>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <button
                                wire:click="openEditModal({{ $catalog->id }})"
                                type="button"
                                class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded-md hover:bg-blue-50 transition cursor-pointer"
                            >
                                Edit
                            </button>
                            <button
                                wire:click="confirmDelete({{ $catalog->id }})"
                                type="button"
                                class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded-md hover:bg-red-50 transition cursor-pointer"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            No catalogs found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    @if(method_exists($catalogs, 'links'))
        <div class="pt-2">
            {{ $catalogs->links() }}
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div
            x-data
            @keydown.escape.window="$wire.set('showModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl z-10 max-h-[90vh] flex flex-col my-auto overflow-hidden">
                <div class="bg-gray-50 px-5 sm:px-6 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-sm font-bold text-gray-900">
                        {{ $catalogIdBeingEdited ? 'Edit Catalog' : 'Add New Catalog' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer p-1">&times;</button>
                </div>

                <form wire:submit="saveCatalog" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    <div>
                        <label for="catalog-title" class="block text-xs font-medium text-gray-700">Title *</label>
                        <input
                            id="catalog-title"
                            type="text"
                            wire:model="title"
                            class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('title') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label for="catalog-author" class="block text-xs font-medium text-gray-700">Author *</label>
                            <select
                                id="catalog-author"
                                wire:model="author_id"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select Author</option>
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}">{{ $author->name }}</option>
                                @endforeach
                            </select>
                            @error('author_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="catalog-asset-type" class="block text-xs font-medium text-gray-700">Asset Type *</label>
                            <select
                                id="catalog-asset-type"
                                wire:model="asset_type_id"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select Asset Type</option>
                                @foreach($assetTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('asset_type_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label for="catalog-publisher" class="block text-xs font-medium text-gray-700">Publisher *</label>
                            <select
                                id="catalog-publisher"
                                wire:model="publisher_id"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select Publisher</option>
                                @foreach($publishers as $publisher)
                                    <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                                @endforeach
                            </select>
                            @error('publisher_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="catalog-reference" class="block text-xs font-medium text-gray-700">General Reference *</label>
                            <select
                                id="catalog-reference"
                                wire:model="general_reference_id"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Select General Reference</option>
                                @foreach($generalReferences as $ref)
                                    <option value="{{ $ref->id }}">{{ $ref->name }}</option>
                                @endforeach
                            </select>
                            @error('general_reference_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label for="catalog-isbn" class="block text-xs font-medium text-gray-700">ISBN / ISSN</label>
                            <input
                                id="catalog-isbn"
                                type="text"
                                wire:model="isbn_issn"
                                maxlength="20"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('isbn_issn') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="catalog-edition" class="block text-xs font-medium text-gray-700">Edition</label>
                            <input
                                id="catalog-edition"
                                type="text"
                                wire:model="edition"
                                maxlength="20"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('edition') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2 lg:col-span-1">
                            <label for="catalog-year" class="block text-xs font-medium text-gray-700">Publication Year *</label>
                            <input
                                id="catalog-year"
                                type="number"
                                wire:model="publication_year"
                                class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('publication_year') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="catalog-description" class="block text-xs font-medium text-gray-700">Description</label>
                        <textarea
                            id="catalog-description"
                            wire:model="description"
                            rows="3"
                            class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                        @error('description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
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
                            <span wire:loading wire:target="saveCatalog" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                            <span>{{ $catalogIdBeingEdited ? 'Save Changes' : 'Create Catalog' }}</span>
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
                    <h3 class="text-base font-bold text-gray-900">Delete Catalog Entry</h3>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Are you sure you want to delete this catalog record? This action cannot be undone.</p>
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
                        wire:click="deleteCatalog"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs sm:text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                        <span wire:loading wire:target="deleteCatalog" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
