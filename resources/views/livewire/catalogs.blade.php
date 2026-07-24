<div class="p-6 space-y-4">
    {{-- Header & Actions --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Catalogs Management</h2>
            <p class="text-xs text-gray-500">Master bibliographic entries linked to authors, publishers, asset types, and general references.</p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search title, ISBN, author..."
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
                <span>Add Catalog</span>
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-left text-xs lg:text-sm text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-4 py-3">Title</th>
                    <th scope="col" class="px-4 py-3">Author</th>
                    <th scope="col" class="px-4 py-3">Asset Type</th>
                    <th scope="col" class="px-4 py-3">Publisher</th>
                    <th scope="col" class="px-4 py-3">Reference</th>
                    <th scope="col" class="px-4 py-3">ISBN / Year</th>
                    <th scope="col" class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($catalogs as $catalog)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">
                            <div>{{ $catalog->title }}</div>
                            @if($catalog->edition)
                                <div class="text-[11px] font-normal text-gray-400">{{ $catalog->edition }} Edition</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $catalog->author->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $catalog->assetType->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $catalog->publisher->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $catalog->generalReference->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            <div>{{ $catalog->isbn_issn ?: '—' }}</div>
                            <div class="text-[11px] text-gray-400">{{ $catalog->publication_year }}</div>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap gap-2">
                            <button wire:click="openEditModal({{ $catalog->id }})" class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded-md hover:bg-blue-50 transition cursor-pointer">Edit</button>
                            <button wire:click="confirmDelete({{ $catalog->id }})" class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded-md hover:bg-red-50 transition cursor-pointer">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No catalogs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $catalogs->links() }}</div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden my-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900">{{ $catalogIdBeingEdited ? 'Edit Catalog' : 'Add New Catalog' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveCatalog" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Title *</label>
                        <input type="text" wire:model="title" class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs">
                        @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Author *</label>
                            <select wire:model="author_id" class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="">Select Author</option>
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}">{{ $author->name }}</option>
                                @endforeach
                            </select>
                            @error('author_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Asset Type *</label>
                            <select wire:model="asset_type_id" class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="">Select Asset Type</option>
                                @foreach($assetTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('asset_type_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Publisher *</label>
                            <select wire:model="publisher_id" class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="">Select Publisher</option>
                                @foreach($publishers as $publisher)
                                    <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                                @endforeach
                            </select>
                            @error('publisher_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">General Reference *</label>
                            <select wire:model="general_reference_id" class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="">Select General Reference</option>
                                @foreach($generalReferences as $ref)
                                    <option value="{{ $ref->id }}">{{ $ref->name }}</option>
                                @endforeach
                            </select>
                            @error('general_reference_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">ISBN / ISSN</label>
                            <input type="text" wire:model="isbn_issn" maxlength="20" class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('isbn_issn') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Edition</label>
                            <input type="text" wire:model="edition" maxlength="20" class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('edition') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Publication Year *</label>
                            <input type="number" wire:model="publication_year" class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('publication_year') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">Description</label>
                        <textarea wire:model="description" rows="3" class="mt-1 w-full text-sm rounded-md border-gray-300 border p-2 shadow-xs"></textarea>
                        @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-5 flex justify-end gap-3">
                        <button wire:click="$set('showModal', false)" type="button" class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 cursor-pointer">{{ $catalogIdBeingEdited ? 'Save Changes' : 'Create Catalog' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm z-10 p-6 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Delete Catalog Entry</h3>
                    <p class="text-xs text-gray-500 mt-1">Are you sure you want to delete this catalog record?</p>
                </div>
                <div class="flex justify-center gap-3 pt-2">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                    <button wire:click="deleteCatalog" type="button" class="px-4 py-2 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700 cursor-pointer">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
