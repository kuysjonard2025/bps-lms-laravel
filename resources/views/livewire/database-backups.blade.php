<?php // resources/views/livewire/database-backups.blade.php ?>
<div
    class="space-y-6 text-content-body"
    x-data="{
        confirmDeleteOpen: false,
        pendingDeleteFile: null,
        pendingDeleteName: '',
        openDeleteModal(fileName, displayName) {
            this.pendingDeleteFile = fileName;
            this.pendingDeleteName = displayName;
            this.confirmDeleteOpen = true;
        },
        closeDeleteModal() {
            this.confirmDeleteOpen = false;
            this.pendingDeleteFile = null;
            this.pendingDeleteName = '';
        },
        confirmDelete() {
            if (!this.pendingDeleteFile) return;
            const targetFile = this.pendingDeleteFile;
            this.closeDeleteModal();
            $wire.deleteBackup(targetFile);
        }
    }"
    @keydown.escape.window="if (confirmDeleteOpen) closeDeleteModal()"
>
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
                        @php
                            $archiveLabel = basename($backup['file_name']);
                            $createdAt = \Carbon\Carbon::createFromTimestamp($backup['last_modified']);
                        @endphp
                        <tr wire:key="backup-archive-{{ md5($backup['file_name']) }}" class="hover:bg-surface-canvas/60 transition-colors">
                            <td class="px-5 py-3.5 font-mono font-medium text-content-heading whitespace-nowrap">
                                {{ $archiveLabel }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-content-body">
                                {{ number_format($backup['file_size'] / 1048576, 2) }} MB
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap" title="{{ $createdAt->toDayDateTimeString() }}">
                                <div class="font-mono text-content-muted">{{ $createdAt->format('M d, Y H:i:s') }}</div>
                                <div class="text-[11px] font-medium text-brand-primary mt-0.5">{{ $createdAt->diffForHumans() }}</div>
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                <button
                                    type="button"
                                    wire:click="downloadBackup(@js($backup['file_name']))"
                                    wire:target="downloadBackup"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold bg-brand-tint-light text-brand-primary border border-brand-primary/25 rounded-lg hover:bg-brand-tint transition cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                >
                                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                                    <span>Download</span>
                                </button>
                                <button
                                    type="button"
                                    @click="openDeleteModal(@js($backup['file_name']), @js($archiveLabel))"
                                    wire:target="deleteBackup"
                                    wire:loading.attr="disabled"
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

    {{-- Styled Confirmation Modal --}}
    <div
        x-show="confirmDeleteOpen"
        x-cloak
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-backup-modal-title"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-content-heading/50 backdrop-blur-[1px]"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            @click.outside="closeDeleteModal()"
            class="w-full max-w-md rounded-2xl border border-surface-border bg-surface-card p-5 sm:p-6 shadow-xl space-y-4"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-brand-secondary/15 text-brand-secondary flex items-center justify-center shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" aria-hidden="true" />
                </div>
                <div class="space-y-1">
                    <h2 id="delete-backup-modal-title" class="text-sm font-bold text-content-heading">
                        Delete Backup Archive?
                    </h2>
                    <p class="text-xs text-content-muted leading-relaxed">
                        Are you sure you want to permanently delete
                        <span class="font-mono font-semibold text-content-heading" x-text="pendingDeleteName"></span>?
                        This action cannot be undone.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-surface-border/70">
                <button
                    type="button"
                    @click="closeDeleteModal()"
                    class="px-3.5 py-2 text-xs font-medium rounded-xl border border-surface-border bg-surface-canvas text-content-body hover:text-content-heading hover:bg-surface-card transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="confirmDelete()"
                    wire:target="deleteBackup"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-xs font-semibold rounded-xl bg-brand-secondary text-white hover:bg-brand-secondary/90 transition flex items-center gap-1.5 shadow-2xs cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-secondary/25"
                >
                    <span wire:loading wire:target="deleteBackup" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                    <x-heroicon-o-trash wire:loading.remove wire:target="deleteBackup" class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                    <span>Delete Archive</span>
                </button>
            </div>
        </div>
    </div>
</div>
