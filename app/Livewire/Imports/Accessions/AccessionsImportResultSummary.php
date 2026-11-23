<?php

namespace App\Livewire\Imports\Accessions;

use Livewire\Attributes\On;
use Livewire\Component;

class AccessionsImportResultSummary extends Component
{
    public bool $hasResult = false;

    public ?string $fileName = null;

    public int $createdCount = 0;

    public int $updatedCount = 0;

    /**
     * @var array<int, array{row: int, column: string, reason: string}>
     */
    public array $failures = [];

    /**
     * @param  array<int, array{row: int, column: string, reason: string}>  $failures
     */
    #[On('accessions-import-completed')]
    public function showSummary(
        string $fileName,
        int $createdCount,
        int $updatedCount,
        array $failures = []
    ): void {
        $this->fileName = $fileName;
        $this->createdCount = $createdCount;
        $this->updatedCount = $updatedCount;
        $this->failures = $failures;
        $this->hasResult = true;
    }

    #[On('accessions-import-file-staged')]
    public function resetOnNewStage(): void
    {
        $this->hasResult = false;
        $this->fileName = null;
        $this->createdCount = 0;
        $this->updatedCount = 0;
        $this->failures = [];
    }

    public function render()
    {
        return view('livewire.imports.accessions.accessions-import-result-summary');
    }
}
