<div>
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Patron Attendance Logs</h1>
            <p class="text-sm text-gray-500">Manage real-time RFID attendance scanning and review daily visitation logs.</p>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="$set('showForceCheckoutModal', true)" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                End-of-Day Log Out
            </button>
        </div>
    </div>

    <!-- DAILY STATS CARDS -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Visits Today</span>
            <p class="mt-2 text-3xl font-extrabold text-gray-900">{{ $totalToday }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Currently Inside</span>
            <p class="mt-2 text-3xl font-extrabold text-emerald-700">{{ $currentlyInside }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-blue-600">Logged Out Today</span>
            <p class="mt-2 text-3xl font-extrabold text-blue-700">{{ $checkedOutToday }}</p>
        </div>
    </div>

    <!-- NAVIGATION TABS -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
                wire:click="$set('activeTab', 'scanner')"
                class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-semibold transition-colors flex items-center gap-2
                {{ $activeTab === 'scanner' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" />
                </svg>
                RFID Kiosk Terminal
            </button>

            <button
                wire:click="$set('activeTab', 'logs')"
                class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-semibold transition-colors flex items-center gap-2
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
                <label for="rfidInput" class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    RFID Terminal Active (Auto-Focused)
                </label>
                <div class="relative">
                    <input
                        x-ref="rfidInput"
                        wire:model="rfidScan"
                        id="rfidInput"
                        type="text"
                        placeholder="Tap RFID Tag or Card..."
                        autocomplete="off"
                        @blur="setTimeout(() => focusInput(), 150)"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50/50 p-4 pl-10 text-xl font-mono text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <svg class="absolute left-3.5 top-5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" />
                    </svg>
                </div>
                <p class="text-xs text-gray-500">Keep window active. RFID scans will process immediately on tap.</p>
            </form>

            <!-- SCAN FEEDBACK BANNER -->
            @if($lastScannedPatron)
                <div class="mt-6 rounded-xl border transition-all duration-300 overflow-hidden shadow-sm
                    {{ $lastScannedPatron['status'] === 'error' ? 'bg-red-50 border-red-200' : '' }}
                    {{ $lastScannedPatron['status'] === 'in' ? 'bg-emerald-50/60 border-emerald-300' : '' }}
                    {{ $lastScannedPatron['status'] === 'out' ? 'bg-blue-50/60 border-blue-300' : '' }}">

                    @if($lastScannedPatron['status'] === 'error')
                        <div class="p-6 flex items-center gap-3 text-red-900">
                            <svg class="h-8 w-8 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-lg font-semibold text-red-700">{{ $lastScannedPatron['message'] }}</span>
                        </div>
                    @else
                        <!-- Header Banner -->
                        <div class="px-6 py-3 flex items-center justify-between border-b
                            {{ $lastScannedPatron['status'] === 'in' ? 'bg-emerald-100/70 border-emerald-200' : 'bg-blue-100/70 border-blue-200' }}">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider
                                    {{ $lastScannedPatron['status'] === 'in' ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white' }}">
                                    {{ $lastScannedPatron['action'] }}
                                </span>
                                <span class="text-xs font-semibold text-gray-600">
                                    Visit #{{ $lastScannedPatron['visits_today'] }} Today
                                </span>
                            </div>
                            <span class="text-xs font-mono font-bold text-gray-500">ID: {{ $lastScannedPatron['patron_id'] }}</span>
                        </div>

                        <!-- Profile Content Grid -->
                        <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                            <!-- Avatar / Photo -->
                            <div class="md:col-span-3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200/80 pb-4 md:pb-0 md:pr-4">
                                @if(!empty($lastScannedPatron['photo_url']))
                                    <img src="{{ $lastScannedPatron['photo_url'] }}" alt="{{ $lastScannedPatron['name'] }}" class="h-28 w-28 rounded-full object-cover shadow-md border-2 border-white" />
                                @else
                                    <div class="h-28 w-28 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-3xl font-black shadow-inner border-2 border-white">
                                        {{ strtoupper(substr($lastScannedPatron['first_name'] ?? 'P', 0, 1)) }}{{ strtoupper(substr($lastScannedPatron['last_name'] ?? '', 0, 1)) }}
                                    </div>
                                @endif
                                <span class="mt-3 inline-block px-3 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-800">
                                    {{ $lastScannedPatron['type'] }}
                                </span>
                            </div>

                            <!-- Patron Detail Fields -->
                            <div class="md:col-span-5 space-y-3">
                                <div>
                                    <h3 class="text-2xl font-black text-gray-900 leading-tight">
                                        {{ $lastScannedPatron['name'] }}
                                    </h3>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 pt-2 border-t border-gray-200/60">
                                    <div>
                                        <span class="block font-medium">Grade:</span>
                                        <span class="font-semibold text-gray-800">{{ $lastScannedPatron['grade'] }}</span>
                                    </div>
                                    <div>
                                        <span class="block font-medium">Section:</span>
                                        <span class="font-semibold text-gray-800">{{ $lastScannedPatron['section'] }}</span>
                                    </div>
                                    <div>
                                        <span class="block font-medium">Address:</span>
                                        <span class="font-semibold text-gray-800">{{ $lastScannedPatron['address'] }}</span>
                                    </div>
                                    <div>
                                        <span class="block font-medium">Contact No.:</span>
                                        <span class="font-semibold text-gray-800">{{ $lastScannedPatron['contact_number'] }}</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="block font-medium">Email:</span>
                                        <span class="font-semibold text-gray-800 truncate block">{{ $lastScannedPatron['email'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Session Clock Box -->
                            <div class="md:col-span-4 bg-white/80 rounded-xl p-4 border border-gray-200 shadow-sm flex flex-col justify-between h-full">
                                <span class="text-xs font-bold uppercase text-gray-400 tracking-wider">Session Info</span>

                                <div class="my-2 space-y-2 font-mono">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">Log In Time:</span>
                                        <span class="font-bold text-emerald-700">{{ $lastScannedPatron['time_in'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">Log Out Time:</span>
                                        <span class="font-bold {{ $lastScannedPatron['time_out'] ? 'text-blue-700' : 'text-gray-300' }}">
                                            {{ $lastScannedPatron['time_out'] ?? '--:--:--' }}
                                        </span>
                                    </div>

                                    @if($lastScannedPatron['duration'])
                                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs font-sans">
                                            <span class="text-gray-400">Duration:</span>
                                            <span class="font-semibold text-gray-700">{{ $lastScannedPatron['duration'] }}</span>
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
        <div>
            <!-- FILTERS -->
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search patron name or ID..."
                        class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <input
                        wire:model.live="filterDate"
                        type="date"
                        class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
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
                        <thead class="bg-gray-50/80 text-xs font-semibold uppercase tracking-wider text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3">Log Date</th>
                                <th class="px-4 py-3">Patron ID / Name</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Grade & Section</th>
                                <th class="px-4 py-3">Log In Time</th>
                                <th class="px-4 py-3">Log Out Time</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $log->patron->first_name ?? '' }}
                                            {{ $log->patron->middle_name ?? '' }}
                                            {{ $log->patron->last_name ?? 'Deleted Patron' }}
                                            {{ $log->patron->suffix ?? '' }}
                                        </div>
                                        <div class="text-xs text-gray-400 font-mono">{{ $log->patron->patron_id ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $log->patron->patronType->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if($log->patron && $log->patron->gradeLevel)
                                            {{ $log->patron->gradeLevel->name }} - {{ $log->patron->section->name ?? '' }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-800">
                                        {{ \Carbon\Carbon::parse($log->time_in)->format('h:i:s A') }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-800">
                                        @if($log->time_out)
                                            {{ \Carbon\Carbon::parse($log->time_out)->format('h:i:s A') }}
                                        @else
                                            <span class="text-gray-400">--:--:--</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($log->time_out)
                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 border border-blue-200">
                                                Logged Out
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Inside
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if(!$log->time_out)
                                            <button
                                                wire:click="manualCheckOut({{ $log->id }})"
                                                class="rounded bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 transition">
                                                Force Log Out
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                        No attendance logs found for selected criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 p-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- END-OF-DAY FORCE LOG OUT MODAL -->
    @if($showForceCheckoutModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-xl bg-white border border-gray-200 p-6 shadow-xl text-gray-900">
                <h3 class="text-lg font-bold text-gray-900">End-of-Day Log Out Confirmation</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to log out all patrons currently marked as <strong class="text-emerald-600">"Inside"</strong> for today?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showForceCheckoutModal', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="checkoutAllActive" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 shadow-sm">
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
