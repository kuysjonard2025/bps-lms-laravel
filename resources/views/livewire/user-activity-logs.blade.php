{{-- resources/views/livewire/user-activity-logs.blade.php --}}
<div class="space-y-6 text-content-body">
    {{-- Header Card with Inline Search, Filters & Export Actions --}}
    <section aria-label="User activity logs header and filters" class="bg-surface-card p-5 sm:p-6 rounded-2xl border border-surface-border shadow-2xs flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-content-heading tracking-tight">User Activity Logs</h1>
            <p class="text-xs text-content-muted mt-0.5">Audit trail tracking created, modified, and deleted data across all system modules.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Search Input --}}
            <div class="relative flex-1 sm:flex-initial sm:w-56">
                <label for="activity-logs-search" class="sr-only">Search user, description, or IP address</label>
                <input
                    id="activity-logs-search"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    wire:keydown.escape="$set('search', '')"
                    placeholder="Search user, description, IP..."
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-surface-border bg-surface-canvas text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                >
                <x-heroicon-o-magnifying-glass wire:loading.remove wire:target="search" class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
                <span wire:loading wire:target="search" class="animate-spin h-4 w-4 border-2 border-brand-primary border-t-transparent rounded-full absolute left-3 top-2.5 inline-block"></span>
            </div>

            {{-- Event Filter --}}
            <div>
                <label for="activity-event-filter" class="sr-only">Filter activity event</label>
                <select
                    id="activity-event-filter"
                    wire:model.live="eventFilter"
                    class="py-2 px-3 text-xs font-medium rounded-xl border border-surface-border bg-surface-card text-content-heading focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition cursor-pointer"
                >
                    <option value="all">All Events</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>
            </div>

            {{-- Searchable Combobox for Modules --}}
            <div
                class="relative flex-1 sm:flex-initial sm:w-48"
                x-data="{
                    open: false,
                    query: @entangle('moduleSearch'),
                    selectedModule: @entangle('moduleFilter'),
                    items: @js($modules->map(fn ($m) => ['value' => (string) $m, 'label' => ucfirst((string) $m)])->values()),
                    get filtered() {
                        const q = (this.query || '').trim().toLowerCase();
                        if (!q || q === 'all modules') return this.items;
                        return this.items.filter(i => i.label.toLowerCase().includes(q) || i.value.toLowerCase().includes(q));
                    },
                    choose(value, label) {
                        this.query = label;
                        this.open = false;
                        $wire.selectModule(value);
                    },
                    clearAll() {
                        this.query = '';
                        this.open = false;
                        $wire.selectModule('all');
                    }
                }"
                @click.outside="open = false"
                @keydown.escape.window="open = false"
            >
                <label for="activity-module-combobox" class="sr-only">Search or select module</label>
                <div class="relative flex items-center">
                    <input
                        id="activity-module-combobox"
                        type="text"
                        role="combobox"
                        aria-autocomplete="list"
                        aria-controls="activity-module-listbox"
                        :aria-expanded="open.toString()"
                        x-model="query"
                        @focus="open = true"
                        @input="open = true"
                        wire:model.live.debounce.300ms="moduleSearch"
                        placeholder="All Modules..."
                        class="w-full py-2 pl-3 pr-14 text-xs font-medium rounded-xl border border-surface-border bg-surface-card text-content-heading placeholder:text-content-muted focus:border-brand-primary focus:outline-none focus:ring-4 focus:ring-brand-primary/15 transition"
                    >
                    <button
                        type="button"
                        x-show="query"
                        x-cloak
                        @click="clearAll()"
                        aria-label="Clear module filter"
                        class="absolute right-7 text-content-muted hover:text-content-heading px-1 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded"
                    >&times;</button>
                    <button
                        type="button"
                        @click="open = !open"
                        aria-label="Toggle module options"
                        class="absolute right-2 text-content-muted hover:text-content-heading p-1 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded"
                    >
                        <x-heroicon-o-chevron-down class="w-3.5 h-3.5 transition-transform duration-200" ::class="{ 'rotate-180 text-brand-primary': open }" aria-hidden="true" />
                    </button>
                </div>

                <div
                    id="activity-module-listbox"
                    role="listbox"
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="absolute right-0 mt-1.5 w-full max-h-60 overflow-y-auto rounded-xl border border-surface-border bg-surface-card shadow-lg z-30 p-1 text-xs"
                >
                    <button
                        type="button"
                        role="option"
                        :aria-selected="(selectedModule === 'all').toString()"
                        @click="choose('all', 'All Modules')"
                        class="w-full text-left px-3 py-2 rounded-lg font-medium transition cursor-pointer flex items-center justify-between"
                        :class="selectedModule === 'all' ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                    >
                        <span>All Modules</span>
                        <span x-show="selectedModule === 'all'" class="text-[10px] font-bold">Selected</span>
                    </button>

                    <template x-for="item in filtered" :key="item.value">
                        <button
                            type="button"
                            role="option"
                            :aria-selected="(selectedModule === item.value).toString()"
                            @click="choose(item.value, item.label)"
                            class="w-full text-left px-3 py-2 rounded-lg transition cursor-pointer flex items-center justify-between mt-0.5"
                            :class="selectedModule === item.value ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                        >
                            <span x-text="item.label"></span>
                            <span x-show="selectedModule === item.value" class="text-[10px] font-bold">Selected</span>
                        </button>
                    </template>

                    <div x-show="filtered.length === 0" class="px-3 py-3 text-center text-content-muted">
                        No matching module.
                    </div>
                </div>
            </div>

            @if ($search !== '' || $eventFilter !== 'all' || $moduleFilter !== 'all')
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
    <section aria-label="Activity metrics" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Total Actions</span>
                <span class="text-xl font-bold font-mono text-content-heading mt-0.5 block">{{ number_format($stats['total_logs']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-clipboard-document-list class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Insertions (Created)</span>
                <span class="text-xl font-bold font-mono text-brand-primary mt-0.5 block">{{ number_format($stats['total_created']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-plus-circle class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Modifications (Updated)</span>
                <span class="text-xl font-bold font-mono text-content-heading mt-0.5 block">{{ number_format($stats['total_updated']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-pencil-square class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Deletions</span>
                <span class="text-xl font-bold font-mono text-brand-secondary mt-0.5 block">{{ number_format($stats['total_deleted']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-secondary/15 text-brand-secondary flex items-center justify-center shrink-0">
                <x-heroicon-o-trash class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>
    </section>

    {{-- Main Activity Log Table Card --}}
    <section aria-label="User activity log table" class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-content-body">
                <thead class="bg-surface-canvas/70 text-[11px] font-semibold uppercase tracking-wider text-content-muted border-b border-surface-border">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 whitespace-nowrap">User</th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">Event</th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">Module</th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">Description</th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">IP Address</th>
                        <th scope="col" class="px-5 py-3.5 text-right whitespace-nowrap">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border/60 bg-surface-card">
                    @forelse ($logs as $log)
                        <tr wire:key="activity-log-{{ $log->id }}" class="hover:bg-surface-canvas/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-content-heading">{{ $log->user?->name ?? 'System/Automated' }}</div>
                                <div class="text-[11px] text-content-muted font-mono mt-0.5">{{ $log->user?->email ?? 'N/A' }}</div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @switch($log->event)
                                    @case('created')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-brand-tint-light text-brand-primary">
                                            Created
                                        </span>
                                        @break
                                    @case('updated')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-brand-tint text-brand-primary">
                                            Updated
                                        </span>
                                        @break
                                    @case('deleted')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-brand-secondary/15 text-brand-secondary">
                                            Deleted
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-surface-canvas text-content-heading">
                                            {{ $log->event ?? 'Event' }}
                                        </span>
                                @endswitch
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-surface-canvas border border-surface-border text-content-heading capitalize">
                                    {{ $log->log_name ?? 'General' }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 text-content-heading">
                                <div>{{ $log->description }}</div>
                                @if (! empty($log->properties))
                                    <details class="mt-1 text-[11px] text-content-muted">
                                        <summary class="hover:text-brand-primary font-medium cursor-pointer select-none">View changed fields</summary>
                                        <pre class="mt-1.5 p-2.5 bg-surface-canvas rounded-xl border border-surface-border text-[10px] font-mono text-content-body overflow-x-auto">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</pre>
                                    </details>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-content-body">
                                {{ $log->ip_address ?? '—' }}
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap font-mono text-content-muted">
                                {{ $log->created_at ? $log->created_at->format('M d, Y H:i:s') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 px-4 text-content-muted">
                                No activity logs recorded yet.
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
