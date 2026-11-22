<!-- resources/views/livewire/patron-portal.blade.php -->
<div class="min-h-screen bg-surface-canvas text-content-heading p-4 sm:p-6 lg:p-8 space-y-6">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header & Active Patron / Guest Card -->
        <div class="bg-surface-card border border-surface-border rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-primary">PATRON KIOSK & OPAC CATALOG</p>
                <h1 class="text-xl sm:text-2xl font-bold text-content-heading mt-1">BPS LIBRARY PORTAL</h1>
                <p class="text-sm text-content-body mt-1">BROWSE THE PUBLIC CATALOG, TRACK LOANS & OVERDUE ITEMS, AND VIEW PATRON PROFILE DETAILS.</p>
            </div>

            <div class="bg-brand-tint-light border border-surface-border rounded-2xl px-4 py-3 text-left sm:text-right space-y-1.5">
                @if ($patron)
                    <p class="text-xs font-semibold uppercase tracking-wider text-content-muted">ACTIVE PATRON</p>
                    <p class="text-sm font-bold text-content-heading uppercase truncate max-w-xs">
                        {{ strtoupper(trim(implode(' ', array_filter([$patron->first_name ?? '', $patron->middle_name ?? '', $patron->last_name ?? '', $patron->suffix ?? ''])))) ?: strtoupper((string) ($patron->name ?? 'PATRON')) }}
                    </p>
                    <p class="text-xs text-content-body uppercase">
                        {{ strtoupper((string) ($patron->school_id ?? 'ID N/A')) }}
                        @if ($patron->patronType?->name)
                            • {{ strtoupper($patron->patronType->name) }}
                        @endif
                    </p>
                    <button
                        type="button"
                        wire:click="logoutPatron"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-lg bg-brand-secondary px-3 py-1 text-xs font-semibold uppercase text-surface-card hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                    >
                        END SESSION
                    </button>
                @else
                    <p class="text-xs font-semibold uppercase tracking-wider text-content-muted">GUEST BROWSING</p>
                    <p class="text-sm font-semibold text-content-body uppercase">NO PATRON SESSION ACTIVE</p>
                    <a
                        href="{{ route('patron.login') }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg bg-brand-primary px-3 py-1 text-xs font-semibold uppercase text-surface-card hover:bg-brand-primary-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >
                        PATRON SIGN IN
                    </a>
                @endif
            </div>
        </div>

        <!-- Navigation Tabs (Catalog always available; Records & Profile when logged in) -->
        <div class="bg-surface-card border border-surface-border rounded-2xl p-2 flex flex-wrap items-center gap-2" role="tablist" aria-label="Patron portal sections">
            <button
                type="button"
                role="tab"
                aria-selected="{{ $activeTab === 'catalog' ? 'true' : 'false' }}"
                wire:click="setTab('catalog')"
                class="rounded-xl px-4 py-2 text-xs font-semibold uppercase transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'catalog' ? 'bg-brand-primary text-surface-card' : 'text-content-body hover:bg-brand-tint-light hover:text-content-heading' }}"
            >
                OPAC CATALOG
            </button>

            @if ($patron)
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === 'records' ? 'true' : 'false' }}"
                    wire:click="setTab('records')"
                    class="rounded-xl px-4 py-2 text-xs font-semibold uppercase transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'records' ? 'bg-brand-primary text-surface-card' : 'text-content-body hover:bg-brand-tint-light hover:text-content-heading' }}"
                >
                    MY RECORDS ({{ ($circulationStats['borrowed'] ?? 0) + ($circulationStats['overdue'] ?? 0) + ($circulationStats['returned'] ?? 0) }})
                </button>

                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}"
                    wire:click="setTab('profile')"
                    class="rounded-xl px-4 py-2 text-xs font-semibold uppercase transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'profile' ? 'bg-brand-primary text-surface-card' : 'text-content-body hover:bg-brand-tint-light hover:text-content-heading' }}"
                >
                    MY PROFILE
                </button>
            @endif
        </div>

        <div wire:loading.flex class="items-center gap-2 text-xs font-semibold uppercase tracking-wider text-brand-primary">
            <span>UPDATING PORTAL DATA...</span>
        </div>

        @if ($activeTab === 'records' && $patron)
            <!-- Borrowing Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <button
                    type="button"
                    wire:click="setRecordFilter('borrowed')"
                    class="bg-surface-card border rounded-2xl p-4 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $recordFilter === 'borrowed' ? 'border-brand-primary' : 'border-surface-border' }}"
                >
                    <p class="text-xs font-semibold uppercase text-content-muted">ACTIVE BORROWED / LOAN</p>
                    <p class="text-2xl font-bold text-brand-primary mt-1">{{ $circulationStats['borrowed'] ?? 0 }}</p>
                </button>

                <button
                    type="button"
                    wire:click="setRecordFilter('overdue')"
                    class="bg-surface-card border rounded-2xl p-4 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary {{ $recordFilter === 'overdue' ? 'border-brand-secondary' : 'border-surface-border' }}"
                >
                    <p class="text-xs font-semibold uppercase text-content-muted">OVERDUE ITEMS</p>
                    <p class="text-2xl font-bold text-brand-secondary mt-1">{{ $circulationStats['overdue'] ?? 0 }}</p>
                </button>

                <button
                    type="button"
                    wire:click="setRecordFilter('returned')"
                    class="bg-surface-card border rounded-2xl p-4 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $recordFilter === 'returned' ? 'border-brand-primary' : 'border-surface-border' }}"
                >
                    <p class="text-xs font-semibold uppercase text-content-muted">RETURNED ITEMS</p>
                    <p class="text-2xl font-bold text-content-heading mt-1">{{ $circulationStats['returned'] ?? 0 }}</p>
                </button>
            </div>

            <!-- Record Filter Pills & Search -->
            <div class="bg-surface-card border border-surface-border rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach (['all' => 'ALL RECORDS', 'borrowed' => 'BORROWED / LOAN', 'overdue' => 'OVERDUE', 'returned' => 'RETURNED'] as $filterKey => $filterLabel)
                        <button
                            type="button"
                            wire:click="setRecordFilter('{{ $filterKey }}')"
                            class="rounded-xl px-3 py-1.5 text-xs font-semibold uppercase transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $recordFilter === $filterKey ? 'bg-brand-primary text-surface-card' : 'bg-surface-canvas text-content-body border border-surface-border hover:text-content-heading' }}"
                        >
                            {{ $filterLabel }}
                        </button>
                    @endforeach
                </div>

                <div class="w-full md:w-80">
                    <label for="patron-record-search" class="sr-only">Search borrowing records</label>
                    <input
                        id="patron-record-search"
                        type="text"
                        wire:model.live.debounce.300ms="recordSearch"
                        placeholder="Search title, author, accession, call no..."
                        class="w-full rounded-xl bg-surface-canvas border border-surface-border px-3.5 py-2 text-sm text-content-heading placeholder:text-content-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    />
                </div>
            </div>

            <!-- Patron Circulation Table -->
            @if ($patronRecords->isEmpty())
                <div class="bg-surface-card border border-surface-border rounded-2xl p-8 text-center">
                    <p class="text-sm font-semibold uppercase text-content-heading">NO CIRCULATION RECORDS FOUND</p>
                    <p class="text-xs text-content-body mt-1">NO ITEMS MATCH YOUR CURRENT RECORD STATUS OR SEARCH FILTER.</p>
                </div>
            @else
                <div class="bg-surface-card border border-surface-border rounded-2xl overflow-x-auto">
                    <table class="min-w-full divide-y divide-surface-border text-left text-xs uppercase">
                        <thead class="bg-surface-canvas text-content-muted font-semibold">
                            <tr>
                                <th class="px-4 py-3">TITLE & AUTHOR</th>
                                <th class="px-4 py-3">ACCESSION / CALL NO.</th>
                                <th class="px-4 py-3">BORROWED</th>
                                <th class="px-4 py-3">DUE DATE</th>
                                <th class="px-4 py-3">RETURNED</th>
                                <th class="px-4 py-3">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-border text-content-heading">
                            @foreach ($patronRecords as $record)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-content-heading truncate max-w-xs">{{ $record->catalog_title }}</p>
                                        <p class="text-content-body truncate max-w-xs">{{ $record->author_name }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-content-body">
                                        <p>{{ $record->accession_number ?: 'N/A' }}</p>
                                        <p class="text-content-muted">{{ $record->call_number ?: 'N/A' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-content-body">{{ $record->borrowed_label }}</td>
                                    <td class="px-4 py-3 text-content-body">{{ $record->due_label }}</td>
                                    <td class="px-4 py-3 text-content-body">{{ $record->returned_label }}</td>
                                    <td class="px-4 py-3">
                                        @if ($record->computed_state === 'overdue')
                                            <span class="inline-flex items-center rounded-full bg-brand-secondary/15 px-2.5 py-0.5 font-semibold text-brand-secondary">
                                                OVERDUE
                                            </span>
                                        @elseif ($record->computed_state === 'returned')
                                            <span class="inline-flex items-center rounded-full bg-brand-tint px-2.5 py-0.5 font-semibold text-brand-primary">
                                                RETURNED
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-brand-tint-light px-2.5 py-0.5 font-semibold text-brand-primary">
                                                BORROWED
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $patronRecords->links() }}
                </div>
            @endif
        @elseif ($activeTab === 'profile' && $patron)
            <!-- Complete Patron Profile Card -->
            <div class="bg-surface-card border border-surface-border rounded-2xl p-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-surface-border pb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-primary">PATRON PROFILE</p>
                        <h2 class="text-lg font-bold text-content-heading uppercase mt-1">
                            {{ strtoupper(trim(implode(' ', array_filter([$patron->first_name ?? '', $patron->middle_name ?? '', $patron->last_name ?? '', $patron->suffix ?? ''])))) ?: strtoupper((string) ($patron->name ?? 'PATRON')) }}
                        </h2>
                    </div>

                    <span class="inline-flex items-center self-start sm:self-auto rounded-full px-3 py-1 text-xs font-semibold uppercase {{ strtolower((string) ($patron->status ?? '')) === 'active' ? 'bg-brand-tint-light text-brand-primary' : 'bg-brand-secondary/15 text-brand-secondary' }}">
                        STATUS: {{ strtoupper((string) ($patron->status ?? 'UNKNOWN')) }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs uppercase">
                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold">SCHOOL / BORROWER ID</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1">{{ $patron->school_id ?: 'N/A' }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold">RFID TAG</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1">{{ $patron->rfid_tag ?: 'N/A' }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold">PATRON TYPE</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1">{{ $patron->patronType?->name ?: 'N/A' }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold">EMAIL ADDRESS</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1 break-all">{{ $patron->email ?: 'N/A' }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold">CONTACT NUMBER</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1">{{ $patron->contact_number ?: 'N/A' }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold">DEPARTMENT</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1">{{ $departmentDisplay ?? 'N/A' }}</dd>
                    </div>

                    @if ($isShs)
                        <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                            <dt class="text-content-muted font-semibold">STRAND</dt>
                            <dd class="text-sm font-bold text-content-heading mt-1">{{ $strandDisplay ?? 'N/A' }}</dd>
                        </div>
                    @endif

                    @if (! $isTeacher)
                        <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                            <dt class="text-content-muted font-semibold">GRADE LEVEL</dt>
                            <dd class="text-sm font-bold text-content-heading mt-1">{{ $gradeLevelDisplay ?? 'N/A' }}</dd>
                        </div>

                        <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                            <dt class="text-content-muted font-semibold">SECTION</dt>
                            <dd class="text-sm font-bold text-content-heading mt-1">{{ $sectionDisplay ?? 'N/A' }}</dd>
                        </div>

                        <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                            <dt class="text-content-muted font-semibold">ADVISER</dt>
                            <dd class="text-sm font-bold text-content-heading mt-1">{{ $adviserDisplay ?? 'N/A' }}</dd>
                        </div>
                    @endif

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4 sm:col-span-2 lg:col-span-3">
                        <dt class="text-content-muted font-semibold">ADDRESS</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1">{{ $patron->address ?: 'N/A' }}</dd>
                    </div>
                </dl>

                <div class="grid grid-cols-3 gap-4 pt-2 border-t border-surface-border text-center uppercase">
                    <div class="bg-brand-tint-light rounded-xl p-3">
                        <p class="text-xs font-semibold text-content-body">ACTIVE LOANS</p>
                        <p class="text-lg font-bold text-brand-primary mt-0.5">{{ $circulationStats['borrowed'] ?? 0 }}</p>
                    </div>
                    <div class="bg-brand-secondary/10 rounded-xl p-3">
                        <p class="text-xs font-semibold text-brand-secondary">OVERDUE</p>
                        <p class="text-lg font-bold text-brand-secondary mt-0.5">{{ $circulationStats['overdue'] ?? 0 }}</p>
                    </div>
                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-3">
                        <p class="text-xs font-semibold text-content-body">RETURNED</p>
                        <p class="text-lg font-bold text-content-heading mt-0.5">{{ $circulationStats['returned'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @else
            <!-- Public OPAC Catalog Search & Filter -->
            <div class="bg-surface-card border border-surface-border rounded-2xl p-4 sm:p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="opac-search" class="block text-xs font-semibold uppercase tracking-wider text-content-body mb-1.5">
                        SEARCH CATALOG
                    </label>
                    <input
                        id="opac-search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by title, author, publisher, copyright year, ISBN, call number, or accession..."
                        class="w-full rounded-xl bg-surface-canvas border border-surface-border px-3.5 py-2.5 text-sm text-content-heading placeholder:text-content-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    />
                </div>

                <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
                    <label for="opac-asset-type-input" class="block text-xs font-semibold uppercase tracking-wider text-content-body mb-1.5">
                        ASSET TYPE
                    </label>
                    <input
                        id="opac-asset-type-input"
                        type="text"
                        wire:model.live.debounce.300ms="assetTypeSearch"
                        @focus="open = true"
                        placeholder="ALL ASSET TYPES"
                        autocomplete="off"
                        role="combobox"
                        aria-expanded="open"
                        class="w-full rounded-xl bg-surface-canvas border border-surface-border px-3.5 py-2.5 text-sm uppercase text-content-heading placeholder:text-content-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    />

                    <div
                        x-show="open"
                        x-transition
                        class="absolute z-20 mt-1.5 w-full rounded-xl bg-surface-card border border-surface-border shadow-lg max-h-60 overflow-y-auto"
                    >
                        <button
                            type="button"
                            wire:click="selectAssetType('all')"
                            @click="open = false"
                            class="w-full text-left px-3.5 py-2 text-xs font-semibold uppercase text-content-heading hover:bg-brand-tint-light focus:outline-none focus-visible:bg-brand-tint-light"
                        >
                            ALL ASSET TYPES
                        </button>

                        @forelse ($assetTypeOptions as $type)
                            <button
                                type="button"
                                wire:click="selectAssetType('{{ $type->id }}')"
                                @click="open = false"
                                class="w-full text-left px-3.5 py-2 text-xs font-semibold uppercase text-content-heading hover:bg-brand-tint-light focus:outline-none focus-visible:bg-brand-tint-light"
                            >
                                {{ strtoupper($type->name) }}
                            </button>
                        @empty
                            <div class="px-3.5 py-2 text-xs uppercase text-content-muted">
                                NO MATCHING ASSET TYPES
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            @if ($catalogs->isEmpty())
                <div class="bg-surface-card border border-surface-border rounded-2xl p-8 text-center">
                    <p class="text-sm font-semibold uppercase text-content-heading">NO CATALOG ITEMS FOUND</p>
                    <p class="text-xs text-content-body mt-1">TRY ADJUSTING YOUR SEARCH TERM OR ASSET TYPE FILTER.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($catalogs as $catalog)
                        @php
                            $totalCopies = (int) ($catalog->total_copies ?? 0);
                            $availableCopies = (int) ($catalog->available_copies ?? 0);
                            $storedCopies = (int) ($catalog->stored_copies ?? 0);
                            $unaccessionedCopies = (int) ($catalog->unaccessioned_copies ?? 0);
                            $publisherName = $catalog->publisher?->name ?? 'N/A';
                            $copyrightYear = $catalog->publication_year ?: 'N/A';
                        @endphp
                        <div class="bg-surface-card border border-surface-border rounded-2xl p-5 flex flex-col justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-full bg-brand-tint px-2.5 py-0.5 text-xs font-semibold uppercase text-brand-primary">
                                        {{ strtoupper((string) ($catalog->assetType?->name ?? 'GENERAL')) }}
                                    </span>

                                    @if ($availableCopies > 0)
                                        <span class="inline-flex items-center rounded-full bg-brand-tint-light px-2.5 py-0.5 text-xs font-semibold uppercase text-brand-primary">
                                            {{ $availableCopies }} AVAILABLE
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-brand-secondary/15 px-2.5 py-0.5 text-xs font-semibold uppercase text-brand-secondary">
                                            UNAVAILABLE
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-base font-bold text-content-heading uppercase line-clamp-2">
                                    {{ $catalog->title }}
                                </h2>

                                <p class="text-xs text-content-body uppercase truncate">
                                    AUTHOR: {{ $catalog->author?->name ?? 'UNKNOWN AUTHOR' }}
                                </p>

                                <p class="text-xs text-content-body uppercase truncate">
                                    PUBLISHER: {{ $publisherName }} • COPYRIGHT: {{ $copyrightYear }}
                                </p>

                                @if ($catalog->isbn_issn)
                                    <p class="text-xs text-content-muted uppercase truncate">
                                        ISBN/ISSN: {{ $catalog->isbn_issn }}
                                    </p>
                                @endif
                            </div>

                            <div class="pt-3 border-t border-surface-border space-y-1.5 text-xs font-semibold uppercase">
                                <div class="flex items-center justify-between text-content-body">
                                    <span>TOTAL COPIES: {{ $totalCopies }}</span>
                                    <span class="text-brand-primary">AVAILABLE: {{ $availableCopies }}</span>
                                </div>
                                <div class="flex items-center justify-between text-content-muted">
                                    <span>STORED: {{ $storedCopies }}</span>
                                    <span>UNACCESSION: {{ $unaccessionedCopies }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div>
                    {{ $catalogs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
