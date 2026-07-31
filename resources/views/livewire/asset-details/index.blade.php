<div class="p-4 lg:p-6 space-y-6 mx-auto">
    {{-- Header --}}
    <div>
        <h1 class="text-xl lg:text-2xl font-bold text-gray-900 whitespace-nowrap">Asset Details & References</h1>
        <p class="text-xs lg:text-sm text-gray-500">Manage catalog metadata, authors, publishers, reference types, and categories.</p>
    </div>

    {{-- Tabs Navigation --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-2">
        <nav class="flex flex-wrap gap-2" aria-label="Tabs">
            @foreach($tabs as $key => $label)
                <button
                    wire:click="setTab('{{ $key }}')"
                    type="button"
                    class="px-4 py-2 text-xs lg:text-sm font-semibold rounded-lg transition-colors cursor-pointer whitespace-nowrap shrink-0 {{ $currentTab === $key ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab Contents --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-4 lg:p-6">
        @switch($currentTab)
            @case('authors')
                <livewire:asset-details.authors-tab />
                @break

            @case('publishers')
                <livewire:asset-details.publishers-tab />
                @break

            @case('general-references')
                <livewire:asset-details.general-references-tab />
                @break

            @case('asset-types')
                <livewire:asset-details.asset-types-tab />
                @break
        @endswitch
    </div>
</div>
