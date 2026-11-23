{{-- resources/views/livewire/catalogs.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-6">

    {{-- Header & Quick Actions --}}
    <section aria-label="Catalogs header" class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-content-heading tracking-tight">Catalogs Management</h1>
            <p class="text-xs text-content-body mt-0.5">Master bibliographic entries linked to authors, publishers, asset types, and references.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
            {{-- Search Bar with Instant Loading State --}}
            <div class="relative w-full sm:w-72">
                <label for="catalog-search" class="sr-only">Search catalogs</label>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    id="catalog-search"
                    placeholder="Search title, ISBN, call #, author..."
                    class="w-full pl-9 pr-8 py-2 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                >
                <x-heroicon-o-magnifying-glass
                    wire:loading.remove
                    wire:target="search"
                    class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none"
                    aria-hidden="true"
                />
                <svg
                    wire:loading
                    wire:target="search"
                    class="animate-spin w-4 h-4 text-brand-primary absolute left-3 top-2.5 pointer-events-none"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
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

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="exportExcel"
                    wire:target="exportExcel"
                    wire:loading.attr="disabled"
                    class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-surface-border bg-surface-canvas hover:bg-brand-tint-light hover:text-brand-primary text-content-heading transition cursor-pointer disabled:opacity-50 shrink-0 whitespace-nowrap focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
                >
                    <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                    <span wire:loading wire:target="exportExcel">Exporting...</span>
                </button>
                <button
                    type="button"
                    wire:click="exportPdf"
                    wire:target="exportPdf"
                    wire:loading.attr="disabled"
                    class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-surface-border bg-surface-canvas hover:bg-brand-tint-light hover:text-brand-primary text-content-heading transition cursor-pointer disabled:opacity-50 shrink-0 whitespace-nowrap focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
                >
                    <span wire:loading.remove wire:target="exportPdf">Export PDF</span>
                    <span wire:loading wire:target="exportPdf">Exporting...</span>
                </button>
            </div>

            <button
                wire:click="openCreateModal"
                wire:loading.attr="disabled"
                wire:target="openCreateModal"
                type="button"
                class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs shrink-0 whitespace-nowrap focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
            >
                <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
                <span>Add Catalog</span>
            </button>
        </div>
    </section>

    {{-- Data Table Container --}}
    <div class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left border-collapse text-xs text-content-body">
                <thead>
                    <tr class="bg-surface-canvas border-b border-surface-border text-content-muted font-semibold uppercase text-[11px] tracking-wider">
                        <th scope="col" class="p-3 whitespace-nowrap">Call #</th>
                        <th scope="col" class="p-3 whitespace-nowrap">ISBN</th>
                        <th scope="col" class="p-3 pl-4 whitespace-nowrap">Title</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Author</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Publisher</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Reference</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Asset Type</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Copyright Year</th>
                        <th scope="col" class="p-3 whitespace-nowrap">Edition</th>
                        <th scope="col" class="p-3 pr-4 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border/70">
                    @forelse($catalogs as $catalog)
                        <tr wire:key="catalog-row-{{ $catalog->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                            <td class="p-3 text-brand-primary whitespace-nowrap font-mono font-bold text-[11px] uppercase">
                                {{ $catalog->call_number ?: 'N/A' }}
                            </td>
                            <td class="p-3 text-content-body whitespace-nowrap font-mono text-[11px]">
                                {{ $catalog->isbn_issn ?: 'N/A' }}
                            </td>
                            <td class="p-3 pl-4 font-medium text-content-heading max-w-[220px] truncate whitespace-nowrap">
                                <div class="font-bold truncate" title="{{ Str::title($catalog->title) }}">{{ Str::title($catalog->title) }}</div>
                            </td>
                            <td class="p-3 text-content-heading whitespace-nowrap font-medium">
                                @if($catalog->author?->name)
                                    {{ Str::title($catalog->author->name) }}
                                @else
                                    <span class="text-content-muted italic">— (Periodical)</span>
                                @endif
                            </td>
                            <td class="p-3 text-content-body whitespace-nowrap">{{ Str::title($catalog->publisher->name ?? '—') }}</td>
                            <td class="p-3 text-content-body whitespace-nowrap">{{ Str::title($catalog->generalReference->name ?? '—') }}</td>
                            <td class="p-3 text-content-body whitespace-nowrap">{{ Str::title($catalog->assetType->name ?? '—') }}</td>
                            <td class="p-3 text-content-body whitespace-nowrap font-mono text-[11px]">
                                {{ $catalog->publication_year ?: 'N/A' }}
                            </td>
                            <td class="p-3 text-content-body whitespace-nowrap font-mono text-[11px]">
                                {{ $catalog->edition ?: 'N/A' }}
                            </td>
                            <td class="p-3 pr-4 text-right whitespace-nowrap space-x-1">
                                <button
                                    wire:click="openEditModal({{ $catalog->id }})"
                                    type="button"
                                    class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                >
                                    Edit
                                </button>
                                <button
                                    wire:click="confirmDelete({{ $catalog->id }})"
                                    type="button"
                                    class="text-brand-secondary hover:bg-brand-secondary/10 font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-10">
                                <p class="text-xs font-semibold text-content-heading">No catalogs found</p>
                                <p class="text-[11px] text-content-muted mt-1">No catalogs found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
            x-data="{
                selectedAssetTypeId: @entangle('asset_type_id'),
                assetTypesList: {{ Js::from($assetTypes) }},
                periodicalNames: ['NEWSPAPER', 'NEWSPAPERS', 'MAGAZINE', 'MAGAZINES', 'JOURNAL', 'JOURNALS', 'PERIODICAL', 'PERIODICALS', 'SERIAL', 'SERIALS'],
                get isPeriodical() {
                    if (!this.selectedAssetTypeId) return false;
                    const found = this.assetTypesList.find(t => t.id == this.selectedAssetTypeId);
                    if (!found || !found.name) return false;
                    return this.periodicalNames.includes(String(found.name).trim().toUpperCase());
                }
            }"
            @keydown.escape.window="$wire.closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
        >
            <div wire:click="closeModal" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-2xl z-10 max-h-[90dvh] flex flex-col my-auto overflow-hidden border border-surface-border">
                <div class="bg-surface-canvas px-5 sm:px-6 py-4 border-b border-surface-border flex justify-between items-center shrink-0">
                    <h3 id="modal-title" class="text-sm font-bold text-content-heading">
                        {{ $catalogIdBeingEdited ? 'Edit Catalog' : 'Add New Catalog' }}
                    </h3>
                    <button
                        wire:click="closeModal"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-lg font-bold cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="saveCatalog" class="p-5 sm:p-6 space-y-4 overflow-y-auto">
                    {{-- Title & Call Number --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div class="sm:col-span-2">
                            <label for="catalog-title" class="block text-xs font-semibold text-content-heading mb-1">
                                Title <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="catalog-title"
                                type="text"
                                wire:model="title"
                                placeholder="e.g. Noli Me Tangere"
                                class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('title') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                            @error('title') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="catalog-call-number" class="block text-xs font-semibold text-content-heading mb-1">
                                Call Number <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="catalog-call-number"
                                type="text"
                                wire:model="call_number"
                                placeholder="e.g. 823.912 R59"
                                maxlength="50"
                                class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 font-mono uppercase focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('call_number') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                            @error('call_number') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Searchable Combobox Row 1: Author & Asset Type --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        {{-- Author Dropdown Combobox --}}
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                selected: @entangle('author_id'),
                                items: {{ Js::from($authors) }},
                                get selectedName() {
                                    if (!this.selected) {
                                        return isPeriodical ? 'No Author (Optional)' : 'Select Author';
                                    }
                                    const found = this.items.find(i => i.id == this.selected);
                                    return found ? found.name : 'Select Author';
                                },
                                get filtered() {
                                    if (!this.search) return this.items;
                                    return this.items.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                select(item) {
                                    this.selected = item ? item.id : null;
                                    this.open = false;
                                    this.search = '';
                                }
                            }"
                            x-effect="if (isPeriodical) { selected = null; }"
                            @click.outside="open = false; search = '';"
                            class="relative"
                        >
                            <label class="block text-xs font-semibold text-content-heading mb-1">
                                Author
                                <template x-if="!isPeriodical">
                                    <span class="text-brand-secondary">*</span>
                                </template>
                                <template x-if="isPeriodical">
                                    <span class="text-brand-primary font-normal">(Optional)</span>
                                </template>
                            </label>

                            <button
                                type="button"
                                @click="open = !open"
                                class="w-full text-left text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-heading flex items-center justify-between focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition cursor-pointer"
                            >
                                <span :class="{ 'text-content-muted': !selected }" x-text="selectedName"></span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-content-muted transition-transform" ::class="{ 'rotate-180': open }" aria-hidden="true" />
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                class="absolute z-30 mt-1 w-full rounded-2xl bg-surface-card p-3 shadow-xl border border-surface-border space-y-2"
                            >
                                <input
                                    type="text"
                                    x-model="search"
                                    x-ref="searchInput"
                                    @input.stop
                                    placeholder="Type to search author..."
                                    class="w-full text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                                    x-init="$watch('open', value => { if (value) setTimeout(() => $refs.searchInput.focus(), 50) })"
                                >
                                <div class="max-h-44 overflow-y-auto space-y-0.5">
                                    <template x-if="isPeriodical">
                                        <button
                                            type="button"
                                            @click="select(null)"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg text-content-muted hover:bg-brand-tint-light hover:text-brand-primary flex items-center justify-between cursor-pointer transition-colors"
                                            :class="{ 'bg-brand-tint-light font-semibold text-brand-primary': !selected }"
                                        >
                                            <span>No Author (Periodical / Publisher Only)</span>
                                            <x-heroicon-o-check x-show="!selected" class="w-3.5 h-3.5 text-brand-primary shrink-0" aria-hidden="true" />
                                        </button>
                                    </template>
                                    <template x-for="item in filtered" :key="item.id">
                                        <button
                                            type="button"
                                            @click="select(item)"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-brand-tint-light hover:text-brand-primary flex items-center justify-between cursor-pointer transition-colors"
                                            :class="{ 'bg-brand-tint-light font-semibold text-brand-primary': selected == item.id }"
                                        >
                                            <span x-text="item.name"></span>
                                            <x-heroicon-o-check x-show="selected == item.id" class="w-3.5 h-3.5 text-brand-primary shrink-0" aria-hidden="true" />
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" class="px-3 py-3 text-xs text-content-muted text-center italic">
                                        No authors found.
                                    </div>
                                </div>
                            </div>
                            @error('author_id') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- Asset Type Dropdown Combobox --}}
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                selected: @entangle('asset_type_id'),
                                items: {{ Js::from($assetTypes) }},
                                get selectedName() {
                                    const found = this.items.find(i => i.id == this.selected);
                                    return found ? found.name : 'Select Asset Type';
                                },
                                get filtered() {
                                    if (!this.search) return this.items;
                                    return this.items.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                select(item) {
                                    this.selected = item.id;
                                    this.open = false;
                                    this.search = '';
                                }
                            }"
                            @click.outside="open = false; search = '';"
                            class="relative"
                        >
                            <label class="block text-xs font-semibold text-content-heading mb-1">
                                Asset Type <span class="text-brand-secondary">*</span>
                            </label>

                            <button
                                type="button"
                                @click="open = !open"
                                class="w-full text-left text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-heading flex items-center justify-between focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition cursor-pointer"
                            >
                                <span :class="{ 'text-content-muted': !selected }" x-text="selectedName"></span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-content-muted transition-transform" ::class="{ 'rotate-180': open }" aria-hidden="true" />
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                class="absolute z-30 mt-1 w-full rounded-2xl bg-surface-card p-3 shadow-xl border border-surface-border space-y-2"
                            >
                                <input
                                    type="text"
                                    x-model="search"
                                    x-ref="searchInput"
                                    @input.stop
                                    placeholder="Type to search asset type..."
                                    class="w-full text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                                    x-init="$watch('open', value => { if (value) setTimeout(() => $refs.searchInput.focus(), 50) })"
                                >
                                <div class="max-h-44 overflow-y-auto space-y-0.5">
                                    <template x-for="item in filtered" :key="item.id">
                                        <button
                                            type="button"
                                            @click="select(item)"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-brand-tint-light hover:text-brand-primary flex items-center justify-between cursor-pointer transition-colors"
                                            :class="{ 'bg-brand-tint-light font-semibold text-brand-primary': selected == item.id }"
                                        >
                                            <span x-text="item.name"></span>
                                            <x-heroicon-o-check x-show="selected == item.id" class="w-3.5 h-3.5 text-brand-primary shrink-0" aria-hidden="true" />
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" class="px-3 py-3 text-xs text-content-muted text-center italic">
                                        No asset types found.
                                    </div>
                                </div>
                            </div>
                            @error('asset_type_id') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Searchable Combobox Row 2: Publisher & General Reference --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        {{-- Publisher Dropdown Combobox --}}
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                selected: @entangle('publisher_id'),
                                items: {{ Js::from($publishers) }},
                                get selectedName() {
                                    const found = this.items.find(i => i.id == this.selected);
                                    return found ? found.name : 'Select Publisher';
                                },
                                get filtered() {
                                    if (!this.search) return this.items;
                                    return this.items.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                select(item) {
                                    this.selected = item.id;
                                    this.open = false;
                                    this.search = '';
                                }
                            }"
                            @click.outside="open = false; search = '';"
                            class="relative"
                        >
                            <label class="block text-xs font-semibold text-content-heading mb-1">
                                Publisher <span class="text-brand-secondary">*</span>
                            </label>

                            <button
                                type="button"
                                @click="open = !open"
                                class="w-full text-left text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-heading flex items-center justify-between focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition cursor-pointer"
                            >
                                <span :class="{ 'text-content-muted': !selected }" x-text="selectedName"></span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-content-muted transition-transform" ::class="{ 'rotate-180': open }" aria-hidden="true" />
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                class="absolute z-30 mt-1 w-full rounded-2xl bg-surface-card p-3 shadow-xl border border-surface-border space-y-2"
                            >
                                <input
                                    type="text"
                                    x-model="search"
                                    x-ref="searchInput"
                                    @input.stop
                                    placeholder="Type to search publisher..."
                                    class="w-full text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                                    x-init="$watch('open', value => { if (value) setTimeout(() => $refs.searchInput.focus(), 50) })"
                                >
                                <div class="max-h-44 overflow-y-auto space-y-0.5">
                                    <template x-for="item in filtered" :key="item.id">
                                        <button
                                            type="button"
                                            @click="select(item)"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-brand-tint-light hover:text-brand-primary flex items-center justify-between cursor-pointer transition-colors"
                                            :class="{ 'bg-brand-tint-light font-semibold text-brand-primary': selected == item.id }"
                                        >
                                            <span x-text="item.name"></span>
                                            <x-heroicon-o-check x-show="selected == item.id" class="w-3.5 h-3.5 text-brand-primary shrink-0" aria-hidden="true" />
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" class="px-3 py-3 text-xs text-content-muted text-center italic">
                                        No publishers found.
                                    </div>
                                </div>
                            </div>
                            @error('publisher_id') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        {{-- General Reference Dropdown Combobox --}}
                        <div
                            x-data="{
                                open: false,
                                search: '',
                                selected: @entangle('general_reference_id'),
                                items: {{ Js::from($generalReferences) }},
                                get selectedName() {
                                    const found = this.items.find(i => i.id == this.selected);
                                    return found ? found.name : 'Select General Reference';
                                },
                                get filtered() {
                                    if (!this.search) return this.items;
                                    return this.items.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                select(item) {
                                    this.selected = item.id;
                                    this.open = false;
                                    this.search = '';
                                }
                            }"
                            @click.outside="open = false; search = '';"
                            class="relative"
                        >
                            <label class="block text-xs font-semibold text-content-heading mb-1">
                                General Reference <span class="text-brand-secondary">*</span>
                            </label>

                            <button
                                type="button"
                                @click="open = !open"
                                class="w-full text-left text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-heading flex items-center justify-between focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition cursor-pointer"
                            >
                                <span :class="{ 'text-content-muted': !selected }" x-text="selectedName"></span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-content-muted transition-transform" ::class="{ 'rotate-180': open }" aria-hidden="true" />
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                class="absolute z-30 mt-1 w-full rounded-2xl bg-surface-card p-3 shadow-xl border border-surface-border space-y-2"
                            >
                                <input
                                    type="text"
                                    x-model="search"
                                    x-ref="searchInput"
                                    @input.stop
                                    placeholder="Type to search reference..."
                                    class="w-full text-xs rounded-xl border border-surface-border p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                                    x-init="$watch('open', value => { if (value) setTimeout(() => $refs.searchInput.focus(), 50) })"
                                >
                                <div class="max-h-44 overflow-y-auto space-y-0.5">
                                    <template x-for="item in filtered" :key="item.id">
                                        <button
                                            type="button"
                                            @click="select(item)"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-brand-tint-light hover:text-brand-primary flex items-center justify-between cursor-pointer transition-colors"
                                            :class="{ 'bg-brand-tint-light font-semibold text-brand-primary': selected == item.id }"
                                        >
                                            <span x-text="item.name"></span>
                                            <x-heroicon-o-check x-show="selected == item.id" class="w-3.5 h-3.5 text-brand-primary shrink-0" aria-hidden="true" />
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" class="px-3 py-3 text-xs text-content-muted text-center italic">
                                        No references found.
                                    </div>
                                </div>
                            </div>
                            @error('general_reference_id') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- ISBN, Edition, Publication Year --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label for="catalog-isbn" class="block text-xs font-semibold text-content-heading mb-1">ISBN / ISSN</label>
                            <input
                                id="catalog-isbn"
                                type="text"
                                wire:model="isbn_issn"
                                maxlength="20"
                                placeholder="Leave blank to auto-generate"
                                class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('isbn_issn') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                            @error('isbn_issn') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="catalog-edition" class="block text-xs font-semibold text-content-heading mb-1">Edition</label>
                            <input
                                id="catalog-edition"
                                type="text"
                                wire:model="edition"
                                maxlength="50"
                                placeholder="e.g. 1st, Revised"
                                class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('edition') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                            @error('edition') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2 lg:col-span-1">
                            <label for="catalog-year" class="block text-xs font-semibold text-content-heading mb-1">
                                Publication Year <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="catalog-year"
                                type="number"
                                wire:model="publication_year"
                                placeholder="e.g. 2026"
                                class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 font-mono focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('publication_year') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                            @error('publication_year') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="catalog-description" class="block text-xs font-semibold text-content-heading mb-1">Description</label>
                        <textarea
                            id="catalog-description"
                            wire:model="description"
                            rows="3"
                            placeholder="Optional summary, notes, or catalog remarks..."
                            class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('description') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                        ></textarea>
                        @error('description') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 border-t border-surface-border/70">
                        <button
                            wire:click="closeModal"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border rounded-xl hover:text-content-heading cursor-pointer transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveCatalog"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary rounded-xl hover:bg-brand-primary-hover cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition-colors shadow-2xs"
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
            aria-labelledby="delete-modal-title"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-sm z-10 p-5 sm:p-6 text-center space-y-4 border border-surface-border">
                <div class="w-12 h-12 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center justify-center mx-auto shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>

                <div>
                    <h3 id="delete-modal-title" class="text-base font-bold text-content-heading">Delete Catalog Entry</h3>
                    <p class="text-xs text-content-body mt-1">Are you sure you want to delete this catalog record? This action cannot be undone.</p>
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
                        wire:click="deleteCatalog"
                        wire:loading.attr="disabled"
                        wire:target="deleteCatalog"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-secondary rounded-xl hover:opacity-90 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition-opacity shadow-2xs"
                    >
                        <span wire:loading wire:target="deleteCatalog" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
