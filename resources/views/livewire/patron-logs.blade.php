{{-- resources/views/livewire/patron-logs.blade.php --}}
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
@endphp

<div class="space-y-5 text-content-body">
    {{-- Page Header --}}
    <section aria-label="Patron attendance logs header" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between bg-surface-card px-6 py-5 rounded-2xl border border-surface-border shadow-2xs">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-content-heading">Patron Attendance Logs</h1>
            <p class="mt-0.5 text-xs text-content-muted">Monitor real-time library visitation history and manage active patron sessions.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a
                href="{{ route('kiosk.login') }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-primary px-4 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-brand-primary-hover focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20 transition cursor-pointer"
            >
                <x-heroicon-o-qr-code class="h-4 w-4 shrink-0" aria-hidden="true" />
                <span>Kiosk Patron Time In/Out</span>
            </a>

            <div x-data="{ open: false }" class="relative inline-block text-left">
                <button
                    @click="open = !open"
                    @click.outside="open = false"
                    @keydown.escape.window="open = false"
                    type="button"
                    aria-haspopup="menu"
                    :aria-expanded="open.toString()"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-surface-border bg-surface-canvas px-3.5 py-2 text-xs font-semibold text-content-heading shadow-2xs hover:bg-surface-card focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15 transition cursor-pointer"
                >
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4 text-content-muted shrink-0" aria-hidden="true" />
                    <span>Export {{ empty($filterDate) ? '(All Records)' : '' }}</span>
                    <x-heroicon-o-chevron-down class="h-3.5 w-3.5 text-content-muted transition-transform duration-200" ::class="{ 'rotate-180 text-brand-primary': open }" aria-hidden="true" />
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    x-cloak
                    role="menu"
                    class="absolute right-0 z-20 mt-2 w-44 rounded-xl bg-surface-card border border-surface-border p-1 shadow-lg focus:outline-none"
                >
                    <button
                        type="button"
                        role="menuitem"
                        wire:click="exportExcel"
                        wire:target="exportExcel"
                        @click="open = false"
                        wire:loading.attr="disabled"
                        class="group flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-content-body hover:bg-brand-tint-light hover:text-brand-primary transition cursor-pointer disabled:opacity-50"
                    >
                        <x-heroicon-o-table-cells wire:loading.remove wire:target="exportExcel" class="h-4 w-4 text-brand-primary shrink-0" aria-hidden="true" />
                        <span wire:loading wire:target="exportExcel" class="animate-spin h-3.5 w-3.5 border-2 border-brand-primary border-t-transparent rounded-full"></span>
                        <span>Export Excel</span>
                    </button>

                    <button
                        type="button"
                        role="menuitem"
                        wire:click="exportPdf"
                        wire:target="exportPdf"
                        @click="open = false"
                        wire:loading.attr="disabled"
                        class="group flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-content-body hover:bg-brand-secondary/10 hover:text-brand-secondary transition cursor-pointer disabled:opacity-50"
                    >
                        <x-heroicon-o-document-arrow-down wire:loading.remove wire:target="exportPdf" class="h-4 w-4 text-brand-secondary shrink-0" aria-hidden="true" />
                        <span wire:loading wire:target="exportPdf" class="animate-spin h-3.5 w-3.5 border-2 border-brand-secondary border-t-transparent rounded-full"></span>
                        <span>Export PDF</span>
                    </button>
                </div>
            </div>

            <button
                type="button"
                wire:click="$set('showForceCheckoutModal', true)"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-secondary px-4 py-2 text-xs font-semibold text-white shadow-2xs hover:opacity-90 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-secondary/20 transition cursor-pointer disabled:opacity-50"
            >
                <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4 shrink-0" aria-hidden="true" />
                <span>End-of-Day Log Out</span>
            </button>
        </div>
    </section>

    {{-- Daily Stats Cards --}}
    <section aria-label="Daily visitation summary" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-surface-border bg-surface-card p-5 shadow-2xs">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-content-muted">
                    Total Visits {{ !empty($filterDate) ? '(' . Carbon::parse($filterDate)->format('M d, Y') . ')' : '(Today)' }}
                </span>
                <span class="rounded-xl bg-surface-canvas border border-surface-border p-2 text-content-heading shrink-0">
                    <x-heroicon-o-users class="h-4 w-4" aria-hidden="true" />
                </span>
            </div>
            <p class="mt-3 text-2xl font-extrabold font-mono text-content-heading tracking-tight">{{ $totalToday }}</p>
        </div>

        <div class="rounded-2xl border border-brand-primary/30 bg-brand-tint-light/70 p-5 shadow-2xs">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-brand-primary">Currently Inside</span>
                <span class="rounded-xl bg-brand-tint p-2 text-brand-primary shrink-0">
                    <x-heroicon-o-arrow-left-on-rectangle class="h-4 w-4" aria-hidden="true" />
                </span>
            </div>
            <p class="mt-3 text-2xl font-extrabold font-mono text-brand-primary tracking-tight">{{ $currentlyInside }}</p>
        </div>

        <div class="rounded-2xl border border-surface-border bg-surface-card p-5 shadow-2xs">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-content-muted">Logged Out</span>
                <span class="rounded-xl bg-surface-canvas border border-surface-border p-2 text-content-body shrink-0">
                    <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" aria-hidden="true" />
                </span>
            </div>
            <p class="mt-3 text-2xl font-extrabold font-mono text-content-heading tracking-tight">{{ $checkedOutToday }}</p>
        </div>
    </section>

    {{-- Filters & Attendance Logs Table --}}
    <div class="space-y-4">
        <section aria-label="Attendance log filters" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center flex-wrap">
                <div class="relative flex-1 min-w-52 max-w-md">
                    <label for="patron-logs-search" class="sr-only">Search patron name or ID</label>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-heroicon-o-magnifying-glass wire:loading.remove wire:target="search" class="h-4 w-4 text-content-muted" aria-hidden="true" />
                        <span wire:loading wire:target="search" class="animate-spin h-4 w-4 border-2 border-brand-primary border-t-transparent rounded-full inline-block"></span>
                    </div>
                    <input
                        id="patron-logs-search"
                        wire:model.live.debounce.300ms="search"
                        wire:keydown.escape="$set('search', '')"
                        type="text"
                        placeholder="Search patron name or ID..."
                        class="w-full rounded-xl border border-surface-border bg-surface-canvas pl-9 pr-8 py-2 text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                    />
                    @if($search !== '')
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            aria-label="Clear search"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-content-muted hover:text-content-heading text-xs cursor-pointer rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                        >
                            &times;
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <label for="patron-logs-date" class="sr-only">Filter by log date</label>
                    <input
                        id="patron-logs-date"
                        wire:model.live="filterDate"
                        type="date"
                        class="rounded-xl border border-surface-border bg-surface-canvas px-3.5 py-2 text-xs font-mono text-content-heading focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                    />

                    @if($filterDate)
                        <button
                            type="button"
                            wire:click="$set('filterDate', null)"
                            class="px-3 py-2 text-xs font-medium text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                        >
                            Show All Dates
                        </button>
                    @endif
                </div>

                <div>
                    <label for="patron-logs-status" class="sr-only">Filter by log status</label>
                    <select
                        id="patron-logs-status"
                        wire:model.live="filterStatus"
                        class="rounded-xl border border-surface-border bg-surface-canvas px-3.5 py-2 text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                    >
                        <option value="all">All Statuses</option>
                        <option value="inside">Currently Inside</option>
                        <option value="logged_out">Logged Out</option>
                    </select>
                </div>

                @if($search !== '' || !empty($filterDate) || $filterStatus !== 'all')
                    <button
                        type="button"
                        wire:click="clearFilters"
                        wire:target="clearFilters"
                        class="text-xs text-brand-primary hover:underline font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >
                        Reset Filters
                    </button>
                @endif
            </div>
        </section>

        <div class="rounded-2xl border border-surface-border bg-surface-card shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse text-xs text-content-body">
                    <thead>
                        <tr class="bg-surface-canvas border-b border-surface-border text-[11px] font-semibold uppercase tracking-wider text-content-muted">
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Log Date</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Student / Employee #</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Name</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Type</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Department</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Grade</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Section</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Log In Time</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Log Out Time</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Status</th>
                            <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border/70 bg-surface-card">
                        @forelse($logs as $log)
                            @php
                                $patron = $log->patron;
                                $fullName = $patron
                                    ? Str::title(trim("{$patron->first_name} {$patron->middle_name} {$patron->last_name}")) . ($patron->suffix ? ' ' . strtoupper($patron->suffix) : '')
                                    : 'Deleted Patron';
                            @endphp
                            <tr wire:key="patron-log-{{ $log->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                                <td class="px-4 py-3 font-mono text-[11px] font-medium text-content-heading whitespace-nowrap">
                                    {{ Carbon::parse($log->log_date)->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold text-brand-primary whitespace-nowrap">
                                    {{ $patron?->school_id ?: '-' }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-content-heading whitespace-nowrap">
                                    {{ $fullName }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full bg-surface-canvas border border-surface-border px-2.5 py-0.5 text-[11px] font-medium text-content-body">
                                        {{ $patron?->patronType?->name ? Str::title($patron->patronType->name) : 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($patron?->department)
                                        <span class="font-medium text-content-heading">
                                            {{ Str::title($patron->department->name) }}
                                        </span>
                                    @else
                                        <span class="text-content-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-content-body">
                                    @if($patron?->gradeLevel)
                                        {{ Str::title($patron->gradeLevel->name) }}
                                    @else
                                        <span class="text-content-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-content-body">
                                    @if($patron?->section)
                                        {{ Str::title($patron->section->name) }}
                                    @else
                                        <span class="text-content-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px] text-content-heading whitespace-nowrap">
                                    {{ Carbon::parse($log->time_in)->format('h:i:s A') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px] text-content-heading whitespace-nowrap">
                                    @if($log->time_out)
                                        {{ Carbon::parse($log->time_out)->format('h:i:s A') }}
                                    @else
                                        <span class="text-content-muted">--:--:--</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($log->time_out)
                                        <span class="inline-flex items-center rounded-full bg-brand-tint-light px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-primary">
                                            LOGGED OUT
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-tint px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-primary">
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-primary animate-pulse"></span>
                                            INSIDE LIBRARY
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if(! $log->time_out)
                                        <button
                                            type="button"
                                            wire:click="manualCheckOut({{ $log->id }})"
                                            wire:target="manualCheckOut({{ $log->id }})"
                                            wire:confirm="Are you sure you want to manually log out this patron?"
                                            class="text-xs font-semibold text-brand-secondary hover:bg-brand-secondary/10 px-2.5 py-1 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                                        >
                                            Log Out
                                        </button>
                                    @else
                                        <span class="text-xs text-content-muted">Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-10 text-center">
                                    <p class="text-xs font-semibold text-content-heading">No attendance log records found</p>
                                    <p class="text-[11px] text-content-muted mt-1">Adjust your search, date, or status filters to view matching visitation logs.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (method_exists($logs, 'links'))
            <div class="pt-1">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Force Checkout Modal --}}
    @if($showForceCheckoutModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="force-checkout-modal-title"
            @keydown.escape.window="$wire.set('showForceCheckoutModal', false)"
        >
            <div wire:click="$set('showForceCheckoutModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card border border-surface-border rounded-2xl shadow-xl w-full max-w-md z-10 overflow-hidden my-auto p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center justify-center shrink-0">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5" aria-hidden="true" />
                    </div>
                    <h3 id="force-checkout-modal-title" class="text-sm font-bold text-content-heading">End-of-Day Mass Log Out</h3>
                </div>

                <p class="text-xs text-content-body leading-relaxed">
                    Are you sure you want to log out all <strong class="text-content-heading font-mono">{{ $currentlyInside }}</strong> active patrons currently inside the library for <strong class="text-content-heading">{{ Carbon::parse($filterDate ?: now())->format('M d, Y') }}</strong>?
                </p>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-3 border-t border-surface-border/70">
                    <button
                        type="button"
                        wire:click="$set('showForceCheckoutModal', false)"
                        class="w-full sm:w-auto px-3.5 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="checkoutAllActive"
                        wire:target="checkoutAllActive"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-secondary hover:opacity-90 rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <span wire:loading wire:target="checkoutAllActive" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Confirm Log Out</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
