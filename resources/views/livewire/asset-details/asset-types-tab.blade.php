{{-- resources/views/livewire/asset-details/asset-types-tab.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-4">
    {{-- Header & Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative w-full sm:w-72 lg:w-80">
            <label for="asset-type-search" class="sr-only">Search asset types</label>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                id="asset-type-search"
                placeholder="Search asset types..."
                class="w-full pl-9 pr-8 py-2 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
            >
            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
            @if(!empty($search))
                <button
                    wire:click="$set('search', '')"
                    type="button"
                    class="absolute right-2.5 top-2 text-content-muted hover:text-content-heading text-xs cursor-pointer rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    aria-label="Clear search"
                >
                    &times;
                </button>
            @endif
        </div>

        <button
            wire:click="openCreateModal"
            wire:loading.attr="disabled"
            wire:target="openCreateModal"
            type="button"
            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary rounded-xl hover:bg-brand-primary-hover transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs shrink-0 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
        >
            <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
            <span>Add Asset Type</span>
        </button>
    </div>

    {{-- Data Table Container --}}
    <div class="overflow-x-auto border border-surface-border rounded-xl shadow-2xs bg-surface-card">
        <table class="min-w-full divide-y divide-surface-border/70 text-left text-xs text-content-body">
            <thead class="bg-surface-canvas text-content-muted uppercase tracking-wider text-[11px]">
                <tr>
                    <th scope="col" class="px-4 py-3 font-semibold">Asset Type Name</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border/70">
                @forelse($assetTypes as $assetType)
                    <tr wire:key="asset-type-row-{{ $assetType->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                        <td class="px-4 py-3 font-semibold text-content-heading whitespace-nowrap">
                            {{ Str::title($assetType->name) }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <button
                                wire:click="openEditModal({{ $assetType->id }})"
                                type="button"
                                class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                            >
                                Edit
                            </button>
                            <button
                                wire:click="confirmDelete({{ $assetType->id }})"
                                type="button"
                                class="text-brand-secondary hover:bg-brand-secondary/10 font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-10 text-center">
                            <p class="text-xs font-semibold text-content-heading">No asset types found</p>
                            <p class="text-[11px] text-content-muted mt-1">No asset types found matching your criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    @if(method_exists($assetTypes, 'links'))
        <div class="pt-2">
            {{ $assetTypes->links() }}
        </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if ($showModal)
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="asset-type-modal-title"
            @keydown.escape.window="$set('showModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        >
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl border border-surface-border shadow-xl w-full max-w-md z-10 max-h-[90dvh] flex flex-col my-auto overflow-hidden">
                <div class="bg-surface-canvas px-5 sm:px-6 py-3.5 border-b border-surface-border flex justify-between items-center shrink-0">
                    <h3 id="asset-type-modal-title" class="text-sm font-bold text-content-heading">
                        {{ $assetTypeIdBeingEdited ? 'Edit Asset Type' : 'Add New Asset Type' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-xl font-bold cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="saveAssetType" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    <div>
                        <label for="asset-type-name" class="block text-xs font-semibold text-content-heading mb-1">
                            Asset Type Name <span class="text-brand-secondary">*</span>
                        </label>
                        <input
                            id="asset-type-name"
                            type="text"
                            wire:model="name"
                            placeholder="e.g. Book, Newspaper"
                            class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('name') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                        >
                        @error('name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                        <button
                            wire:click="$set('showModal', false)"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border rounded-xl hover:text-content-heading cursor-pointer transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveAssetType"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary rounded-xl hover:bg-brand-primary-hover cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition-colors"
                        >
                            <span wire:loading wire:target="saveAssetType" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                            <span>{{ $assetTypeIdBeingEdited ? 'Save Changes' : 'Create Asset Type' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="asset-type-delete-title"
            @keydown.escape.window="$set('showDeleteModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl border border-surface-border shadow-xl w-full max-w-sm z-10 p-5 sm:p-6 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center justify-center mx-auto shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>

                <div>
                    <h3 id="asset-type-delete-title" class="text-base font-bold text-content-heading">Delete Asset Type</h3>
                    <p class="text-xs text-content-body mt-1">Are you sure you want to delete this asset type? This action cannot be undone.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button
                        wire:click="$set('showDeleteModal', false)"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border rounded-xl hover:text-content-heading cursor-pointer transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteAssetType"
                        wire:loading.attr="disabled"
                        wire:target="deleteAssetType"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-secondary rounded-xl hover:opacity-90 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition-opacity"
                    >
                        <span wire:loading wire:target="deleteAssetType" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
