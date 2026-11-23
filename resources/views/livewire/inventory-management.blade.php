<?php // resources/views/livewire/inventory-management.blade.php ?>
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-5 text-content-body">
    {{-- Header Layout --}}
    <section aria-label="Inventory management header" class="bg-surface-card px-6 py-5 rounded-2xl border border-surface-border shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-content-heading tracking-tight">Inventory Management</h1>
            <p class="text-xs text-content-muted mt-0.5">Manage title-level stock counts, accession statuses, and condition metrics.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                <button
                    type="button"
                    @click="open = !open"
                    aria-haspopup="menu"
                    :aria-expanded="open.toString()"
                    class="px-3.5 py-2 text-xs font-semibold bg-surface-canvas text-content-heading border border-surface-border rounded-xl hover:bg-surface-card transition flex items-center gap-2 shadow-2xs cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
                >
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-content-muted shrink-0" aria-hidden="true" />
                    <span>Export</span>
                    <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-content-muted transition-transform duration-200" ::class="{ 'rotate-180 text-brand-primary': open }" aria-hidden="true" />
                </button>
                <div
                    x-show="open"
                    x-cloak
                    role="menu"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-40 bg-surface-card rounded-xl shadow-lg border border-surface-border p-1 z-30 text-xs"
                >
                    <button
                        type="button"
                        role="menuitem"
                        wire:click="exportExcel"
                        wire:target="exportExcel"
                        wire:loading.attr="disabled"
                        @click="open = false"
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-brand-tint-light text-brand-primary font-medium flex items-center gap-2 cursor-pointer transition disabled:opacity-50"
                    >
                        <x-heroicon-o-table-cells class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                        <span>Export Excel</span>
                    </button>
                    <button
                        type="button"
                        role="menuitem"
                        wire:click="exportPdf"
                        wire:target="exportPdf"
                        wire:loading.attr="disabled"
                        @click="open = false"
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-brand-secondary/10 text-brand-secondary font-medium flex items-center gap-2 cursor-pointer transition disabled:opacity-50"
                    >
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 text-brand-secondary shrink-0" aria-hidden="true" />
                        <span>Export PDF</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Row --}}
    <section aria-label="Inventory overview statistics" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4">
        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted tracking-wider block">TOTAL ACCESSIONED</span>
                <span class="text-xl font-bold font-mono text-content-heading">{{ number_format($stats['total_accessioned']) }}</span>
                <span class="text-[10px] text-brand-secondary font-semibold block mt-0.5 font-mono">UNACCESSIONED: {{ number_format($stats['total_unaccessioned']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light border border-brand-primary/25 text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-archive-box class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted tracking-wider block">AVAILABLE</span>
                <span class="text-xl font-bold font-mono text-brand-primary">{{ number_format($stats['total_available']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light border border-brand-primary/25 text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-check-circle class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted tracking-wider block">ON LOAN</span>
                <span class="text-xl font-bold font-mono text-brand-primary">{{ number_format($stats['total_on_loan']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-book-open class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted tracking-wider block">MAINTENANCE</span>
                <span class="text-xl font-bold font-mono text-brand-secondary">{{ number_format($stats['total_maintenance']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-secondary/10 text-brand-secondary flex items-center justify-center shrink-0">
                <x-heroicon-o-wrench-screwdriver class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted tracking-wider block">DAMAGED</span>
                <span class="text-xl font-bold font-mono text-brand-secondary">{{ number_format($stats['total_damaged']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-secondary/15 text-brand-secondary flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted tracking-wider block">LOST</span>
                <span class="text-xl font-bold font-mono text-brand-secondary">{{ number_format($stats['total_lost']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-secondary/15 text-brand-secondary flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-circle class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted tracking-wider block">DUMPED</span>
                <span class="text-xl font-bold font-mono text-content-body">{{ number_format($stats['total_dumped']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-surface-canvas border border-surface-border text-content-muted flex items-center justify-center shrink-0">
                <x-heroicon-o-trash class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>
    </section>

    {{-- Main Panel with Search and Filters --}}
    <section aria-label="Inventory title breakdown" class="space-y-4">
        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div class="relative flex-1 w-full lg:max-w-md">
                <label for="inventory-search-input" class="sr-only">Search title, author, ISBN, or accession number</label>
                <input
                    id="inventory-search-input"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    wire:keydown.escape="$set('search', '')"
                    placeholder="Search title, author, ISBN, accession #..."
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-surface-border bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                >
                <x-heroicon-o-magnifying-glass wire:loading.remove wire:target="search" class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
                <span wire:loading wire:target="search" class="animate-spin h-4 w-4 border-2 border-brand-primary border-t-transparent rounded-full absolute left-3 top-2.5 inline-block"></span>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Searchable Combobox (Input + Filtered Select List) for Asset Types --}}
                <div
                    class="relative w-full sm:w-64"
                    x-data="{
                        open: false,
                        query: @entangle('assetTypeSearch'),
                        selectedId: @entangle('assetTypeFilter'),
                        items: @js($assetTypes->map(fn ($t) => ['id' => (string) $t->id, 'name' => (string) $t->name])->values()),
                        get filtered() {
                            const q = (this.query || '').trim().toLowerCase();
                            if (!q || q === 'all asset types') return this.items;
                            return this.items.filter(i => i.name.toLowerCase().includes(q));
                        },
                        onType(e) {
                            this.query = e.target.value;
                            this.open = true;
                            const exact = this.items.find(i => i.name.toLowerCase() === this.query.trim().toLowerCase());
                            if (exact) {
                                $wire.selectAssetType(exact.id);
                            } else if (this.query.trim() === '' || this.query.trim().toLowerCase() === 'all asset types') {
                                $wire.selectAssetType('all');
                            }
                        },
                        choose(id, name) {
                            this.query = name;
                            this.open = false;
                            $wire.selectAssetType(id);
                        },
                        clearAll() {
                            this.query = '';
                            this.open = false;
                            $wire.selectAssetType('all');
                        }
                    }"
                    @click.outside="open = false"
                    @keydown.escape.window="open = false"
                >
                    <label for="inventory-asset-type-combobox" class="sr-only">Search or select asset type</label>
                    <div class="relative flex items-center">
                        <input
                            id="inventory-asset-type-combobox"
                            type="text"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-controls="inventory-asset-type-listbox"
                            :aria-expanded="open.toString()"
                            :value="query"
                            @focus="open = true"
                            @input="onType($event)"
                            placeholder="All Asset Types"
                            class="w-full py-2 pl-3.5 pr-16 text-xs rounded-xl border border-surface-border bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                        >
                        <button
                            type="button"
                            x-show="query"
                            x-cloak
                            @click="clearAll()"
                            aria-label="Clear asset type filter"
                            class="absolute right-8 text-content-muted hover:text-content-heading px-1 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded"
                        >&times;</button>
                        <button
                            type="button"
                            @click="open = !open"
                            aria-label="Toggle asset type options"
                            class="absolute right-2.5 text-content-muted hover:text-content-heading p-1 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded"
                        >
                            <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform duration-200" ::class="{ 'rotate-180 text-brand-primary': open }" aria-hidden="true" />
                        </button>
                    </div>

                    <div
                        id="inventory-asset-type-listbox"
                        role="listbox"
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="absolute right-0 mt-1.5 w-full max-h-60 overflow-y-auto rounded-xl border border-surface-border bg-surface-card shadow-lg z-30 p-1 text-xs"
                    >
                        <button
                            type="button"
                            role="option"
                            :aria-selected="(selectedId === 'all').toString()"
                            @click="choose('all', 'All Asset Types')"
                            class="w-full text-left px-3 py-2 rounded-lg font-semibold transition cursor-pointer flex items-center justify-between"
                            :class="selectedId === 'all' ? 'bg-brand-tint-light text-brand-primary' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                        >
                            <span>All Asset Types</span>
                            <span x-show="selectedId === 'all'" class="text-[10px] font-bold">Selected</span>
                        </button>

                        <template x-for="item in filtered" :key="item.id">
                            <button
                                type="button"
                                role="option"
                                :aria-selected="(selectedId === item.id).toString()"
                                @click="choose(item.id, item.name)"
                                class="w-full text-left px-3 py-2 rounded-lg transition cursor-pointer flex items-center justify-between mt-0.5"
                                :class="selectedId === item.id ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                            >
                                <span x-text="item.name"></span>
                                <span x-show="selectedId === item.id" class="text-[10px] font-bold">Selected</span>
                            </button>
                        </template>

                        <div x-show="filtered.length === 0" class="px-3 py-3 text-center text-content-muted">
                            No matching asset type.
                        </div>
                    </div>
                </div>

                @if ($search !== '' || $assetTypeFilter !== 'all')
                    <button
                        type="button"
                        wire:click="resetFilters"
                        wire:target="resetFilters"
                        class="px-3.5 py-2 text-xs font-medium rounded-xl border border-surface-border bg-surface-canvas text-content-muted hover:text-content-heading transition cursor-pointer shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >
                        Reset
                    </button>
                @endif
            </div>
        </div>

        {{-- Table Container --}}
        <div class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse text-xs text-content-body">
                    <thead>
                        <tr class="bg-surface-canvas border-b border-surface-border text-[11px] font-semibold uppercase tracking-wider text-content-muted">
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Title</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Author</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Asset Type</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Total</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Unaccessioned</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Accessioned</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Available</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Stored</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">On Loan</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Damaged</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Lost</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Maintenance</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Dumped</th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Batches</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border/70 bg-surface-card">
                        @forelse ($catalogs as $catalog)
                            @php
                                $accessionedCopies = (int) ($catalog->accessioned_copies ?? 0);
                                $receivedQty = (int) ($catalog->total_received_quantity ?? 0);
                                $totalStock = max($receivedQty, $accessionedCopies);
                                $unaccessionedCopies = max(0, $totalStock - $accessionedCopies);
                            @endphp
                            <tr wire:key="inventory-row-{{ $catalog->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-bold text-content-heading">{{ Str::title((string) $catalog->title) }}</div>
                                    @if ($catalog->isbn_issn)
                                        <div class="text-[11px] text-content-muted font-mono">ISBN: {{ $catalog->isbn_issn }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-content-body">
                                    {{ $catalog->author?->name ? Str::title((string) $catalog->author->name) : 'Unknown' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-surface-canvas border border-surface-border text-content-heading">
                                        {{ $catalog->assetType?->name ? Str::title((string) $catalog->assetType->name) : 'Standard' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center font-bold font-mono text-content-heading">{{ $totalStock }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono {{ $unaccessionedCopies > 0 ? 'bg-brand-secondary/15 text-brand-secondary' : 'bg-surface-canvas text-content-muted' }}">
                                        {{ $unaccessionedCopies }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center font-semibold font-mono text-content-heading">{{ $accessionedCopies }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-brand-tint-light text-brand-primary">{{ $catalog->available_copies }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-brand-tint-light border border-brand-primary/20 text-brand-primary">{{ $catalog->stored_copies }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-brand-tint text-brand-primary">{{ $catalog->on_loan_copies }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono {{ $catalog->damaged_copies > 0 ? 'bg-brand-secondary/15 text-brand-secondary' : 'bg-surface-canvas text-content-muted' }}">{{ $catalog->damaged_copies }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono {{ $catalog->lost_copies > 0 ? 'bg-brand-secondary/15 text-brand-secondary' : 'bg-surface-canvas text-content-muted' }}">{{ $catalog->lost_copies }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-brand-secondary/10 text-brand-secondary">{{ $catalog->maintenance_copies }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono {{ $catalog->dumped_copies > 0 ? 'bg-surface-canvas border border-surface-border text-content-heading' : 'bg-surface-canvas text-content-muted' }}">{{ $catalog->dumped_copies }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center font-mono text-content-body">{{ $catalog->acquisition_batches }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="px-4 py-10 text-center">
                                    <p class="text-xs font-semibold text-content-heading">No inventory entries found</p>
                                    <p class="text-[11px] text-content-muted mt-1">Try adjusting your search query or asset type filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (method_exists($catalogs, 'hasPages') && $catalogs->hasPages())
            <div class="pt-1">
                {{ $catalogs->links() }}
            </div>
        @endif
    </section>
</div>
