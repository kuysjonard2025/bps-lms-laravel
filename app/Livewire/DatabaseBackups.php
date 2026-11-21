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
use Throwable;

class DatabaseBackups extends Component
{
    use SanitizesInputs;

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
            $exitCode = Artisan::call('backup:run', ['--only-db' => true]);
            $output = trim(Artisan::output());

            $lower = strtolower($output);
            $hasError = $exitCode !== 0
                || str_contains($lower, 'backup failed')
                || str_contains($lower, 'exception')
                || str_contains($lower, 'error:');

            if ($hasError) {
                Log::error('Spatie backup:run failed', [
                    'exit_code' => $exitCode,
                    'output'    => $output,
                ]);

                $message = 'Backup failed on server. Check dump client (pg_dump/mysqldump), DB_DUMP_BINARY_PATH, and storage permissions.';
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
            Log::error('Exception during createBackup: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $message = 'Backup failed due to a server error. Check server logs.';
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
                $message = 'Invalid backup file path.';
                session()->flash('error', $message);
                $this->dispatch('toast', message: $message, type: 'error');
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

            $message = 'Backup file not found.';
            session()->flash('error', $message);
            $this->dispatch('toast', message: $message, type: 'error');
            return null;
        } catch (Throwable $e) {
            Log::error('Error downloading backup archive: ' . $e->getMessage());
            $message = 'Unable to download backup archive.';
            session()->flash('error', $message);
            $this->dispatch('toast', message: $message, type: 'error');
            return null;
        }
    }

    public function deleteBackup(string $fileName): void
    {
        try {
            if (! $this->isSafeBackupPath($fileName)) {
                $message = 'Invalid backup file path.';
                session()->flash('error', $message);
                $this->dispatch('toast', message: $message, type: 'error');
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

                $message = 'Backup file deleted.';
                session()->flash('success', $message);
                $this->dispatch('toast', message: $message, type: 'success');
            } else {
                $message = 'File could not be found.';
                session()->flash('error', $message);
                $this->dispatch('toast', message: $message, type: 'error');
            }
        } catch (Throwable $e) {
            Log::error('Error deleting backup archive: ' . $e->getMessage());
            $message = 'Unable to delete backup archive.';
            session()->flash('error', $message);
            $this->dispatch('toast', message: $message, type: 'error');
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
