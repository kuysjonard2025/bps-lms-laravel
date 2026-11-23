<!-- resources/views/livewire/patron-portal.blade.php -->
<div class="min-h-screen bg-surface-canvas text-content-heading p-4 sm:p-6 lg:p-8 space-y-6">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header & Active Patron / Guest Card -->
        <div class="bg-surface-card border border-surface-border rounded-2xl p-5 sm:p-6 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-primary">Patron Kiosk &amp; OPAC Catalog</p>
                <h1 class="text-xl sm:text-2xl font-bold text-content-heading mt-1">BPS Library Portal</h1>
                <p class="text-sm text-content-body mt-1">Browse the public catalog, track loans and overdue items, and view patron profile details.</p>
            </div>

            <div class="bg-brand-tint-light border border-surface-border rounded-2xl px-4 py-3 text-left sm:text-right space-y-1.5 shrink-0">
                @if ($patron)
                    <p class="text-xs font-semibold uppercase tracking-wider text-content-muted">Active Patron</p>
                    <p class="text-sm font-bold text-content-heading truncate max-w-xs">
                        {{ trim(implode(' ', array_filter([$patron->first_name ?? '', $patron->middle_name ?? '', $patron->last_name ?? '', $patron->suffix ?? '']))) ?: ($patron->name ?? 'Patron') }}
                    </p>
                    <p class="text-xs text-content-body">
                        {{ $patron->school_id ?? 'ID N/A' }}
                        @if ($patron->patronType?->name)
                            &bull; {{ \Illuminate\Support\Str::title($patron->patronType->name) }}
                        @endif
                    </p>
                    <button
                        type="button"
                        wire:click="logoutPatron"
                        wire:loading.attr="disabled"
                        wire:target="logoutPatron"
                        class="inline-flex items-center rounded-lg bg-brand-secondary px-3 py-1 text-xs font-semibold text-white hover:opacity-90 transition cursor-pointer disabled:opacity-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                    >
                        End Session
                    </button>
                @else
                    <p class="text-xs font-semibold uppercase tracking-wider text-content-muted">Guest Browsing</p>
                    <p class="text-sm font-semibold text-content-body">No patron session active</p>
                    <a
                        href="{{ route('patron.login') }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg bg-brand-primary px-3 py-1 text-xs font-semibold text-white hover:bg-brand-primary-hover transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >
                        Patron Sign In
                    </a>
                @endif
            </div>
        </div>

        <!-- Navigation Tabs (Catalog always available; Records & Profile when logged in) -->
        <div class="bg-surface-card border border-surface-border rounded-2xl p-2 shadow-2xs flex flex-wrap items-center justify-between gap-2" role="tablist" aria-label="Patron portal sections">
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === 'catalog' ? 'true' : 'false' }}"
                    wire:click="setTab('catalog')"
                    class="rounded-xl px-4 py-2 text-xs font-semibold transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'catalog' ? 'bg-brand-primary text-white' : 'text-content-body hover:bg-brand-tint-light hover:text-content-heading' }}"
                >
                    OPAC Catalog
                </button>

                @if ($patron)
                    <button
                        type="button"
                        role="tab"
                        aria-selected="{{ $activeTab === 'records' ? 'true' : 'false' }}"
                        wire:click="setTab('records')"
                        class="rounded-xl px-4 py-2 text-xs font-semibold transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'records' ? 'bg-brand-primary text-white' : 'text-content-body hover:bg-brand-tint-light hover:text-content-heading' }}"
                    >
                        My Records ({{ ($circulationStats['borrowed'] ?? 0) + ($circulationStats['overdue'] ?? 0) + ($circulationStats['returned'] ?? 0) }})
                    </button>

                    <button
                        type="button"
                        role="tab"
                        aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}"
                        wire:click="setTab('profile')"
                        class="rounded-xl px-4 py-2 text-xs font-semibold transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'profile' ? 'bg-brand-primary text-white' : 'text-content-body hover:bg-brand-tint-light hover:text-content-heading' }}"
                    >
                        My Profile
                    </button>
                @endif
            </div>

            @unless ($patron)
                <a
                    href="{{ route('login') }}"
                    wire:navigate
                    class="inline-flex items-center gap-1.5 rounded-xl bg-surface-canvas hover:bg-brand-tint-light border border-surface-border px-4 py-2 text-xs font-bold text-content-heading transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                >
                    <x-heroicon-o-home class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                    <span>Main Home</span>
                </a>
            @endunless
        </div>

        <div wire:loading.flex class="items-center gap-2 text-xs font-semibold text-brand-primary">
            <span class="animate-spin h-3.5 w-3.5 border-2 border-brand-primary border-t-transparent rounded-full"></span>
            <span>Updating portal data...</span>
        </div>

        @if ($activeTab === 'records' && $patron)
            <!-- Borrowing Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <button
                    type="button"
                    wire:click="setRecordFilter('borrowed')"
                    class="bg-surface-card border rounded-2xl p-4 text-left transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $recordFilter === 'borrowed' ? 'border-brand-primary ring-2 ring-brand-primary/15' : 'border-surface-border hover:border-brand-primary/50' }}"
                >
                    <p class="text-xs font-semibold uppercase tracking-wider text-content-muted">Active Borrowed / Loan</p>
                    <p class="text-2xl font-bold font-mono text-brand-primary mt-1">{{ $circulationStats['borrowed'] ?? 0 }}</p>
                </button>

                <button
                    type="button"
                    wire:click="setRecordFilter('overdue')"
                    class="bg-surface-card border rounded-2xl p-4 text-left transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary {{ $recordFilter === 'overdue' ? 'border-brand-secondary ring-2 ring-brand-secondary/15' : 'border-surface-border hover:border-brand-secondary/50' }}"
                >
                    <p class="text-xs font-semibold uppercase tracking-wider text-content-muted">Overdue Items</p>
                    <p class="text-2xl font-bold font-mono text-brand-secondary mt-1">{{ $circulationStats['overdue'] ?? 0 }}</p>
                </button>

                <button
                    type="button"
                    wire:click="setRecordFilter('returned')"
                    class="bg-surface-card border rounded-2xl p-4 text-left transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $recordFilter === 'returned' ? 'border-brand-primary ring-2 ring-brand-primary/15' : 'border-surface-border hover:border-brand-primary/50' }}"
                >
                    <p class="text-xs font-semibold uppercase tracking-wider text-content-muted">Returned Items</p>
                    <p class="text-2xl font-bold font-mono text-content-heading mt-1">{{ $circulationStats['returned'] ?? 0 }}</p>
                </button>
            </div>

            <!-- Record Filter Pills & Search -->
            <div class="bg-surface-card border border-surface-border rounded-2xl p-4 sm:p-5 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach (['all' => 'All Records', 'borrowed' => 'Borrowed / Loan', 'overdue' => 'Overdue', 'returned' => 'Returned'] as $filterKey => $filterLabel)
                        <button
                            type="button"
                            wire:click="setRecordFilter('{{ $filterKey }}')"
                            class="rounded-xl px-3 py-1.5 text-xs font-semibold transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $recordFilter === $filterKey ? 'bg-brand-primary text-white' : 'bg-surface-canvas text-content-body border border-surface-border hover:text-content-heading' }}"
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
                    <p class="text-sm font-semibold text-content-heading">No circulation records found</p>
                    <p class="text-xs text-content-body mt-1">No items match your current record status or search filter.</p>
                </div>
            @else
                <div class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs overflow-x-auto">
                    <table class="min-w-full divide-y divide-surface-border text-left text-xs">
                        <thead class="bg-surface-canvas text-content-muted font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Title</th>
                                <th class="px-4 py-3">Author</th>
                                <th class="px-4 py-3">Accession No.</th>
                                <th class="px-4 py-3">Call No.</th>
                                <th class="px-4 py-3">Borrowed</th>
                                <th class="px-4 py-3">Due Date</th>
                                <th class="px-4 py-3">Returned</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-border text-content-heading">
                            @foreach ($patronRecords as $record)
                                @php
                                    $recordTitle = filled($record->catalog_title)
                                        ? \Illuminate\Support\Str::title($record->catalog_title)
                                        : 'N/A';
                                    $recordAuthor = (filled($record->author_name) && strtoupper(trim($record->author_name)) !== 'N/A')
                                        ? \Illuminate\Support\Str::title($record->author_name)
                                        : 'N/A';
                                @endphp
                                <tr class="hover:bg-surface-canvas/60 transition-colors">
                                    <td class="px-4 py-3 font-bold text-content-heading whitespace-nowrap">{{ $recordTitle }}</td>
                                    <td class="px-4 py-3 text-content-body whitespace-nowrap">{{ $recordAuthor }}</td>
                                    <td class="px-4 py-3 text-content-body font-mono whitespace-nowrap">{{ $record->accession_number ?: 'N/A' }}</td>
                                    <td class="px-4 py-3 text-content-body font-mono whitespace-nowrap">{{ $record->call_number ?: 'N/A' }}</td>
                                    <td class="px-4 py-3 text-content-body font-mono whitespace-nowrap">{{ $record->borrowed_label }}</td>
                                    <td class="px-4 py-3 text-content-body font-mono whitespace-nowrap">{{ $record->due_label }}</td>
                                    <td class="px-4 py-3 text-content-body font-mono whitespace-nowrap">{{ $record->returned_label }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($record->computed_state === 'overdue')
                                            <span class="inline-flex items-center rounded-full bg-brand-secondary/15 px-2.5 py-0.5 font-semibold text-brand-secondary">
                                                Overdue
                                            </span>
                                        @elseif ($record->computed_state === 'returned')
                                            <span class="inline-flex items-center rounded-full bg-brand-tint px-2.5 py-0.5 font-semibold text-brand-primary">
                                                Returned
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-brand-tint-light px-2.5 py-0.5 font-semibold text-brand-primary">
                                                Borrowed
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
            @php
                $patronTypeDisplay = filled($patron->patronType?->name)
                    ? \Illuminate\Support\Str::title($patron->patronType->name)
                    : 'N/A';
                $addressDisplay = filled($patron->address)
                    ? \Illuminate\Support\Str::title($patron->address)
                    : 'N/A';
            @endphp
            <!-- Complete Patron Profile Card -->
            <div class="bg-surface-card border border-surface-border rounded-2xl p-6 shadow-2xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-surface-border pb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-primary">Patron Profile</p>
                        <h2 class="text-lg font-bold text-content-heading mt-1">
                            {{ trim(implode(' ', array_filter([$patron->first_name ?? '', $patron->middle_name ?? '', $patron->last_name ?? '', $patron->suffix ?? '']))) ?: ($patron->name ?? 'Patron') }}
                        </h2>
                    </div>

                    <span class="inline-flex items-center self-start sm:self-auto rounded-full px-3 py-1 text-xs font-semibold {{ strtolower((string) ($patron->status ?? '')) === 'active' ? 'bg-brand-tint-light text-brand-primary' : 'bg-brand-secondary/15 text-brand-secondary' }}">
                        Status: {{ ucfirst(strtolower((string) ($patron->status ?? 'Unknown'))) }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold uppercase tracking-wider">School / Borrower ID</dt>
                        <dd class="text-sm font-bold font-mono text-content-heading mt-1">{{ $patron->school_id ?: 'N/A' }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold uppercase tracking-wider">RFID Tag</dt>
                        <dd class="text-sm font-bold font-mono text-content-heading mt-1">{{ $patron->rfid_tag ?: 'N/A' }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold uppercase tracking-wider">Patron Type</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1">{{ $patronTypeDisplay }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold uppercase tracking-wider">Email Address</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1 break-all">{{ $patron->email ?: 'N/A' }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold uppercase tracking-wider">Contact Number</dt>
                        <dd class="text-sm font-bold font-mono text-content-heading mt-1">{{ $patron->contact_number ?: 'N/A' }}</dd>
                    </div>

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                        <dt class="text-content-muted font-semibold uppercase tracking-wider">Department</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1">{{ $departmentDisplay ?? 'N/A' }}</dd>
                    </div>

                    @if ($isShs)
                        <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                            <dt class="text-content-muted font-semibold uppercase tracking-wider">Strand</dt>
                            <dd class="text-sm font-bold text-content-heading mt-1">{{ $strandDisplay ?? 'N/A' }}</dd>
                        </div>
                    @endif

                    @if (! $isTeacher)
                        <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                            <dt class="text-content-muted font-semibold uppercase tracking-wider">Grade Level</dt>
                            <dd class="text-sm font-bold text-content-heading mt-1">{{ $gradeLevelDisplay ?? 'N/A' }}</dd>
                        </div>

                        <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                            <dt class="text-content-muted font-semibold uppercase tracking-wider">Section</dt>
                            <dd class="text-sm font-bold text-content-heading mt-1">{{ $sectionDisplay ?? 'N/A' }}</dd>
                        </div>

                        <div class="bg-surface-canvas border border-surface-border rounded-xl p-4">
                            <dt class="text-content-muted font-semibold uppercase tracking-wider">Adviser</dt>
                            <dd class="text-sm font-bold text-content-heading mt-1">{{ $adviserDisplay ?? 'N/A' }}</dd>
                        </div>
                    @endif

                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-4 sm:col-span-2 lg:col-span-3">
                        <dt class="text-content-muted font-semibold uppercase tracking-wider">Address</dt>
                        <dd class="text-sm font-bold text-content-heading mt-1">{{ $addressDisplay }}</dd>
                    </div>
                </dl>

                <div class="grid grid-cols-3 gap-4 pt-2 border-t border-surface-border text-center">
                    <div class="bg-brand-tint-light rounded-xl p-3">
                        <p class="text-xs font-semibold text-content-body">Active Loans</p>
                        <p class="text-lg font-bold font-mono text-brand-primary mt-0.5">{{ $circulationStats['borrowed'] ?? 0 }}</p>
                    </div>
                    <div class="bg-brand-secondary/10 rounded-xl p-3">
                        <p class="text-xs font-semibold text-brand-secondary">Overdue</p>
                        <p class="text-lg font-bold font-mono text-brand-secondary mt-0.5">{{ $circulationStats['overdue'] ?? 0 }}</p>
                    </div>
                    <div class="bg-surface-canvas border border-surface-border rounded-xl p-3">
                        <p class="text-xs font-semibold text-content-body">Returned</p>
                        <p class="text-lg font-bold font-mono text-content-heading mt-0.5">{{ $circulationStats['returned'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @else
            <!-- Public OPAC Catalog Search & Single Combobox Filter -->
            <div class="bg-surface-card border border-surface-border rounded-2xl p-4 sm:p-5 shadow-2xs grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="opac-search" class="block text-xs font-semibold uppercase tracking-wider text-content-body mb-1.5">
                        Search Catalog
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
                        Asset Type
                    </label>

                    <div class="relative">
                        <input
                            id="opac-asset-type-input"
                            type="text"
                            wire:model.live.debounce.300ms="assetTypeSearch"
                            @focus="open = true"
                            @click="open = true"
                            placeholder="All Asset Types"
                            autocomplete="off"
                            role="combobox"
                            aria-expanded="open"
                            class="w-full rounded-xl bg-surface-canvas border border-surface-border pl-3.5 pr-9 py-2.5 text-sm text-content-heading placeholder:text-content-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                        />

                        <button
                            type="button"
                            @click="open = !open"
                            aria-label="Toggle asset type options"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-content-muted hover:text-content-heading cursor-pointer"
                        >
                            <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform" ::class="open ? 'rotate-180' : ''" aria-hidden="true" />
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            x-transition
                            class="absolute z-20 mt-1.5 w-full rounded-xl bg-surface-card border border-surface-border shadow-lg max-h-60 overflow-y-auto"
                        >
                            <button
                                type="button"
                                wire:click="selectAssetType('all')"
                                @click="open = false"
                                class="w-full text-left px-3.5 py-2 text-xs font-semibold text-content-heading hover:bg-brand-tint-light cursor-pointer focus:outline-none focus-visible:bg-brand-tint-light {{ $assetTypeFilter === 'all' ? 'bg-brand-tint text-brand-primary' : '' }}"
                            >
                                All Asset Types
                            </button>

                            @forelse ($assetTypeOptions as $type)
                                <button
                                    type="button"
                                    wire:click="selectAssetType('{{ $type->id }}')"
                                    @click="open = false"
                                    class="w-full text-left px-3.5 py-2 text-xs font-semibold text-content-heading hover:bg-brand-tint-light cursor-pointer focus:outline-none focus-visible:bg-brand-tint-light {{ (string) $assetTypeFilter === (string) $type->id ? 'bg-brand-tint text-brand-primary' : '' }}"
                                >
                                    {{ \Illuminate\Support\Str::title($type->name) }}
                                </button>
                            @empty
                                <div class="px-3.5 py-2 text-xs text-content-muted">
                                    No matching asset types
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            @if ($catalogs->isEmpty())
                <div class="bg-surface-card border border-surface-border rounded-2xl p-8 text-center">
                    <p class="text-sm font-semibold text-content-heading">No catalog items found</p>
                    <p class="text-xs text-content-body mt-1">Try adjusting your search term or asset type filter.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($catalogs as $catalog)
                        @php
                            $totalCopies = (int) ($catalog->total_copies ?? 0);
                            $availableCopies = (int) ($catalog->available_copies ?? 0);
                            $storedCopies = (int) ($catalog->stored_copies ?? 0);
                            $unaccessionedCopies = (int) ($catalog->unaccessioned_copies ?? 0);
                            $catalogTitle = filled($catalog->title)
                                ? \Illuminate\Support\Str::title($catalog->title)
                                : 'N/A';
                            $authorName = filled($catalog->author?->name)
                                ? \Illuminate\Support\Str::title($catalog->author->name)
                                : 'N/A';
                            $publisherName = filled($catalog->publisher?->name)
                                ? \Illuminate\Support\Str::title($catalog->publisher->name)
                                : 'N/A';
                            $copyrightYear = $catalog->publication_year ?: 'N/A';
                        @endphp
                        <div class="bg-surface-card border border-surface-border rounded-2xl p-5 shadow-2xs flex flex-col justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-full bg-brand-tint px-2.5 py-0.5 text-xs font-semibold text-brand-primary">
                                        {{ filled($catalog->assetType?->name) ? \Illuminate\Support\Str::title($catalog->assetType->name) : 'General' }}
                                    </span>

                                    @if ($availableCopies > 0)
                                        <span class="inline-flex items-center rounded-full bg-brand-tint-light px-2.5 py-0.5 text-xs font-semibold text-brand-primary">
                                            {{ $availableCopies }} Available
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-brand-secondary/15 px-2.5 py-0.5 text-xs font-semibold text-brand-secondary">
                                            Unavailable
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-base font-bold text-content-heading line-clamp-2">
                                    {{ $catalogTitle }}
                                </h2>

                                <p class="text-xs text-content-body truncate">
                                    Author: {{ $authorName }}
                                </p>

                                <p class="text-xs text-content-body truncate">
                                    Publisher: {{ $publisherName }} &bull; Copyright: {{ $copyrightYear }}
                                </p>

                                @if ($catalog->isbn_issn)
                                    <p class="text-xs text-content-muted truncate">
                                        ISBN/ISSN: {{ $catalog->isbn_issn }}
                                    </p>
                                @endif
                            </div>

                            <div class="pt-3 border-t border-surface-border space-y-1.5 text-xs font-semibold">
                                <div class="flex items-center justify-between text-content-body">
                                    <span>Total Copies: {{ $totalCopies }}</span>
                                    <span class="text-brand-primary">Available: {{ $availableCopies }}</span>
                                </div>
                                <div class="flex items-center justify-between text-content-muted">
                                    <span>Stored: {{ $storedCopies }}</span>
                                    <span>Unaccession: {{ $unaccessionedCopies }}</span>
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
