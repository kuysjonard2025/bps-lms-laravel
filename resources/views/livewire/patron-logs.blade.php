<div>
    <!-- PAGE HEADER -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Patron Attendance Logs</h1>
            <p class="mt-1 text-sm text-gray-500">Manage real-time RFID attendance scanning and review daily visitation logs.</p>
        </div>

        <div class="flex items-center gap-3">
            <button
                wire:click="$set('showForceCheckoutModal', true)"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                End-of-Day Log Out
            </button>
        </div>
    </div>

    <!-- DAILY STATS CARDS -->
    <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
        <!-- Card 1 -->
        <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Visits Today</span>
                <span class="rounded-lg bg-gray-100 p-2 text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-gray-900 tracking-tight">{{ $totalToday }}</p>
        </div>

        <!-- Card 2 -->
        <div class="relative overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/40 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Currently Inside</span>
                <span class="rounded-lg bg-emerald-100 p-2 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-emerald-700 tracking-tight">{{ $currentlyInside }}</p>
        </div>

        <!-- Card 3 -->
        <div class="relative overflow-hidden rounded-xl border border-blue-200 bg-blue-50/40 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Logged Out Today</span>
                <span class="rounded-lg bg-blue-100 p-2 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-blue-700 tracking-tight">{{ $checkedOutToday }}</p>
        </div>
    </div>

    <!-- NAVIGATION TABS -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
                wire:click="$set('activeTab', 'scanner')"
                class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-semibold transition-colors whitespace-nowrap
                {{ $activeTab === 'scanner' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" />
                </svg>
                RFID Kiosk Terminal
            </button>

            <button
                wire:click="$set('activeTab', 'logs')"
                class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-semibold transition-colors whitespace-nowrap
                {{ $activeTab === 'logs' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Attendance Logs & History
            </button>
        </nav>
    </div>

    <!-- TAB 1: RFID SCANNER TERMINAL -->
    @if($activeTab === 'scanner')
        <div
            x-data="{
                focusInput() {
                    $nextTick(() => { this.$refs.rfidInput?.focus(); });
                }
            }"
            x-init="focusInput()"
            @click="focusInput()"
            class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <form wire:submit.prevent="processRfidScan" class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <label for="rfidInput" class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        RFID Scanner Active
                    </label>
                    <span class="text-xs text-gray-400 font-medium">Auto-Focus Enabled</span>
                </div>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" />
                        </svg>
                    </div>
                    <input
                        x-ref="rfidInput"
                        wire:model="rfidScan"
                        id="rfidInput"
                        type="text"
                        placeholder="Tap RFID tag or card to scan..."
                        autocomplete="off"
                        @blur="setTimeout(() => focusInput(), 150)"
                        class="w-full rounded-xl border border-gray-300 bg-gray-50/50 py-4 pl-12 pr-4 text-xl font-mono text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all shadow-inner"
                    />
                </div>
                <p class="text-xs text-gray-500">Keep window active. Scans process automatically upon tag detection.</p>
            </form>

            <!-- SCAN FEEDBACK BANNER -->
            @if($lastScannedPatron)
                <div class="mt-6 rounded-xl border transition-all duration-300 overflow-hidden shadow-sm
                    {{ $lastScannedPatron['status'] === 'error' ? 'bg-red-50/80 border-red-200' : '' }}
                    {{ $lastScannedPatron['status'] === 'in' ? 'bg-emerald-50/60 border-emerald-200' : '' }}
                    {{ $lastScannedPatron['status'] === 'out' ? 'bg-blue-50/60 border-blue-200' : '' }}">

                    @if($lastScannedPatron['status'] === 'error')
                        <div class="p-6 flex items-center gap-4 text-red-900">
                            <div class="rounded-full bg-red-100 p-2.5 text-red-600 shrink-0">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-base font-semibold text-red-800">{{ $lastScannedPatron['message'] }}</span>
                        </div>
                    @else
                        <!-- Header Banner -->
                        <div class="px-6 py-3.5 flex items-center justify-between border-b
                            {{ $lastScannedPatron['status'] === 'in' ? 'bg-emerald-100/70 border-emerald-200' : 'bg-blue-100/70 border-blue-200' }}">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider
                                    {{ $lastScannedPatron['status'] === 'in' ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white' }}">
                                    {{ $lastScannedPatron['action'] }}
                                </span>
                                <span class="text-xs font-semibold text-gray-700">
                                    Visit #{{ $lastScannedPatron['visits_today'] }} Today
                                </span>
                            </div>
                            <span class="text-xs font-mono font-bold text-gray-500 bg-white/60 px-2.5 py-1 rounded border border-gray-200/50">
                                ID: {{ $lastScannedPatron['patron_id'] }}
                            </span>
                        </div>

                        <!-- Profile Content Grid -->
                        <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                            <!-- Avatar / Photo -->
                            <div class="lg:col-span-3 flex flex-col items-center justify-center border-b lg:border-b-0 lg:border-r border-gray-200/80 pb-6 lg:pb-0 lg:pr-6">
                                @if(!empty($lastScannedPatron['photo_url']))
                                    <img src="{{ $lastScannedPatron['photo_url'] }}" alt="{{ $lastScannedPatron['name'] }}" class="h-28 w-28 rounded-full object-cover shadow-md border-2 border-white ring-2 ring-gray-100" />
                                @else
                                    <div class="h-28 w-28 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-2xl font-bold shadow-inner border-2 border-white ring-2 ring-indigo-50">
                                        {{ strtoupper(substr($lastScannedPatron['first_name'] ?? 'P', 0, 1)) }}{{ strtoupper(substr($lastScannedPatron['last_name'] ?? '', 0, 1)) }}
                                    </div>
                                @endif
                                <span class="mt-3 inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-200/80 text-gray-800">
                                    {{ $lastScannedPatron['type'] }}
                                </span>
                            </div>

                            <!-- Patron Detail Fields -->
                            <div class="lg:col-span-5 space-y-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 leading-tight">
                                        {{ $lastScannedPatron['name'] }}
                                    </h3>
                                </div>

                                <div class="grid grid-cols-2 gap-3 text-xs text-gray-600 pt-3 border-t border-gray-200/60">
                                    <div>
                                        <span class="block text-gray-400 font-medium">Grade</span>
                                        <span class="font-semibold text-gray-800 text-sm">{{ $lastScannedPatron['grade'] }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-gray-400 font-medium">Section</span>
                                        <span class="font-semibold text-gray-800 text-sm">{{ $lastScannedPatron['section'] }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-gray-400 font-medium">Address</span>
                                        <span class="font-semibold text-gray-800">{{ $lastScannedPatron['address'] }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-gray-400 font-medium">Contact No.</span>
                                        <span class="font-semibold text-gray-800">{{ $lastScannedPatron['contact_number'] }}</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="block text-gray-400 font-medium">Email</span>
                                        <span class="font-semibold text-gray-800 truncate block">{{ $lastScannedPatron['email'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Session Clock Box -->
                            <div class="lg:col-span-4 bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex flex-col justify-between h-full">
                                <span class="text-xs font-bold uppercase text-gray-400 tracking-wider">Session Info</span>

                                <div class="my-3 space-y-2.5 font-mono">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500 font-sans text-xs">Log In Time:</span>
                                        <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">{{ $lastScannedPatron['time_in'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500 font-sans text-xs">Log Out Time:</span>
                                        <span class="font-bold {{ $lastScannedPatron['time_out'] ? 'text-blue-700 bg-blue-50 px-2 py-0.5 rounded' : 'text-gray-400' }}">
                                            {{ $lastScannedPatron['time_out'] ?? '--:--:--' }}
                                        </span>
                                    </div>

                                    @if($lastScannedPatron['duration'])
                                        <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs font-sans">
                                            <span class="text-gray-500">Duration:</span>
                                            <span class="font-semibold text-gray-800">{{ $lastScannedPatron['duration'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 2: DATATABLE LOGS & HISTORY -->
    @if($activeTab === 'logs')
        <div class="space-y-4">
            <!-- FILTERS -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
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
                            placeholder="Search patron name or ID..."
                            class="w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3.5 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Date Filter -->
                    <input
                        wire:model.live="filterDate"
                        type="date"
                        class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />

                    <!-- Status Filter -->
                    <select wire:model.live="filterStatus" class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="all">All Statuses</option>
                        <option value="inside">Currently Inside</option>
                        <option value="logged_out">Logged Out</option>
                    </select>
                </div>
            </div>

            <!-- TABLE -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-5 py-3.5">Log Date</th>
                                <th class="px-5 py-3.5">Patron ID / Name</th>
                                <th class="px-5 py-3.5">Type</th>
                                <th class="px-5 py-3.5">Grade & Section</th>
                                <th class="px-5 py-3.5">Log In Time</th>
                                <th class="px-5 py-3.5">Log Out Time</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-5 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $log->patron->first_name ?? '' }}
                                            {{ $log->patron->middle_name ?? '' }}
                                            {{ $log->patron->last_name ?? 'Deleted Patron' }}
                                            {{ $log->patron->suffix ?? '' }}
                                        </div>
                                        <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $log->patron->patron_id ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-700">
                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                            {{ $log->patron->patronType->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-700">
                                        @if($log->patron && $log->patron->gradeLevel)
                                            {{ $log->patron->gradeLevel->name }} - {{ $log->patron->section->name ?? '' }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-mono text-xs text-gray-800 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($log->time_in)->format('h:i:s A') }}
                                    </td>
                                    <td class="px-5 py-4 font-mono text-xs text-gray-800 whitespace-nowrap">
                                        @if($log->time_out)
                                            {{ \Carbon\Carbon::parse($log->time_out)->format('h:i:s A') }}
                                        @else
                                            <span class="text-gray-400">--:--:--</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if($log->time_out)
                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                Logged Out
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Inside
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        @if(!$log->time_out)
                                            <button
                                                wire:click="manualCheckOut({{ $log->id }})"
                                                class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 transition focus:outline-none focus:ring-2 focus:ring-gray-300">
                                                Force Log Out
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 mb-3">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">No attendance logs found</p>
                                        <p class="text-xs text-gray-500 mt-1">Try adjusting your search or filter parameters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 p-4 bg-gray-50/50">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- END-OF-DAY FORCE LOG OUT MODAL -->
    @if($showForceCheckoutModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>

            <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-xl transition-all">
                <div class="flex items-center gap-3 text-amber-600 mb-3">
                    <div class="rounded-full bg-amber-100 p-2">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">End-of-Day Log Out Confirmation</h3>
                </div>

                <p class="text-sm text-gray-600 leading-relaxed">
                    Are you sure you want to log out all patrons currently marked as <strong class="text-emerald-600 font-semibold">"Inside"</strong> for today? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        wire:click="$set('showForceCheckoutModal', false)"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition">
                        Cancel
                    </button>
                    <button
                        wire:click="checkoutAllActive"
                        class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                        Confirm Mass Log Out
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- AUDIO FEEDBACK -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('play-sound', (event) => {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);

                if (event.type === 'in') {
                    osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                    osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1);
                } else if (event.type === 'out') {
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    osc.frequency.setValueAtTime(587.33, ctx.currentTime + 0.1);
                } else {
                    osc.frequency.setValueAtTime(200, ctx.currentTime);
                }

                osc.start();
                gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.25);
                osc.stop(ctx.currentTime + 0.25);
            });
        });
    </script>
</div>
