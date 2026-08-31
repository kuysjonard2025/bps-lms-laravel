<div class="space-y-6">
    <!-- PAGE HEADER -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white p-5 rounded-xl border border-gray-200 shadow-xs">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">Borrower Attendance Logs</h1>
            <p class="mt-1 text-xs sm:text-sm text-gray-500">Monitor real-time library visitation history and manage active borrower sessions.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Excel Export Button -->
            <button
                type="button"
                wire:click="exportExcel"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-600 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition cursor-pointer disabled:opacity-50">
                <svg wire:loading.remove wire:target="exportExcel" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <svg wire:loading wire:target="exportExcel" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>Export Excel</span>
            </button>

            <!-- PDF Export Button -->
            <button
                type="button"
                wire:click="exportPdf"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-lg border border-red-600 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 transition cursor-pointer disabled:opacity-50">
                <svg wire:loading.remove wire:target="exportPdf" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <svg wire:loading wire:target="exportPdf" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>Export PDF</span>
            </button>

            <!-- End of Day Logout -->
            <button
                type="button"
                wire:click="$set('showForceCheckoutModal', true)"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>End-of-Day Log Out</span>
            </button>
        </div>
    </div>

    <!-- DAILY STATS CARDS -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- Card 1 -->
        <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Visits Today</span>
                <span class="rounded-lg bg-gray-100 p-2 text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-gray-900 tracking-tight">{{ $totalToday }}</p>
        </div>

        <!-- Card 2 -->
        <div class="relative overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/40 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Currently Inside</span>
                <span class="rounded-lg bg-emerald-100 p-2 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-emerald-700 tracking-tight">{{ $currentlyInside }}</p>
        </div>

        <!-- Card 3 -->
        <div class="relative overflow-hidden rounded-xl border border-blue-200 bg-blue-50/40 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Logged Out Today</span>
                <span class="rounded-lg bg-blue-100 p-2 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-blue-700 tracking-tight">{{ $checkedOutToday }}</p>
        </div>
    </div>

    <!-- DATATABLE LOGS & HISTORY -->
    <div class="space-y-4">
        <!-- FILTERS BAR -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-white p-3.5 rounded-xl border border-gray-200 shadow-xs">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search borrower name or ID..."
                        class="w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>

                <!-- Date Filter -->
                <input
                    wire:model.live="filterDate"
                    type="date"
                    class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />

                <!-- Status Filter -->
                <select wire:model.live="filterStatus" class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="all">All Statuses</option>
                    <option value="inside">Currently Inside</option>
                    <option value="logged_out">Logged Out</option>
                </select>
            </div>
        </div>

        <!-- TABLE MATRIX -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wider text-gray-500 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Log Date</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Borrower ID / Name</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Type</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Grade & Section</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Log In Time</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Log Out Time</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">Status</th>
                            <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($logs as $log)
                            <tr wire:key="borrower-log-{{ $log->id }}" class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">
                                        {{ $log->patron->first_name ?? '' }}
                                        {{ $log->patron->middle_name ?? '' }}
                                        {{ $log->patron->last_name ?? 'Deleted Borrower' }}
                                        {{ $log->patron->suffix ?? '' }}
                                    </div>
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">{{ $log->patron->school_id ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                        {{ $log->patron->patronType->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                    @if($log->patron && $log->patron->gradeLevel)
                                        {{ $log->patron->gradeLevel->name }} - {{ $log->patron->section->name ?? '' }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-800 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($log->time_in)->format('h:i:s A') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-800 whitespace-nowrap">
                                    @if($log->time_out)
                                        {{ \Carbon\Carbon::parse($log->time_out)->format('h:i:s A') }}
                                    @else
                                        <span class="text-gray-400">--:--:--</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($log->time_out)
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-medium text-blue-700 border border-blue-200">
                                            Logged Out
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-medium text-emerald-700 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Inside Library
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if(! $log->time_out)
                                        <button
                                            type="button"
                                            wire:click="manualCheckOut({{ $log->id }})"
                                            wire:confirm="Are you sure you want to manually log out this borrower?"
                                            class="text-xs font-semibold text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 px-2.5 py-1 rounded transition cursor-pointer">
                                            Log Out
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400">Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    No attendance log records found matching the current criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- FORCE CHECKOUT MODAL -->
    @if($showForceCheckoutModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" role="dialog" aria-modal="true">
            <div wire:click="$set('showForceCheckoutModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10 overflow-hidden my-auto p-6">
                <div class="flex items-center gap-3 text-amber-600 mb-3">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900">End-of-Day Mass Log Out</h3>
                </div>

                <p class="text-xs text-gray-600 mb-5">
                    Are you sure you want to log out all <strong>{{ $currentlyInside }}</strong> active borrowers who are currently marked inside the library for today?
                </p>

                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        wire:click="$set('showForceCheckoutModal', false)"
                        class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="checkoutAllActive"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-md shadow-xs transition cursor-pointer">
                        Confirm Log Out
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
