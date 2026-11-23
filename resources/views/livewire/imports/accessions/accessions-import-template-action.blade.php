<div>
    <button
        type="button"
        wire:click="downloadTemplate"
        wire:loading.attr="disabled"
        wire:target="downloadTemplate"
        class="px-3.5 py-2 text-xs font-medium rounded-xl border border-surface-border bg-surface-card hover:bg-surface-canvas text-content-heading shadow-2xs transition cursor-pointer inline-flex items-center gap-2 disabled:opacity-50"
    >
        <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-content-muted" aria-hidden="true" />
        <span wire:loading.remove wire:target="downloadTemplate">Download import template</span>
        <span wire:loading wire:target="downloadTemplate">Preparing template...</span>
    </button>
</div>
