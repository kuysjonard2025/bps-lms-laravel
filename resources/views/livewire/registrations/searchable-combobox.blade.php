<?php // resources/views/livewire/registrations/searchable-combobox.blade.php ?>
@php
    $hasError = $errorKey !== '' && $errors->has($errorKey);
@endphp

<div
    x-data="{
        open: false,
        query: '',
        selected: @entangle('value'),
        options: @js($options),
        disabled: @js($disabled),
        get filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.options;
            return this.options.filter(item => String(item.label).toLowerCase().includes(q));
        },
        get selectedLabel() {
            if (this.selected === null || this.selected === '') return '';
            const match = this.options.find(item => String(item.id) === String(this.selected));
            return match ? match.label : '';
        },
        toggle() {
            if (this.disabled) return;
            this.open = !this.open;
            if (this.open) {
                this.query = '';
                this.$nextTick(() => this.$refs.searchInput?.focus());
            }
        },
        choose(id) {
            if (this.disabled) return;
            this.selected = id;
            this.open = false;
            this.query = '';
        }
    }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    class="relative"
>
    @if($label !== '')
        <span class="block text-xs font-semibold text-content-heading mb-1">
            {{ $label }}
            @if($required)
                <span class="text-brand-secondary">*</span>
            @endif
        </span>
    @endif

    <button
        type="button"
        :disabled="disabled"
        aria-expanded="false"
        x-bind:aria-expanded="open ? 'true' : 'false'"
        x-on:click="toggle()"
        :class="disabled
            ? 'bg-surface-canvas/70 text-content-muted border-surface-border cursor-not-allowed opacity-70'
            : 'bg-surface-canvas text-content-heading cursor-pointer focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 {{ $hasError ? 'border-brand-secondary bg-brand-secondary/5' : 'border-surface-border' }}'"
        class="w-full px-3 py-2 border rounded-xl text-xs flex items-center justify-between gap-2 text-left focus-visible:outline-none transition"
    >
        <span
            x-text="selectedLabel || @js($placeholder)"
            :class="selectedLabel
                ? 'text-content-heading font-semibold truncate {{ $uppercase ? 'uppercase' : '' }}'
                : 'text-content-muted truncate'"
        ></span>
        <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0 transition-transform" ::class="open ? 'rotate-180 text-brand-primary' : 'text-content-muted'" aria-hidden="true" />
    </button>

    <div
        x-show="open && !disabled"
        x-cloak
        class="absolute z-40 mt-1.5 w-full bg-surface-card rounded-xl border border-surface-border shadow-lg p-2.5 space-y-2"
    >
        <input
            x-ref="searchInput"
            type="text"
            x-model="query"
            placeholder="{{ $searchPlaceholder }}"
            class="w-full px-3 py-2 text-xs text-content-heading placeholder:text-content-muted bg-surface-canvas border border-brand-primary rounded-lg focus:bg-surface-card focus:outline-none"
        >
        <div class="max-h-44 overflow-y-auto divide-y divide-surface-border/60">
            <template x-for="opt in filtered" :key="opt.id">
                <button
                    type="button"
                    x-on:click="choose(opt.id)"
                    :class="String(selected) === String(opt.id)
                        ? 'bg-brand-tint-light text-brand-primary font-semibold'
                        : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                    class="w-full text-left px-3 py-2 text-xs rounded-lg cursor-pointer flex items-center justify-between transition-colors {{ $uppercase ? 'uppercase' : '' }}"
                >
                    <span x-text="opt.label"></span>
                    <x-heroicon-o-check x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                </button>
            </template>
            <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-content-muted">
                {{ $emptyText }}
            </div>
        </div>
    </div>

    @if($hasError)
        <span class="text-brand-secondary text-[11px] mt-1 block font-medium">
            {{ $errors->first($errorKey) }}
        </span>
    @endif
</div>
