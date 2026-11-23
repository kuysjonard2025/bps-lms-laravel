<?php

namespace App\Imports\Accessions;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AccessionsImportRunner implements Import, WithMultipleSheets
{
    private AccessionsImportFirstSheetSaveReader $sheetReader;

    private AccessionsImportPersister $persister;

    public function __construct(
        ?AccessionsImportValidator $validator = null,
        ?AccessionsImportPersister $persister = null
    ) {
        $this->persister = $persister ?? new AccessionsImportPersister();
        $this->sheetReader = new AccessionsImportFirstSheetSaveReader(
            $validator ?? new AccessionsImportValidator(),
            $this->persister
        );
    }

    public function sheets(): array
    {
        return [
            0 => $this->sheetReader,
        ];
    }

    public function createdCount(): int
    {
        return $this->persister->createdCount;
    }

    public function updatedCount(): int
    {
        return $this->persister->updatedCount;
    }

    /**
     * @return array<int, array{row: int, column: string, reason: string}>
     */
    public function failures(): array
    {
        return $this->sheetReader->failures;
    }
}
