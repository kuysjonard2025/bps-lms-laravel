<?php

namespace App\Livewire;

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
            // Runs Spatie backup or Laravel DB dump command
            Artisan::call('backup:run', ['--only-db' => true]);
            session()->flash('success', 'Database backup created successfully!');
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
        $files = $disk->allFiles(config('backup.backup.name', 'laravel-backup'));

        $backups = collect($files)->map(function ($file) use ($disk) {
            return [
                'file_name'     => $file,
                'file_size'     => $disk->size($file),
                'last_modified' => $disk->lastModified($file),
            ];
        })->sortByDesc('last_modified');

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
