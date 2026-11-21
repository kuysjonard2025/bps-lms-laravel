{{-- resources/views/livewire/database-backups.blade.php --}}
<div class="space-y-6 text-content-body">
    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            role="status"
            class="flex items-center justify-between gap-3 rounded-xl border border-brand-primary/30 bg-brand-tint-light p-3.5 text-xs font-semibold text-brand-primary shadow-2xs"
        >
            <div class="flex items-center gap-2.5">
                <x-heroicon-o-check-circle class="h-4 w-4 shrink-0" aria-hidden="true" />
                <span>{{ session('success') }}</span>
            </div>
            <button
                type="button"
                @click="show = false"
                aria-label="Dismiss notification"
                class="text-brand-primary hover:text-content-heading cursor-pointer rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
            >&times;</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div
            x-data="{ show: true }"
            x-show="show"
            role="alert"
            class="flex items-center justify-between gap-3 rounded-xl border border-brand-secondary/35 bg-brand-secondary/15 p-3.5 text-xs font-semibold text-brand-secondary shadow-2xs"
        >
            <div class="flex items-center gap-2.5">
                <x-heroicon-o-exclamation-circle class="h-4 w-4 shrink-0" aria-hidden="true" />
                <span>{{ session('error') }}</span>
            </div>
            <button
                type="button"
                @click="show = false"
                aria-label="Dismiss error"
                class="text-brand-secondary hover:text-content-heading cursor-pointer rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
            >&times;</button>
        </div>
    @endif

    {{-- Header Card with Inline Primary Action --}}
    <section aria-label="Database backups header" class="bg-surface-card p-5 sm:p-6 rounded-2xl border border-surface-border shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-content-heading tracking-tight">Database Backups &amp; Recovery</h1>
            <p class="text-xs text-content-muted mt-0.5">Generate manual database snapshots, view backup history, and download recovery files.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                wire:click="createBackup"
                wire:target="createBackup"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-xs font-semibold bg-brand-primary text-white rounded-xl hover:bg-brand-primary-hover transition flex items-center gap-2 shadow-2xs cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
            >
                <span wire:loading wire:target="createBackup" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                <x-heroicon-o-circle-stack wire:loading.remove wire:target="createBackup" class="w-4 h-4 shrink-0" aria-hidden="true" />
                <span wire:loading.remove wire:target="createBackup">Create Backup Now</span>
                <span wire:loading wire:target="createBackup">Generating Backup...</span>
            </button>
        </div>
    </section>

    {{-- Stats Row --}}
    <section aria-label="Backup storage metrics" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Total Backups</span>
                <span class="text-xl font-bold font-mono text-content-heading mt-0.5 block">{{ number_format($stats['total_backups']) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-archive-box class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Storage Used</span>
                <span class="text-xl font-bold font-mono text-content-heading mt-0.5 block">{{ number_format($stats['total_size'] / 1048576, 2) }} MB</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-server-stack class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>

        <div class="bg-surface-card p-4 rounded-2xl border border-surface-border shadow-2xs flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider block">Last Backup</span>
                <span class="text-sm font-bold text-content-heading mt-1 block">
                    {{ $stats['last_backup'] ? \Carbon\Carbon::createFromTimestamp($stats['last_backup'])->diffForHumans() : 'Never' }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-tint-light text-brand-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-clock class="w-5 h-5" aria-hidden="true" />
            </div>
        </div>
    </section>

    {{-- Backup History Table Card --}}
    <section aria-label="Available backup archives" class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-surface-border/70 flex items-center justify-between">
            <h2 class="text-sm font-bold text-content-heading">Available Backup Archives</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-content-body">
                <thead class="bg-surface-canvas/70 text-[11px] font-semibold uppercase tracking-wider text-content-muted border-b border-surface-border">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 whitespace-nowrap">File Name</th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">Size</th>
                        <th scope="col" class="px-4 py-3.5 whitespace-nowrap">Created At</th>
                        <th scope="col" class="px-5 py-3.5 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border/60 bg-surface-card">
                    @forelse ($backups as $backup)
                        <tr wire:key="backup-archive-{{ md5($backup['file_name']) }}" class="hover:bg-surface-canvas/60 transition-colors">
                            <td class="px-5 py-3.5 font-mono font-medium text-content-heading whitespace-nowrap">
                                {{ basename($backup['file_name']) }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-content-body">
                                {{ number_format($backup['file_size'] / 1048576, 2) }} MB
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-content-muted">
                                {{ \Carbon\Carbon::createFromTimestamp($backup['last_modified'])->format('M d, Y H:i:s') }}
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                <button
                                    type="button"
                                    wire:click="downloadBackup(@js($backup['file_name']))"
                                    wire:target="downloadBackup(@js($backup['file_name']))"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold bg-brand-tint-light text-brand-primary border border-brand-primary/25 rounded-lg hover:bg-brand-tint transition cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                >
                                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                                    <span>Download</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="deleteBackup(@js($backup['file_name']))"
                                    wire:target="deleteBackup(@js($backup['file_name']))"
                                    wire:loading.attr="disabled"
                                    wire:confirm="Are you sure you want to delete this backup archive?"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold bg-brand-secondary/10 text-brand-secondary border border-brand-secondary/30 rounded-lg hover:bg-brand-secondary/15 transition cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                                >
                                    <x-heroicon-o-trash class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                                    <span>Delete</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-10 px-4 text-content-muted">
                                No backup archives available. Click "Create Backup Now" to generate one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
