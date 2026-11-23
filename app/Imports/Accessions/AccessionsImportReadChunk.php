<?php

namespace App\Imports\Accessions;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AccessionsImportReadChunk implements Import, WithMultipleSheets
{
    private AccessionsImportFirstSheetReader $sheetReader;

    public function __construct(?AccessionsImportValidator $validator = null)
    {
        $this->sheetReader = new AccessionsImportFirstSheetReader(
            $validator ?? new AccessionsImportValidator()
        );
    }

    public function sheets(): array
    {
        return [
            0 => $this->sheetReader,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headingWarnings(): array
    {
        return $this->sheetReader->headingErrors;
    }

    public function validRowCount(): int
    {
        return $this->sheetReader->validRows;
    }

    /**
     * @return array<int, array{row: int, column: string, reason: string}>
     */
    public function failures(): array
    {
        return $this->sheetReader->failures;
    }
}
