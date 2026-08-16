<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">User Activity Logs</h1>
            <p class="text-xs text-slate-500 mt-1">Audit trail tracking created, modified, and deleted data across all system modules.</p>
        </div>

        {{-- Export Placeholders --}}
        <div class="flex items-center gap-2">
            <button class="px-3 py-2 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition flex items-center gap-1.5">
                <span>📊</span> Export Excel
            </button>
            <button class="px-3 py-2 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded-xl hover:bg-rose-100 transition flex items-center gap-1.5">
                <span>📄</span> Export PDF
            </button>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Actions</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($stats['total_logs']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-bold flex items-center justify-center">📝</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Insertions (Created)</span>
                <span class="text-xl font-bold text-emerald-600">{{ number_format($stats['total_created']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center">➕</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Modifications (Updated)</span>
                <span class="text-xl font-bold text-amber-600">{{ number_format($stats['total_updated']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 font-bold flex items-center justify-center">✏️</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Deletions</span>
                <span class="text-xl font-bold text-rose-600">{{ number_format($stats['total_deleted']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center">🗑️</div>
        </div>
    </div>

    {{-- Main Activity Log Table --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        {{-- Filters Bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto flex-1">
                {{-- Search --}}
                <div class="relative flex-1 min-w-[240px]">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search User, Description, IP..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none"
                    >
                    <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
                </div>

                {{-- Action Filter --}}
                <select wire:model.live="eventFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none">
                    <option value="all">All Events</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>

                {{-- Module Filter --}}
                <select wire:model.live="moduleFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none capitalize">
                    <option value="all">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="p-3 pl-4 whitespace-nowrap">User</th>
                        <th class="p-3 whitespace-nowrap">Event</th>
                        <th class="p-3 whitespace-nowrap">Module</th>
                        <th class="p-3 whitespace-nowrap">Description</th>
                        <th class="p-3 whitespace-nowrap">IP Address</th>
                        <th class="p-3 pr-4 text-right whitespace-nowrap">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-3 pl-4">
                                <div class="font-bold text-slate-900">{{ $log->user->name ?? 'System/Automated' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $log->user->email ?? 'N/A' }}</div>
                            </td>

                            <td class="p-3 whitespace-nowrap">
                                @switch($log->event)
                                    @case('created')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span>➕</span> CREATED
                                        </span>
                                        @break
                                    @case('updated')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span>✏️</span> UPDATED
                                        </span>
                                        @break
                                    @case('deleted')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span>🗑️</span> DELETED
                                        </span>
                                        @break
                                @endswitch
                            </td>

                            <td class="p-3 whitespace-nowrap capitalize font-medium text-slate-700">
                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-[11px]">
                                    {{ $log->log_name }}
                                </span>
                            </td>

                            <td class="p-3 text-slate-800">
                                <div>{{ $log->description }}</div>
                                @if(!empty($log->properties))
                                    <details class="mt-1 text-[11px] text-slate-500 cursor-pointer">
                                        <summary class="hover:text-blue-600 font-medium">View changed fields</summary>
                                        <pre class="mt-1 p-2 bg-slate-50 rounded border border-slate-200 text-[10px] font-mono overflow-x-auto">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</pre>
                                    </details>
                                @endif
                            </td>

                            <td class="p-3 whitespace-nowrap font-mono text-slate-600">
                                {{ $log->ip_address ?? '—' }}
                            </td>

                            <td class="p-3 pr-4 text-right whitespace-nowrap font-mono text-slate-500">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">
                                No activity logs recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $logs->links() }}
        </div>
    </div>
</div>
