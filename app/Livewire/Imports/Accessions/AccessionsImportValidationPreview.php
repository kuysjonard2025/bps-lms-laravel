<?php

namespace App\Livewire\Imports\Accessions;

use App\Imports\Accessions\AccessionsImportReadChunk;
use App\Imports\Accessions\AccessionsImportRunner;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class AccessionsImportValidationPreview extends Component
{
    public bool $hasValidated = false;

    public ?string $stagedPath = null;

    public ?string $fileName = null;

    public int $validRowsCount = 0;

    public int $createdCount = 0;

    public int $updatedCount = 0;

    public bool $hasSaved = false;

    /**
     * @var array<int, string>
     */
    public array $headingErrors = [];

    /**
     * @var array<int, array{row: int, column: string, reason: string}>
     */
    public array $failures = [];

    #[On('accessions-import-file-staged')]
    public function validateStagedFile(string $storedPath, string $originalName): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('librarian')) {
            abort(403);
        }

        $this->stagedPath = $storedPath;
        $this->fileName = $originalName;
        $this->headingErrors = [];
        $this->failures = [];
        $this->validRowsCount = 0;
        $this->createdCount = 0;
        $this->updatedCount = 0;
        $this->hasSaved = false;

        $reader = new AccessionsImportReadChunk();
        Excel::import($reader, $storedPath, 'local');

        $this->headingErrors = $reader->headingWarnings();
        $this->validRowsCount = $reader->validRowCount();
        $this->failures = $reader->failures();
        $this->hasValidated = true;
    }

    public function importValidRows(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('librarian')) {
            abort(403);
        }

        if (! $this->stagedPath || $this->headingErrors !== []) {
            return;
        }

        $runner = new AccessionsImportRunner();
        Excel::import($runner, $this->stagedPath, 'local');

        $this->createdCount = $runner->createdCount();
        $this->updatedCount = $runner->updatedCount();
        $this->failures = $runner->failures();
        $this->hasSaved = true;

        $failedCount = count($this->failures);
        $headline = "{$this->createdCount} created, {$this->updatedCount} updated, {$failedCount} failed.";

        $this->dispatch('toast', message: $headline, type: $failedCount > 0 ? 'warning' : 'success');
    }

    public function render()
    {
        return view('livewire.imports.accessions.accessions-import-validation-preview');
    }
}
