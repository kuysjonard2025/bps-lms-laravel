<?php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Livewire\Traits\LogsActivity;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class DatabaseBackups extends Component
{
    use WithPagination, SanitizesInputs;

    public bool $isCreatingBackup = false;

    private function resolveBackupDisk(): FilesystemAdapter
    {
        $diskName = config('backup.backup.destination.disks')[0] ?? 'local';

        /** @var FilesystemAdapter $disk */
        return Storage::disk($diskName);
    }

    private function isSafeBackupPath(string $fileName): bool
    {
        $normalized = ltrim(str_replace('\\', '/', $fileName), '/');

        if ($normalized === '' || str_contains($normalized, '..') || ! str_ends_with(strtolower($normalized), '.zip')) {
            return false;
        }

        return true;
    }

    public function createBackup(): void
    {
        $this->isCreatingBackup = true;

        try {
            Artisan::call('backup:run', ['--only-db' => true]);
            $output = Artisan::output();

            if (
                str_contains(strtolower($output), 'failed')
                || str_contains(strtolower($output), 'exception')
                || str_contains(strtolower($output), 'error')
            ) {
                $message = 'Backup failed: ' . substr(trim($output), 0, 300);
                session()->flash('error', $message);
                $this->dispatch('toast', message: $message, type: 'error');
            } else {
                LogsActivity::logCustomActivity(
                    logName: 'backup',
                    event: 'created',
                    description: 'Generated a manual database backup archive',
                    properties: ['type' => 'manual_db_backup']
                );

                $message = 'Database backup created successfully.';
                session()->flash('success', $message);
                $this->dispatch('toast', message: $message, type: 'success');
            }
        } catch (Throwable $e) {
            Log::error('Error creating database backup: ' . $e->getMessage());
            $message = 'Backup failed: ' . $e->getMessage();
            session()->flash('error', $message);
            $this->dispatch('toast', message: $message, type: 'error');
        } finally {
            $this->isCreatingBackup = false;
        }
    }

    public function downloadBackup(string $fileName)
    {
        try {
            if (! $this->isSafeBackupPath($fileName)) {
                session()->flash('error', 'Invalid backup file path.');
                $this->dispatch('toast', message: 'Invalid backup file path.', type: 'error');
                return null;
            }

            $disk = $this->resolveBackupDisk();

            if ($disk->exists($fileName)) {
                LogsActivity::logCustomActivity(
                    logName: 'backup',
                    event: 'downloaded',
                    description: 'Downloaded database backup archive: ' . basename($fileName),
                    properties: ['file' => $fileName]
                );

                return $disk->download($fileName);
            }

            session()->flash('error', 'Backup file not found.');
            $this->dispatch('toast', message: 'Backup file not found.', type: 'error');
            return null;
        } catch (Throwable $e) {
            Log::error('Error downloading backup archive: ' . $e->getMessage());
            session()->flash('error', 'Unable to download backup archive.');
            $this->dispatch('toast', message: 'Unable to download backup archive.', type: 'error');
            return null;
        }
    }

    public function deleteBackup(string $fileName): void
    {
        try {
            if (! $this->isSafeBackupPath($fileName)) {
                session()->flash('error', 'Invalid backup file path.');
                $this->dispatch('toast', message: 'Invalid backup file path.', type: 'error');
                return;
            }

            $disk = $this->resolveBackupDisk();

            if ($disk->exists($fileName)) {
                $disk->delete($fileName);

                LogsActivity::logCustomActivity(
                    logName: 'backup',
                    event: 'deleted',
                    description: 'Deleted database backup archive: ' . basename($fileName),
                    properties: ['file' => $fileName]
                );

                session()->flash('success', 'Backup file deleted.');
                $this->dispatch('toast', message: 'Backup file deleted.', type: 'success');
            } else {
                session()->flash('error', 'File could not be found.');
                $this->dispatch('toast', message: 'File could not be found.', type: 'error');
            }
        } catch (Throwable $e) {
            Log::error('Error deleting backup archive: ' . $e->getMessage());
            session()->flash('error', 'Unable to delete backup archive.');
            $this->dispatch('toast', message: 'Unable to delete backup archive.', type: 'error');
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Database Backups')]
    public function render()
    {
        try {
            $disk = $this->resolveBackupDisk();

            $files = collect($disk->allFiles())
                ->filter(fn ($file) => str_ends_with(strtolower((string) $file), '.zip'))
                ->values();

            $backups = $files->map(function ($file) use ($disk) {
                return [
                    'file_name'     => (string) $file,
                    'file_size'     => (int) $disk->size($file),
                    'last_modified' => (int) $disk->lastModified($file),
                ];
            })->sortByDesc('last_modified')->values();

            $stats = [
                'total_backups' => $backups->count(),
                'total_size'    => (int) $backups->sum('file_size'),
                'last_backup'   => $backups->first()['last_modified'] ?? null,
            ];
        } catch (Throwable $e) {
            Log::error('Error loading backup archives: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to load backup archives.', type: 'error');

            $backups = collect();
            $stats = [
                'total_backups' => 0,
                'total_size'    => 0,
                'last_backup'   => null,
            ];
        }

        return view('livewire.database-backups', [
            'backups' => $backups,
            'stats'   => $stats,
        ]);
    }
}
