{{-- resources/views/livewire/asset-details/index.blade.php --}}
<div class="space-y-6">
    {{-- Header --}}
    <section aria-label="Asset details header" class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs px-6 py-4">
        <h1 class="text-lg sm:text-xl font-bold text-content-heading tracking-tight">Asset Details &amp; References</h1>
        <p class="text-xs text-content-body mt-0.5">Manage catalog metadata, authors, publishers, reference types, and categories.</p>
    </section>

    {{-- Tabs Navigation --}}
    <div class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs p-2">
        <nav class="flex flex-wrap gap-2" aria-label="Asset detail sections" role="tablist">
            @foreach($tabs as $key => $label)
                <button
                    wire:key="tab-button-{{ $key }}"
                    wire:click="setTab('{{ $key }}')"
                    type="button"
                    role="tab"
                    aria-selected="{{ $currentTab === $key ? 'true' : 'false' }}"
                    class="px-4 py-2 text-xs font-semibold rounded-xl transition-colors cursor-pointer whitespace-nowrap shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $currentTab === $key ? 'bg-brand-primary text-white shadow-2xs' : 'text-content-body hover:text-content-heading hover:bg-surface-canvas' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab Contents --}}
    <div class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs p-4 sm:p-6">
        @switch($currentTab)
            @case('authors')
                <livewire:asset-details.authors-tab wire:key="tab-authors" />
                @break

            @case('publishers')
                <livewire:asset-details.publishers-tab wire:key="tab-publishers" />
                @break

            @case('general-references')
                <livewire:asset-details.general-references-tab wire:key="tab-general-references" />
                @break

            @case('asset-types')
                <livewire:asset-details.asset-types-tab wire:key="tab-asset-types" />
                @break

            @default
                <livewire:asset-details.authors-tab wire:key="tab-default" />
        @endswitch
    </div>
</div>
