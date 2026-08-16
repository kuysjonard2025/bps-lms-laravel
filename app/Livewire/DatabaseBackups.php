<?php

namespace App\Livewire;

use App\Livewire\Traits\LogsActivity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class DatabaseBackups extends Component
{
    use WithPagination;

    public bool $isCreatingBackup = false;

    #[Layout('components.layouts.app')]
    #[Title('Database Backups')]
    public function createBackup(): void
    {
        $this->isCreatingBackup = true;

        try {
            Artisan::call('backup:run', ['--only-db' => true]);
            $output = Artisan::output();

            // Catch Spatie CLI errors if the command fails internally
            if (str_contains(strtolower($output), 'failed') || str_contains(strtolower($output), 'exception') || str_contains(strtolower($output), 'error')) {
                session()->flash('error', 'Backup failed: ' . substr($output, 0, 300));
            } else {
                // Log activity on successful creation
                LogsActivity::logCustomActivity(
                    logName: 'backup',
                    event: 'created',
                    description: 'Generated a manual database backup archive',
                    properties: ['type' => 'manual_db_backup']
                );

                session()->flash('success', 'Database backup created successfully!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Backup failed: ' . $e->getMessage());
        }

        $this->isCreatingBackup = false;
    }

    public function downloadBackup(string $fileName)
    {
        $diskName = config('backup.backup.destination.disks')[0] ?? 'local';

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);

        if ($disk->exists($fileName)) {
            // Log activity on file download
            LogsActivity::logCustomActivity(
                logName: 'backup',
                event: 'downloaded',
                description: 'Downloaded database backup archive: ' . basename($fileName),
                properties: ['file' => $fileName]
            );

            return $disk->download($fileName);
        }

        session()->flash('error', 'Backup file not found.');
    }

    public function deleteBackup(string $fileName): void
    {
        $diskName = config('backup.backup.destination.disks')[0] ?? 'local';

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);

        if ($disk->exists($fileName)) {
            $disk->delete($fileName);

            // Log activity on file deletion
            LogsActivity::logCustomActivity(
                logName: 'backup',
                event: 'deleted',
                description: 'Deleted database backup archive: ' . basename($fileName),
                properties: ['file' => $fileName]
            );

            session()->flash('success', 'Backup file deleted.');
        } else {
            session()->flash('error', 'File could not be found.');
        }
    }

    public function render()
    {
        $diskName = config('backup.backup.destination.disks')[0] ?? 'local';

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);

        // Recursively list all .zip files on the target disk
        $files = collect($disk->allFiles())
            ->filter(fn ($file) => str_ends_with($file, '.zip'))
            ->values();

        $backups = $files->map(function ($file) use ($disk) {
            return [
                'file_name'     => $file,
                'file_size'     => $disk->size($file),
                'last_modified' => $disk->lastModified($file),
            ];
        })->sortByDesc('last_modified')->values();

        $stats = [
            'total_backups' => $backups->count(),
            'total_size'    => $backups->sum('file_size'),
            'last_backup'   => $backups->first()['last_modified'] ?? null,
        ];

        return view('livewire.database-backups', [
            'backups' => $backups,
            'stats'   => $stats,
        ]);
    }
}
