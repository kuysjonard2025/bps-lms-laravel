{{-- resources/views/livewire/partials/academic-parent-combobox.blade.php --}}
<div
    x-data="{
        open: false,
        search: '',
        selected: @entangle('parent_id'),
        items: {{ Js::from($comboboxItems) }},
        init() {
            this.syncLabel();
            this.$watch('selected', () => this.syncLabel());
        },
        formatTitle(val) {
            return String(val || '').replace(/\b\w/g, ch => ch.toUpperCase());
        },
        syncLabel() {
            const found = this.items.find(i => i.id == this.selected);
            this.search = found ? this.formatTitle(found.name) : '';
        },
        get filtered() {
            if (!this.search) return this.items;
            const current = this.items.find(i => i.id == this.selected);
            if (current && this.formatTitle(current.name).toLowerCase() === this.search.toLowerCase()) return this.items;
            return this.items.filter(i => this.formatTitle(i.name).toLowerCase().includes(this.search.toLowerCase()));
        },
        select(item) {
            this.selected = item.id;
            this.search = this.formatTitle(item.name);
            this.open = false;
        },
        clear() {
            this.selected = null;
            this.search = '';
            this.open = true;
        }
    }"
    @click.outside="open = false; syncLabel();"
    class="relative"
>
    <label class="block text-xs font-semibold text-content-heading">
        {{ $comboboxLabel }} <span class="text-brand-secondary">*</span>
    </label>
    <div class="relative mt-1">
        <input
            type="text"
            x-model="search"
            @focus="open = true"
            @input="open = true; if (search === '') selected = null"
            placeholder="Search or select {{ strtolower($comboboxLabel) }}..."
            autocomplete="off"
            class="w-full text-xs rounded-xl border border-surface-border p-2.5 pr-14 bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
        >
        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 gap-1">
            <button
                x-show="selected || search"
                x-cloak
                @click.stop="clear()"
                type="button"
                aria-label="Clear selection"
                title="Clear selection"
                class="text-content-muted hover:text-content-heading p-0.5 rounded cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
            >
                <x-heroicon-o-x-mark class="w-3.5 h-3.5" aria-hidden="true" />
            </button>
            <button
                @click.stop="open = !open"
                type="button"
                aria-label="Toggle {{ strtolower($comboboxLabel) }} list"
                class="text-content-muted hover:text-content-heading p-0.5 rounded cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
            >
                <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform" ::class="{ 'rotate-180': open }" aria-hidden="true" />
            </button>
        </div>
    </div>

    <div
        x-show="open"
        x-cloak
        class="absolute z-30 mt-1 w-full max-h-48 overflow-y-auto rounded-xl bg-surface-card py-1 text-xs text-content-body shadow-lg border border-surface-border"
    >
        <template x-for="item in filtered" :key="item.id">
            <button
                type="button"
                @click="select(item)"
                class="w-full text-left px-3 py-2 hover:bg-brand-tint-light hover:text-brand-primary flex items-center justify-between cursor-pointer transition-colors"
                :class="{ 'bg-brand-tint-light font-semibold text-brand-primary': selected == item.id }"
            >
                <span x-text="formatTitle(item.name)"></span>
                <x-heroicon-o-check x-show="selected == item.id" class="w-3.5 h-3.5 text-brand-primary shrink-0" aria-hidden="true" />
            </button>
        </template>
        <div x-show="filtered.length === 0" class="px-3 py-2 text-content-muted italic">No matching {{ strtolower($comboboxLabel) }}s found.</div>
    </div>
    @error('parent_id') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
</div>
