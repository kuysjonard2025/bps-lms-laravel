{{-- resources/views/livewire/dashboard.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-6">

    {{-- Top Page Header --}}
    <section aria-label="Dashboard overview header" class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-semibold text-content-heading tracking-tight">Library Dashboard</h1>
            <p class="text-xs text-content-body mt-0.5">
                Overview of accessioned holdings, active loans, overdue alerts, and recent transactions
            </p>
        </div>
        <span class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-xl bg-surface-canvas text-content-body border border-surface-border/80 self-start sm:self-auto">
            <span class="w-2 h-2 rounded-full bg-brand-primary" aria-hidden="true"></span>
            {{ now()->format('M d, Y') }}
        </span>
    </section>

    {{-- Key Stats / Metrics Grid --}}
    <section aria-label="Key library metrics" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {{-- Total Accessioned --}}
        <div class="bg-surface-card p-5 rounded-2xl border border-surface-border hover:border-brand-primary/40 shadow-2xs flex items-start justify-between gap-4 transition-colors">
            <div>
                <p class="text-[11px] font-semibold text-content-muted uppercase tracking-wider">Total Accessioned</p>
                <p class="text-2xl font-bold text-content-heading mt-1.5 tabular-nums">{{ number_format($totalAccessioned) }}</p>
                <p class="text-[11px] font-medium text-brand-secondary mt-1.5 tabular-nums">
                    Unaccessioned: {{ number_format($unaccessionedCount) }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light text-brand-primary font-semibold flex items-center justify-center shrink-0" aria-hidden="true">
                📦
            </div>
        </div>

        {{-- Active Borrows --}}
        <div class="bg-surface-card p-5 rounded-2xl border border-surface-border hover:border-brand-primary/40 shadow-2xs flex items-start justify-between gap-4 transition-colors">
            <div>
                <p class="text-[11px] font-semibold text-content-muted uppercase tracking-wider">Active Borrows</p>
                <p class="text-2xl font-bold text-brand-primary mt-1.5 tabular-nums">{{ number_format($activeBorrows) }}</p>
                <p class="text-[11px] text-content-body mt-1.5">Currently checked out</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint text-brand-primary font-semibold flex items-center justify-center shrink-0" aria-hidden="true">
                📖
            </div>
        </div>

        {{-- Overdue Assets --}}
        <div class="bg-surface-card p-5 rounded-2xl border border-surface-border hover:border-brand-secondary/40 shadow-2xs flex items-start justify-between gap-4 transition-colors">
            <div>
                <p class="text-[11px] font-semibold text-content-muted uppercase tracking-wider">Overdue Assets</p>
                <p class="text-2xl font-bold text-brand-secondary mt-1.5 tabular-nums">{{ number_format($overdueBooks) }}</p>
                <p class="text-[11px] text-content-body mt-1.5">Past scheduled due date</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-secondary/15 text-brand-secondary font-semibold flex items-center justify-center shrink-0" aria-hidden="true">
                ⚠️
            </div>
        </div>

        {{-- Total Borrowers --}}
        <div class="bg-surface-card p-5 rounded-2xl border border-surface-border hover:border-brand-primary/40 shadow-2xs flex items-start justify-between gap-4 transition-colors">
            <div>
                <p class="text-[11px] font-semibold text-content-muted uppercase tracking-wider">Total Borrowers</p>
                <p class="text-2xl font-bold text-content-heading mt-1.5 tabular-nums">{{ number_format($totalPatrons) }}</p>
                <p class="text-[11px] text-content-body mt-1.5">Registered patrons</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light text-brand-primary font-semibold flex items-center justify-center shrink-0" aria-hidden="true">
                👥
            </div>
        </div>
    </section>

    {{-- Graphical Section: Side-by-Side Charts --}}
    <section aria-label="Accession analytics" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <livewire:dashboard.accession-chart lazy />
        <livewire:dashboard.accession-trend-chart lazy />
    </section>

    {{-- Layout Grid: Overdue Alerts (1/3) & Recent Transactions (2/3) --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">

        {{-- Overdue Alerts Section --}}
        <section
            aria-label="Overdue alerts"
            class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs p-5 flex flex-col"
        >
            <div class="flex items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-base font-semibold text-content-heading flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-secondary animate-pulse" aria-hidden="true"></span>
                        Overdue Alerts
                    </h2>
                    <p class="text-xs text-content-body mt-0.5">Priority unreturned loans</p>
                </div>
                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-brand-secondary/15 text-brand-secondary whitespace-nowrap tabular-nums">
                    {{ $overdueAlerts->count() }} Urgent
                </span>
            </div>

            <div class="space-y-3 overflow-y-auto max-h-[420px] pr-1">
                @forelse ($overdueAlerts as $alert)
                    @php
                        $alertBorrower = trim(($alert->patron?->first_name ?? '') . ' ' . ($alert->patron?->last_name ?? ''));
                        $alertTitle = $alert->accession?->catalog?->title ? Str::title($alert->accession->catalog->title) : 'Unknown Title';
                    @endphp
                    <article class="p-3.5 rounded-xl border border-brand-secondary/20 bg-brand-secondary/5 flex flex-col justify-between gap-2 transition-colors hover:border-brand-secondary/40 hover:bg-brand-secondary/10">
                        <div>
                            <p class="text-xs font-semibold text-content-heading">
                                {{ $alertBorrower !== '' ? Str::title($alertBorrower) : 'Borrower' }}
                            </p>
                            <p class="text-xs text-content-body truncate mt-0.5" title="{{ $alertTitle }}">
                                {{ $alertTitle }}
                            </p>
                            @if ($alert->accession?->accession_number)
                                <p class="text-[11px] font-mono text-content-muted mt-0.5">
                                    #{{ strtoupper($alert->accession->accession_number) }}
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between gap-2 text-[11px] text-brand-secondary font-medium pt-2 border-t border-surface-border/70">
                            <span>Due: {{ $alert->due_at ? $alert->due_at->format('M d, Y') : 'N/A' }}</span>
                            <span>{{ $alert->due_at ? $alert->due_at->diffForHumans() : '' }}</span>
                        </div>
                    </article>
                @empty
                    <div class="text-center py-12 px-4 rounded-xl bg-surface-canvas border border-dashed border-surface-border">
                        <p class="text-xs font-semibold text-content-heading">No overdue assets right now</p>
                        <p class="text-[11px] text-content-muted mt-1">All active loans are within their scheduled return window.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Recent Activity / Transactions Section --}}
        <section
            aria-label="Recent transactions"
            x-data="{
                statusOptions: [
                    { value: 'all', label: 'All' },
                    { value: 'borrowed', label: 'Borrowed' },
                    { value: 'overdue', label: 'Overdue' },
                    { value: 'returned', label: 'Returned' },
                ]
            }"
            class="xl:col-span-2 bg-surface-card rounded-2xl border border-surface-border shadow-2xs p-5 space-y-4"
        >
            <div class="flex flex-col gap-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-content-heading">Recent Transactions</h2>
                        <p class="text-xs text-content-body">Filter by status or search accession number, title, author, borrower, or ID</p>
                    </div>

                    {{-- Status Filter Pills --}}
                    <div role="group" aria-label="Filter transactions by status" class="inline-flex items-center rounded-xl bg-surface-canvas p-1 border border-surface-border/80 self-start sm:self-auto shrink-0">
                        <template x-for="opt in statusOptions" :key="opt.value">
                            <button
                                type="button"
                                @click="$wire.set('statusFilter', opt.value)"
                                :class="$wire.statusFilter === opt.value
                                    ? 'bg-brand-primary text-white shadow-2xs'
                                    : 'text-content-body hover:text-content-heading'"
                                class="px-3 py-1 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                x-text="opt.label"
                            ></button>
                        </template>
                    </div>
                </div>

                {{-- Search Bar --}}
                <div class="relative w-full">
                    <label for="dashboard-transaction-search" class="sr-only">Search transactions</label>
                    <input
                        id="dashboard-transaction-search"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search accession #, title, author, borrower name, or ID..."
                        class="w-full pl-9 pr-9 py-2 text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                    />
                    <span class="absolute left-3 top-2.5 text-content-muted text-xs pointer-events-none" aria-hidden="true">🔍</span>

                    @if ($search !== '' || $statusFilter !== 'all')
                        <button
                            type="button"
                            wire:click="resetFilters"
                            wire:loading.attr="disabled"
                            aria-label="Clear search and status filters"
                            class="absolute right-2.5 top-2 text-xs font-semibold text-content-muted hover:text-content-heading cursor-pointer"
                        >
                            ✕
                        </button>
                    @endif
                </div>
            </div>

            {{-- Subtle Loading Bar --}}
            <div wire:loading.flex wire:target="search,statusFilter,resetFilters,previousPage,nextPage,gotoPage" class="items-center gap-2 text-xs text-brand-primary font-medium">
                <span class="w-2 h-2 rounded-full bg-brand-primary animate-ping" aria-hidden="true"></span>
                <span>Updating transactions...</span>
            </div>

            {{-- Transactions Table --}}
            <div class="overflow-x-auto border border-surface-border rounded-xl">
                <table class="w-full text-left border-collapse text-xs text-content-body">
                    <thead>
                        <tr class="bg-surface-canvas border-b border-surface-border text-content-muted font-semibold uppercase text-[11px] tracking-wider">
                            <th scope="col" class="p-3 pl-4 whitespace-nowrap">Accession #</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Title</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Author</th>
                            <th scope="col" class="p-3 whitespace-nowrap">ID #</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Borrower Name</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Type</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Borrowed</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Due Date</th>
                            <th scope="col" class="p-3 pr-4 whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border/70">
                        @forelse ($recentTransactions as $transaction)
                            @php
                                $patronTypeName = strtolower((string) ($transaction->patron?->patronType?->name ?? ''));
                                $displayTitle = $transaction->accession?->catalog?->title
                                    ? Str::title($transaction->accession->catalog->title)
                                    : 'Unknown Title';
                                $displayAuthor = $transaction->accession?->catalog?->author?->name
                                    ? Str::title($transaction->accession->catalog->author->name)
                                    : 'N/A';
                                $fullBorrowerName = trim(($transaction->patron?->first_name ?? '') . ' ' . ($transaction->patron?->last_name ?? ''));
                                $displayBorrower = $fullBorrowerName !== '' ? Str::title($fullBorrowerName) : 'N/A';
                                $displayPatronType = $transaction->patron?->patronType?->name
                                    ? Str::title($transaction->patron->patronType->name)
                                    : 'N/A';
                            @endphp
                            <tr class="hover:bg-surface-canvas/70 transition-colors">
                                <td class="p-3 pl-4 uppercase whitespace-nowrap font-mono font-medium text-content-heading">
                                    {{ $transaction->accession?->accession_number ?? 'N/A' }}
                                </td>
                                <td class="p-3 text-content-heading max-w-52 truncate font-medium" title="{{ $displayTitle }}">
                                    {{ $displayTitle }}
                                </td>
                                <td class="p-3 text-content-body max-w-44 truncate" title="{{ $displayAuthor }}">
                                    {{ $displayAuthor }}
                                </td>
                                <td class="p-3 whitespace-nowrap font-mono text-content-muted">
                                    {{ $transaction->patron?->school_id ?? 'N/A' }}
                                </td>
                                <td class="p-3 font-medium text-content-heading whitespace-nowrap">
                                    {{ $displayBorrower }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-surface-canvas text-content-heading border border-surface-border/80">
                                        {{ $displayPatronType }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap text-content-body tabular-nums">
                                    {{ $transaction->borrowed_at ? $transaction->borrowed_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="p-3 whitespace-nowrap text-content-body tabular-nums">
                                    @if ($patronTypeName === 'student')
                                        {{ $transaction->due_at ? $transaction->due_at->format('M d, Y') : 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="p-3 pr-4 whitespace-nowrap">
                                    @if ($transaction->returned_at)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-tint-light text-brand-primary">
                                            Returned
                                        </span>
                                    @elseif ($transaction->due_at && $transaction->due_at->isPast())
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-secondary/15 text-brand-secondary">
                                            Overdue
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-tint text-content-heading">
                                            Borrowed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 px-4">
                                    <p class="text-xs font-semibold text-content-heading">No matching circulation transactions found</p>
                                    <p class="text-[11px] text-content-muted mt-1">Try adjusting your search query or status filter.</p>
                                    @if ($search !== '' || $statusFilter !== 'all')
                                        <button
                                            type="button"
                                            wire:click="resetFilters"
                                            class="mt-3 inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-brand-primary text-white hover:bg-brand-primary-hover transition cursor-pointer"
                                        >
                                            Reset Filters
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Rendering --}}
            <div class="pt-2">
                {{ $recentTransactions->links() }}
            </div>
        </section>

    </div>
</div>
