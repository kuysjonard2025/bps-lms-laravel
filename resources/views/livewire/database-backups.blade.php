<div class="p-6 space-y-6">
    {{-- Notifications --}}
    @if (session()->has('success'))
        <div class="p-3 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-3 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded-xl">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Database Backups & Recovery</h1>
            <p class="text-xs text-slate-500 mt-1">Generate manual database snapshots, view backup history, and download recovery files.</p>
        </div>

        <div>
            <button
                wire:click="createBackup"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-xs font-bold bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-2 shadow-sm disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="createBackup">💾 Create Backup Now</span>
                <span wire:loading wire:target="createBackup">⚙️ Generating Backup...</span>
            </button>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Backups</span>
                <span class="text-xl font-bold text-slate-900">{{ $stats['total_backups'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 font-bold flex items-center justify-center">📦</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Storage Used</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($stats['total_size'] / 1048576, 2) }} MB</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-bold flex items-center justify-center">💾</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Last Backup</span>
                <span class="text-sm font-bold text-slate-900">
                    {{ $stats['last_backup'] ? \Carbon\Carbon::parse($stats['last_backup'])->diffForHumans() : 'Never' }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 font-bold flex items-center justify-center">🕒</div>
        </div>
    </div>

    {{-- Backup History Table --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-800">Available Backup Archives</h2>

        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="p-3 pl-4 whitespace-nowrap">File Name</th>
                        <th class="p-3 whitespace-nowrap">Size</th>
                        <th class="p-3 whitespace-nowrap">Created At</th>
                        <th class="p-3 pr-4 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($backups as $backup)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-3 pl-4 font-mono font-medium text-slate-800">
                                {{ basename($backup['file_name']) }}
                            </td>

                            <td class="p-3 whitespace-nowrap font-mono text-slate-600">
                                {{ number_format($backup['file_size'] / 1048576, 2) }} MB
                            </td>

                            <td class="p-3 whitespace-nowrap font-mono text-slate-500">
                                {{ \Carbon\Carbon::createFromTimestamp($backup['last_modified'])->format('M d, Y H:i:s') }}
                            </td>

                            <td class="p-3 pr-4 text-right whitespace-nowrap space-x-2">
                                <button
                                    wire:click="downloadBackup(@js($backup['file_name']))"
                                    class="px-2.5 py-1 text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-100 transition"
                                >
                                    ⬇️ Download
                                </button>
                                <button
                                    wire:click="deleteBackup(@js($backup['file_name']))"
                                    wire:confirm="Are you sure you want to delete this backup archive?"
                                    class="px-2.5 py-1 text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded-lg hover:bg-rose-100 transition"
                                >
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-slate-400">
                                No backup archives available. Click "Create Backup Now" to generate one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
