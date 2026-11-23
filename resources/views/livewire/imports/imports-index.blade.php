<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-content-heading">Imports</h1>
            <p class="text-xs text-content-body mt-0.5">
                Select a module tab to manage batch spreadsheet imports.
            </p>
        </div>

        <a
            href="{{ route('catalogs') }}"
            wire:navigate
            class="px-3.5 py-2 text-xs font-medium rounded-xl border border-surface-border bg-surface-card hover:bg-surface-canvas text-content-heading transition"
        >
            Back to Catalogs
        </a>
    </div>

    <div class="border-b border-surface-border">
        <nav class="flex flex-wrap gap-2 -mb-px" aria-label="Import module tabs">
            @php
                $tabs = [
                    'catalogs'     => 'Catalogs',
                    'accessions'   => 'Accessions',
                    'acquisitions' => 'Acquisitions',
                    'patrons'      => 'Patrons',
                ];
            @endphp

            @foreach ($tabs as $key => $label)
                <button
                    type="button"
                    wire:click="setTab('{{ $key }}')"
                    class="px-4 py-2.5 text-xs font-semibold border-b-2 transition cursor-pointer {{ $activeTab === $key ? 'border-[#4D9EFF] text-[#4D9EFF]' : 'border-transparent text-content-body hover:text-content-heading hover:border-surface-border' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    @if ($activeTab === 'catalogs')
        <div class="space-y-5">
            <livewire:imports.catalogs.catalogs-import-template-action />
            <livewire:imports.catalogs.catalogs-import-upload-form />
            <livewire:imports.catalogs.catalogs-import-preview />
        </div>
    @elseif ($activeTab === 'accessions')
        <div class="space-y-5">
            <livewire:imports.accessions.accessions-import-template-action />
            <livewire:imports.accessions.accessions-import-upload-form />
            <livewire:imports.accessions.accessions-import-validation-preview />
        </div>
    @elseif ($activeTab === 'acquisitions')
        <div class="rounded-2xl border border-surface-border bg-surface-card p-6 text-xs text-content-body">
            <p class="font-semibold text-content-heading">Acquisitions Import</p>
            <p class="mt-1">Not configured yet. Ready to build when you specify the Acquisitions import columns and rules.</p>
        </div>
    @elseif ($activeTab === 'patrons')
        <div class="rounded-2xl border border-surface-border bg-surface-card p-6 text-xs text-content-body">
            <p class="font-semibold text-content-heading">Patrons Import</p>
            <p class="mt-1">Not configured yet. Ready to build when you specify the Patrons import columns and rules.</p>
        </div>
    @endif
</div>
