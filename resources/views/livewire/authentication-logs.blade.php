<?php // resources/views/livewire/authentication-logs.blade.php ?>
<div class="space-y-6 text-content-body">
    {{-- Header Card with Inline Search, Filters & Export Actions --}}
    <section aria-label="Authentication logs header and filters" class="bg-surface-card p-5 sm:p-6 rounded-2xl border border-surface-border shadow-2xs flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-content-heading tracking-tight">Authentication Logs</h1>
            <p class="text-xs text-content-muted mt-0.5">Audit log of login attempts, successes, failures, and logouts across the platform.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Search Input --}}
            <div class="relative flex-1 sm:flex-initial sm:w-64">
                <label for="auth-logs-search" class="sr-only">Search email, user name, or IP address</label>
                <input
                    id="auth-logs-search"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    wire:keydown.escape="$set('search', '')"
                    placeholder="Search email, user, or IP..."
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-surface-border bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                >
                <x-heroicon-o-magnifying-glass wire:loading.remove wire:target="search" class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
                <span wire:loading wire:target="search" class="animate-spin h-4 w-4 border-2 border-brand-primary border-t-transparent rounded-full absolute left-3 top-2.5 inline-block"></span>
            </div>

            {{-- Event Filter --}}
            <div>
                <label for="auth-event-filter" class="sr-only">Filter authentication event</label>
                <select
                    id="auth-event-filter"
                    wire:model.live="eventFilter"
                    class="py-2 px-3 text-xs font-medium rounded-xl border border-surface-border bg-surface-card text-content-heading focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition cursor-pointer"
                >
                    <option value="all">All Event Types</option>
                    <option value="login_success">Login Success</option>
                    <option value="login_failed">Login Failed</option>
                    <option value="logout">Logout</option>
                    <option value="lockout">Lockout</option>
                </select>
            </div>

            {{-- Date Range Filter --}}
            <div>
                <label for="auth-date-range" class="sr-only">Filter by date range</label>
                <select
                    id="auth-date-range"
                    wire:model.live="dateRange"
                    class="py-2 px-3 text-xs font-medium rounded-xl border border-surface-border bg-surface-card text-content-heading focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition cursor-pointer"
                >
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="7_days">Last 7 Days</option>
                    <option value="30_days">Last 30 Days</option>
                </select>
            </div>

            @if ($search !== '' || $eventFilter !== 'all' || $dateRange !== 'all')
                <button
                    type="button"
                    wire:click="resetFilters"
                    wire:target="resetFilters"
                    class="px-3 py-2 text-xs font-medium rounded-xl border border-surface-border bg-surface-canvas text-content-body hover:text-content-heading hover:bg-surface-card transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                >
                    Reset
                </button>
            @endif

            {{-- Export Dropdown --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                <button
                    type="button"
                    @click="open = !open"
                    aria-haspopup="menu"
                    :aria-expanded="open.toString()"
                    class="px-3.5 py-2 text-xs font-semibold bg-brand-primary text-white rounded-xl hover:bg-brand-primary-hover transition flex items-center gap-1.5 shadow-2xs cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
                >
                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                    <span>Export</span>
                    <x-heroicon-o-chevron-down class="w-3.5 h-3.5 transition-transform duration-200" ::class="{ 'rotate-180': open }" aria-hidden="true" />
                </button>

                <div
                    x-show="open"
                    x-cloak
                    role="menu"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-40 bg-surface-card rounded-xl shadow-lg border border-surface-border p-1 z-30 text-xs"
                >
                    <button
                        type="button"
                        role="menuitem"
                        wire:click="exportExcel"
                        wire:target="exportExcel"
                        wire:loading.attr="disabled"
                        @click="open = false"
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-brand-tint-light text-brand-primary font-medium flex items-center gap-2 cursor-pointer transition disabled:opacity-50"
                    >
                        <x-heroicon-o-table-cells class="w-4 h-4 shrink-0" aria-hidden="true" />
                        <span>Export Excel</span>
                    </button>
                    <button
                        type="button"
                        role="menuitem"
                        wire:click="exportPdf"
                        wire:target="exportPdf"
                        wire:loading.attr="disabled"
                        @click="open = false"
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-brand-secondary/10 text-brand-secondary font-medium flex items-center gap-2 cursor-pointer transition disabled:opacity-50"
                    >
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 shrink-0" aria-hidden="true" />
                        <span>Export PDF</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Row --}}
    <section aria-label="Authentication metrics" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Total Auth Events</span>
                <span class="text-xl font-bold font-mono text-content-heading mt-0.5 block">{{ number_format($stats['total_logs']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-shield-check class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Successful Logins</span>
                <span class="text-xl font-bold font-mono text-brand-primary mt-0.5 block">{{ number_format($stats['login_success']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-key class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Failed Attempts</span>
                <span class="text-xl font-bold font-mono text-brand-secondary mt-0.5 block">{{ number_format($stats['login_failed']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-secondary/15 text-brand-secondary flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Lockouts</span>
                <span class="text-xl font-bold font-mono text-brand-secondary mt-0.5 block">{{ number_format($stats['lockouts']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-secondary/15 text-brand-secondary flex items-center justify-center shrink-0">
                <x-heroicon-o-no-symbol class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>
    </section>

    {{-- Main Table Card --}}
    <section aria-label="Authentication audit log table" class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-content-body">
                <thead class="bg-surface-canvas/70 text-[11px] font-semibold uppercase tracking-wider text-content-muted border-b border-surface-border">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 whitespace-nowrap">User / Targeted Email</th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">Event Status</th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">IP Address</th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">Device (Browser / OS)</th>
                        <th scope="col" class="px-5 py-3.5 text-right whitespace-nowrap">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border/60 bg-surface-card">
                    @forelse ($logs as $log)
                        @php
                            $displayUser = $log->user
                                ? (method_exists($log->user, 'getFullNameAttribute') && filled($log->user->full_name)
                                    ? $log->user->full_name
                                    : ($log->user->name ?? 'Registered User'))
                                : 'Unregistered User';
                        @endphp
                        <tr wire:key="auth-log-{{ $log->id }}" class="hover:bg-surface-canvas/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-content-heading">{{ $displayUser }}</div>
                                <div class="text-[11px] text-content-muted font-mono mt-0.5">{{ $log->email ?? '—' }}</div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @switch($log->event)
                                    @case('login_success')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-brand-tint-light text-brand-primary">
                                            Success
                                        </span>
                                        @break
                                    @case('login_failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-brand-secondary/15 text-brand-secondary">
                                            Failed
                                        </span>
                                        @break
                                    @case('logout')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-surface-canvas text-content-body">
                                            Logout
                                        </span>
                                        @break
                                    @case('lockout')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-brand-secondary/20 text-brand-secondary">
                                            Lockout
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-surface-canvas text-content-heading">
                                            {{ $log->event ?? 'Event' }}
                                        </span>
                                @endswitch
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-content-body">
                                {{ $log->ip_address ?? '—' }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap" title="{{ $log->user_agent }}">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-surface-canvas border border-surface-border text-content-heading">
                                    {{ $log->device_browser_os ?? 'Unknown' }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap font-mono text-content-muted">
                                {{ $log->logged_at ? $log->logged_at->format('M d, Y H:i:s') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 px-4 text-content-muted">
                                No authentication logs match your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($logs, 'total') && $logs->total() > 0)
            <div class="px-5 py-3.5 bg-surface-canvas/50 border-t border-surface-border flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-content-muted">
                <div>
                    Showing <span class="font-semibold font-mono text-content-heading">{{ $logs->firstItem() }}</span>
                    to <span class="font-semibold font-mono text-content-heading">{{ $logs->lastItem() }}</span>
                    of <span class="font-semibold font-mono text-content-heading">{{ $logs->total() }}</span> results
                </div>
                @if ($logs->hasPages())
                    <div>
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        @endif
    </section>
</div>
