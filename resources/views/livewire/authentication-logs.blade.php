<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Authentication Logs</h1>
            <p class="text-xs text-slate-500 mt-1">Audit log of login attempts, successes, failures, and logouts across the platform.</p>
        </div>

        {{-- Export Action Placeholders --}}
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
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Auth Events</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($stats['total_logs']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-bold flex items-center justify-center">🛡️</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Successful Logins</span>
                <span class="text-xl font-bold text-emerald-600">{{ number_format($stats['login_success']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center">🔑</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Failed Attempts</span>
                <span class="text-xl font-bold text-amber-600">{{ number_format($stats['login_failed']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 font-bold flex items-center justify-center">⚠️</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Lockouts</span>
                <span class="text-xl font-bold text-rose-600">{{ number_format($stats['lockouts']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 font-bold flex items-center justify-center">🚫</div>
        </div>
    </div>

    {{-- Main Table Panel --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        {{-- Filters Bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto flex-1">
                {{-- Search --}}
                <div class="relative flex-1 min-w-[240px]">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search Email, User Name, IP Address..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none"
                    >
                    <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
                </div>

                {{-- Event Filter --}}
                <select wire:model.live="eventFilter" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none">
                    <option value="all">All Event Types</option>
                    <option value="login_success">Login Success</option>
                    <option value="login_failed">Login Failed</option>
                    <option value="logout">Logout</option>
                    <option value="lockout">Lockout</option>
                </select>

                {{-- Date Range Filter --}}
                <select wire:model.live="dateRange" class="py-2 px-3 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="7_days">Last 7 Days</option>
                    <option value="30_days">Last 30 Days</option>
                </select>
            </div>
        </div>

        {{-- Logs Table --}}
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="p-3 pl-4 whitespace-nowrap">User / Targeted Email</th>
                        <th class="p-3 whitespace-nowrap">Event Status</th>
                        <th class="p-3 whitespace-nowrap">IP Address</th>
                        <th class="p-3 whitespace-nowrap">User Agent</th>
                        <th class="p-3 pr-4 text-right whitespace-nowrap">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-3 pl-4">
                                <div class="font-bold text-slate-900">{{ $log->user?->getFullNameAttribute() ?? 'Unregistered User' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $log->email }}</div>
                            </td>

                            <td class="p-3 whitespace-nowrap">
                                @switch($log->event)
                                    @case('login_success')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span>✅</span> SUCCESS
                                        </span>
                                        @break
                                    @case('login_failed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span>⚠️</span> FAILED
                                        </span>
                                        @break
                                    @case('logout')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span>🚪</span> LOGOUT
                                        </span>
                                        @break
                                    @case('lockout')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span>🚫</span> LOCKOUT
                                        </span>
                                        @break
                                @endswitch
                            </td>

                            <td class="p-3 whitespace-nowrap font-mono text-slate-700">
                                {{ $log->ip_address ?? '—' }}
                            </td>

                            <td class="p-3 max-w-xs truncate text-[11px] text-slate-400" title="{{ $log->user_agent }}">
                                {{ $log->user_agent ?? '—' }}
                            </td>

                            <td class="p-3 pr-4 text-right whitespace-nowrap font-mono text-slate-500">
                                {{ $log->logged_at->format('M d, Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-400">
                                No authentication logs match your criteria.
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
