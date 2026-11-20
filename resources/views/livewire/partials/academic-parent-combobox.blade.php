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
    <label class="block text-xs font-semibold text-slate-700">{{ $comboboxLabel }} *</label>
    <div class="relative mt-1">
        <input
            type="text"
            x-model="search"
            @focus="open = true"
            @input="open = true; if (search === '') selected = null"
            placeholder="Search or select {{ strtolower($comboboxLabel) }}..."
            autocomplete="off"
            class="w-full text-xs rounded-xl border border-slate-200 p-2.5 pr-14 bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all"
        >
        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 gap-1">
            <button x-show="selected || search" x-cloak @click.stop="clear()" type="button" class="text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer" title="Clear selection">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <button @click.stop="open = !open" type="button" class="text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer">
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak class="absolute z-30 mt-1 w-full max-h-48 overflow-y-auto rounded-xl bg-white py-1 text-xs shadow-lg ring-1 ring-black/5 border border-slate-100">
        <template x-for="item in filtered" :key="item.id">
            <button
                type="button"
                @click="select(item)"
                class="w-full text-left px-3 py-2 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between cursor-pointer transition-colors"
                :class="{ 'bg-blue-50/70 font-semibold text-blue-700': selected == item.id }"
            >
                <span x-text="formatTitle(item.name)"></span>
                <svg x-show="selected == item.id" class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </button>
        </template>
        <div x-show="filtered.length === 0" class="px-3 py-2 text-slate-400 italic">No matching {{ strtolower($comboboxLabel) }}s found.</div>
    </div>
    @error('parent_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
</div>
