{{-- resources/views/livewire/patron-records.blade.php --}}
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-5 text-content-body">
    {{-- Header --}}
    <section aria-label="Patron records header" class="flex flex-col gap-4 rounded-2xl border border-surface-border bg-surface-card px-6 py-5 shadow-2xs sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-content-heading">Patron History</h1>
            <p class="mt-0.5 text-xs text-content-muted">Search patrons, check borrowing history, and manage account status.</p>
        </div>
    </section>

    {{-- Main Split View --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- LEFT PANEL: Patron Directory (5 Columns) --}}
        <section aria-label="Patron directory" class="lg:col-span-5 bg-surface-card p-5 rounded-2xl border border-surface-border shadow-2xs space-y-4">
            <div class="space-y-3">
                <h2 class="text-xs font-bold text-content-heading uppercase tracking-wider">Patron Directory</h2>

                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <label for="patron-directory-search" class="sr-only">Search patron name or ID number</label>
                        <input
                            id="patron-directory-search"
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            wire:keydown.escape="$set('search', '')"
                            placeholder="Search patron name or student/employee #..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-surface-border bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                        >
                        <x-heroicon-o-magnifying-glass wire:loading.remove wire:target="search" class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
                        <span wire:loading wire:target="search" class="animate-spin h-4 w-4 border-2 border-brand-primary border-t-transparent rounded-full absolute left-3 top-2.5 inline-block"></span>
                    </div>

                    <div>
                        <label for="patron-status-filter" class="sr-only">Filter account status</label>
                        <select
                            id="patron-status-filter"
                            wire:model.live="statusFilter"
                            class="py-2 px-3 text-xs rounded-xl border border-surface-border bg-surface-canvas text-content-heading focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                        >
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Patron Cards List --}}
            <div class="space-y-2 max-h-[600px] overflow-y-auto pr-1">
                @forelse ($patrons as $p)
                    @php
                        $isSelected = $selectedPatronId === $p->id;
                        $typeLower = strtolower(trim($p->patronType?->name ?? ''));
                        $idLabel = $typeLower === 'student' ? 'Student #' : 'Employee #';
                        $pMiddle = $p->middle_name ? mb_substr(trim($p->middle_name), 0, 1) . '.' : '';
                        $pFullName = Str::title(trim("{$p->first_name} {$pMiddle} {$p->last_name}")) . ($p->suffix ? ' ' . strtoupper(trim($p->suffix)) : '');
                    @endphp
                    <div
                        wire:key="patron-card-{{ $p->id }}"
                        wire:click="selectPatron({{ $p->id }})"
                        role="button"
                        tabindex="0"
                        @keydown.enter.prevent="$wire.selectPatron({{ $p->id }})"
                        @keydown.space.prevent="$wire.selectPatron({{ $p->id }})"
                        class="p-3.5 rounded-xl border transition cursor-pointer flex items-center justify-between gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $isSelected ? 'border-brand-primary bg-brand-tint-light shadow-2xs' : 'border-surface-border bg-surface-canvas/50 hover:border-brand-primary/40 hover:bg-surface-canvas' }}"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl font-bold flex items-center justify-center text-sm shrink-0 {{ $isSelected ? 'bg-brand-primary text-white' : 'bg-brand-tint text-brand-primary' }}">
                                {{ mb_strtoupper(mb_substr($p->first_name ?? 'P', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs font-bold text-content-heading truncate">{{ $pFullName }}</h3>
                                <p class="text-[11px] text-content-muted font-mono truncate">{{ $idLabel }}: {{ $p->school_id }}</p>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-surface-card border border-surface-border text-content-body">
                                        {{ $p->patronType?->name ? Str::title($p->patronType->name) : 'Unknown' }}
                                    </span>
                                    <span class="text-[10px] font-semibold {{ $p->status === 'active' ? 'text-brand-primary' : 'text-brand-secondary' }}">
                                        &bull; {{ Str::title((string) $p->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            @if (($p->active_loans_count ?? 0) > 0)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-brand-secondary/15 text-brand-secondary">
                                    {{ $p->active_loans_count }} Active Loan{{ $p->active_loans_count > 1 ? 's' : '' }}
                                </span>
                            @else
                                <span class="text-[10px] text-content-muted">No active loans</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 px-4 text-xs text-content-muted border border-dashed border-surface-border rounded-xl bg-surface-canvas/40">
                        No patron found matching your search.
                    </div>
                @endforelse
            </div>

            @if ($patrons->hasPages())
                <div class="pt-1">
                    {{ $patrons->links() }}
                </div>
            @endif
        </section>

        {{-- RIGHT PANEL: Patron Transaction Details (7 Columns) --}}
        <div class="lg:col-span-7 space-y-5">
            @if ($selectedPatron)
                @php
                    $selTypeLower = strtolower(trim($selectedPatron->patronType?->name ?? ''));
                    $selIdLabel = $selTypeLower === 'student' ? 'Student #' : 'Employee #';
                    $selMiddle = $selectedPatron->middle_name ? mb_substr(trim($selectedPatron->middle_name), 0, 1) . '.' : '';
                    $selFullName = Str::title(trim("{$selectedPatron->first_name} {$selMiddle} {$selectedPatron->last_name}")) . ($selectedPatron->suffix ? ' ' . strtoupper(trim($selectedPatron->suffix)) : '');
                @endphp

                {{-- Patron Summary Card --}}
                <section aria-label="Selected patron summary" class="bg-surface-card p-6 rounded-2xl border border-surface-border shadow-2xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 border-surface-border/70">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-brand-primary text-white font-bold flex items-center justify-center text-lg shadow-2xs shrink-0">
                                {{ mb_strtoupper(mb_substr($selectedPatron->first_name ?? 'P', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-base sm:text-lg font-bold text-content-heading truncate">{{ $selFullName }}</h2>
                                <p class="text-xs text-content-muted truncate">
                                    {{ $selIdLabel }}: <strong class="text-brand-primary font-mono">{{ $selectedPatron->school_id }}</strong> &bull;
                                    Email: <span class="text-content-body">{{ $selectedPatron->email ?? 'N/A' }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-start sm:self-center shrink-0">
                            <button
                                type="button"
                                wire:click="togglePatronStatus({{ $selectedPatron->id }})"
                                wire:target="togglePatronStatus({{ $selectedPatron->id }})"
                                wire:loading.attr="disabled"
                                class="px-3.5 py-1.5 text-xs font-semibold rounded-xl border transition cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $selectedPatron->status === 'active' ? 'border-brand-secondary/35 text-brand-secondary bg-brand-secondary/10 hover:bg-brand-secondary/15' : 'border-brand-primary/30 text-brand-primary bg-brand-tint-light hover:bg-brand-tint' }}"
                            >
                                {{ $selectedPatron->status === 'active' ? 'Deactivate Account' : 'Activate Account' }}
                            </button>
                            <button
                                type="button"
                                wire:click="clearSelectedPatron"
                                aria-label="Close patron details"
                                class="px-2.5 py-1.5 text-xs font-semibold rounded-xl border border-surface-border bg-surface-canvas text-content-muted hover:text-content-heading transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                            >&times;</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="bg-surface-canvas p-3 rounded-xl border border-surface-border">
                            <span class="text-content-muted text-[11px] font-semibold block">Type</span>
                            <strong class="text-content-heading mt-0.5 block">{{ $selectedPatron->patronType?->name ? Str::title($selectedPatron->patronType->name) : 'N/A' }}</strong>
                        </div>
                        <div class="bg-surface-canvas p-3 rounded-xl border border-surface-border">
                            <span class="text-content-muted text-[11px] font-semibold block">Grade / Section</span>
                            <strong class="text-content-heading mt-0.5 block">
                                {{ $selectedPatron->gradeLevel?->name ? Str::title($selectedPatron->gradeLevel->name) : '—' }} {{ $selectedPatron->section?->name ? '(' . Str::title($selectedPatron->section->name) . ')' : '' }}
                            </strong>
                        </div>
                        <div class="bg-surface-canvas p-3 rounded-xl border border-surface-border">
                            <span class="text-content-muted text-[11px] font-semibold block">Account Status</span>
                            <span class="font-bold mt-0.5 block {{ $selectedPatron->status === 'active' ? 'text-brand-primary' : 'text-brand-secondary' }}">
                                {{ Str::title((string) $selectedPatron->status) }}
                            </span>
                        </div>
                    </div>
                </section>

                {{-- Circulation History Table --}}
                <section aria-label="Patron circulation history" class="bg-surface-card p-6 rounded-2xl border border-surface-border shadow-2xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b pb-4 border-surface-border/70 gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-content-heading">Patron History Log</h3>
                            <p class="text-xs text-content-muted">Complete record of items borrowed by this patron.</p>
                        </div>

                        <div>
                            <label for="patron-loan-filter" class="sr-only">Filter borrowing history</label>
                            <select
                                id="patron-loan-filter"
                                wire:model.live="loanFilter"
                                class="px-3 py-1.5 rounded-xl border border-surface-border bg-surface-canvas text-xs font-medium text-content-heading focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                            >
                                <option value="all">All Records</option>
                                <option value="active">Active Borrowed</option>
                                <option value="overdue">Overdue</option>
                                <option value="returned">Returned</option>
                            </select>
                        </div>
                    </div>

                    <div class="rounded-xl border border-surface-border overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left border-collapse text-xs text-content-body">
                                <thead>
                                    <tr class="bg-surface-canvas border-b border-surface-border text-[11px] font-semibold uppercase tracking-wider text-content-muted">
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Accession #</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">ISBN</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Book Title</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Author</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Type</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Borrowed Date</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Due Date</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Returned Date</th>
                                        <th scope="col" class="px-4 py-3 whitespace-nowrap">Fine / Penalty</th>
                                        <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-surface-border/70 bg-surface-card">
                                    @forelse ($patronLoans as $loan)
                                        @php
                                            $loanStatusLower = strtolower((string) ($loan->status ?? ''));
                                            $isOverdue = $loanStatusLower === 'borrowed' && $loan->due_at && $loan->due_at->isPast();
                                            $fineVal = (float) ($loan->fine_amount ?? 0);
                                        @endphp
                                        <tr wire:key="patron-loan-{{ $loan->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                                            <td class="px-4 py-3 font-mono text-[11px] font-semibold text-brand-primary whitespace-nowrap">
                                                {{ $loan->accession?->accession_number ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 font-mono text-[11px] text-content-muted whitespace-nowrap">
                                                {{ $loan->accession?->catalog?->isbn_issn ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 font-semibold text-content-heading whitespace-nowrap">
                                                {{ $loan->accession?->catalog?->title ? Str::title($loan->accession->catalog->title) : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-content-body whitespace-nowrap">
                                                {{ $loan->accession?->catalog?->author?->name ? Str::title($loan->accession->catalog->author->name) : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-content-body whitespace-nowrap">
                                                {{ $loan->accession?->catalog?->assetType?->name ? Str::title($loan->accession->catalog->assetType->name) : '—' }}
                                            </td>
                                            <td class="px-4 py-3 font-mono text-[11px] text-content-body whitespace-nowrap">
                                                {{ $loan->borrowed_at ? $loan->borrowed_at->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 font-mono text-[11px] whitespace-nowrap {{ $isOverdue ? 'font-bold text-brand-secondary' : 'text-content-body' }}">
                                                {{ $loan->due_at ? $loan->due_at->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 font-mono text-[11px] text-content-body whitespace-nowrap">
                                                {{ $loan->returned_at ? $loan->returned_at->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 font-mono text-[11px] font-semibold {{ $fineVal > 0 ? 'text-brand-secondary' : 'text-content-muted' }} whitespace-nowrap">
                                                ₱{{ number_format($fineVal, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                                @if (in_array($loanStatusLower, ['returned', 'lost'], true))
                                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-tint-light text-brand-primary uppercase tracking-wider">RETURNED</span>
                                                @elseif ($isOverdue)
                                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-secondary/15 text-brand-secondary uppercase tracking-wider">OVERDUE</span>
                                                @else
                                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-tint text-brand-primary uppercase tracking-wider">ON-LOAN</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-4 py-10 text-center">
                                                <p class="text-xs font-semibold text-content-heading">No borrowing history found</p>
                                                <p class="text-[11px] text-content-muted mt-1">Try changing the loan filter to view more records for this patron.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($patronLoans->hasPages())
                        <div class="pt-1">
                            {{ $patronLoans->links() }}
                        </div>
                    @endif
                </section>
            @else
                {{-- Empty State Placeholder --}}
                <div class="bg-surface-card p-12 text-center rounded-2xl border border-surface-border text-content-muted space-y-3 shadow-2xs">
                    <div class="mx-auto w-12 h-12 rounded-2xl bg-brand-tint-light border border-brand-primary/25 flex items-center justify-center">
                        <x-heroicon-o-user class="w-6 h-6 text-brand-primary" aria-hidden="true" />
                    </div>
                    <h3 class="text-sm font-bold text-content-heading">No Patron Selected</h3>
                    <p class="text-xs text-content-muted max-w-sm mx-auto">
                        Select a patron from the directory list on the left to view their account details and circulation history.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
