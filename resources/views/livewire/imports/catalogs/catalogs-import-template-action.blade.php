{{-- resources/views/livewire/imports/catalogs/catalogs-import-template-action.blade.php --}}
<div class="inline-block">
    <button
        type="button"
        wire:click="downloadTemplate"
        wire:target="downloadTemplate"
        wire:loading.attr="disabled"
        class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-surface-border bg-surface-card text-content-heading hover:bg-surface-canvas transition inline-flex items-center gap-1.5 shadow-2xs cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
    >
        <x-heroicon-o-arrow-down-tray wire:loading.remove wire:target="downloadTemplate" class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
        <span wire:loading wire:target="downloadTemplate" class="animate-spin h-3.5 w-3.5 border-2 border-brand-primary border-t-transparent rounded-full"></span>
        <span>Download import template</span>
    </button>
</div>
