{{-- resources/views/livewire/asset-details/general-references-tab.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-4">
    {{-- Top Action Bar: Search & Create --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative w-full sm:w-72 lg:w-80">
            <label for="general-reference-search" class="sr-only">Search general references</label>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                id="general-reference-search"
                placeholder="Search references..."
                class="w-full pl-9 pr-4 py-2 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
            >
            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
        </div>

        <button
            wire:click="openCreateModal"
            wire:loading.attr="disabled"
            wire:target="openCreateModal"
            type="button"
            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary rounded-xl hover:bg-brand-primary-hover transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs shrink-0 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
        >
            <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
            <span>Add General Reference</span>
        </button>
    </div>

    {{-- Data Table --}}
    <div class="overflow-x-auto border border-surface-border rounded-xl shadow-2xs bg-surface-card">
        <table class="min-w-full divide-y divide-surface-border/70 text-left text-xs text-content-body">
            <thead class="bg-surface-canvas text-content-muted uppercase tracking-wider text-[11px]">
                <tr>
                    <th scope="col" class="px-4 py-3 font-semibold">Reference Name</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border/70">
                @forelse($references as $reference)
                    <tr wire:key="reference-row-{{ $reference->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                        <td class="px-4 py-3 font-semibold text-content-heading whitespace-nowrap">
                            {{ Str::title($reference->name) }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                            <button
                                wire:click="openEditModal({{ $reference->id }})"
                                type="button"
                                class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                            >
                                Edit
                            </button>
                            <button
                                wire:click="confirmDelete({{ $reference->id }})"
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
                            <p class="text-xs font-semibold text-content-heading">No general references found</p>
                            <p class="text-[11px] text-content-muted mt-1">Try adjusting your search query or add a new reference.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    @if(method_exists($references, 'links'))
        <div class="pt-2">
            {{ $references->links() }}
        </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if ($showModal)
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="reference-modal-title"
            @keydown.escape.window="$set('showModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        >
            <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl border border-surface-border shadow-xl w-full max-w-md z-10 max-h-[90dvh] flex flex-col my-auto overflow-hidden">
                <div class="bg-surface-canvas px-5 sm:px-6 py-3.5 border-b border-surface-border flex justify-between items-center shrink-0">
                    <h3 id="reference-modal-title" class="text-sm font-bold text-content-heading">
                        {{ $referenceIdBeingEdited ? 'Edit General Reference' : 'Add New General Reference' }}
                    </h3>
                    <button
                        wire:click="$set('showModal', false)"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-xl font-bold cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="saveReference" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    <div>
                        <label for="reference-name" class="block text-xs font-semibold text-content-heading mb-1">
                            Reference Name <span class="text-brand-secondary">*</span>
                        </label>
                        <input
                            id="reference-name"
                            type="text"
                            wire:model="name"
                            placeholder="e.g. Encyclopedia"
                            class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
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
                            wire:target="saveReference"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary rounded-xl hover:bg-brand-primary-hover cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition-colors"
                        >
                            <span wire:loading wire:target="saveReference" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                            <span>{{ $referenceIdBeingEdited ? 'Save Changes' : 'Create Reference' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="reference-delete-title"
            @keydown.escape.window="$set('showDeleteModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl border border-surface-border shadow-xl w-full max-w-sm z-10 p-5 sm:p-6 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center justify-center mx-auto shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>

                <div>
                    <h3 id="reference-delete-title" class="text-base font-bold text-content-heading">Delete General Reference</h3>
                    <p class="text-xs text-content-body mt-1">Are you sure you want to delete this general reference? This action cannot be undone.</p>
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
                        wire:click="deleteReference"
                        wire:loading.attr="disabled"
                        wire:target="deleteReference"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-secondary rounded-xl hover:opacity-90 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition-opacity"
                    >
                        <span wire:loading wire:target="deleteReference" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
