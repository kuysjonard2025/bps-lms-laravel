<?php

namespace App\Imports\Accessions;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class AccessionsImportFirstSheetReader extends DefaultValueBinder implements
    Import,
    ToCollection,
    WithStartRow,
    WithChunkReading,
    WithCustomValueBinder,
    WithEvents,
    SkipsEmptyRows
{
    /**
     * @var array<int, string>
     */
    public array $headingErrors = [];

    public int $validRows = 0;

    /**
     * @var array<int, array{row: int, column: string, reason: string}>
     */
    public array $failures = [];

    /**
     * @var array<int, string>
     */
    private array $columnFieldMap = [];

    private bool $headingsChecked = false;

    private int $nextExpectedRow = 2;

    public function __construct(
        private readonly AccessionsImportValidator $validator
    ) {
        $this->validator->resetRunState();
    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if ($value !== null && $value !== '') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event): void {
                if ($this->headingsChecked) {
                    return;
                }

                $this->headingsChecked = true;
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $rowOneValues = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, false)[0] ?? [];

                $headingToField = [];
                foreach (AccessionImportColumns::definitions() as $field => $def) {
                    $headingToField[strtolower(trim($def['heading']))] = $field;
                }

                $foundFields = [];
                foreach ($rowOneValues as $colIndex => $cellHeading) {
                    $normalizedHeading = strtolower(trim((string) ($cellHeading ?? '')));
                    if ($normalizedHeading !== '' && isset($headingToField[$normalizedHeading])) {
                        $field = $headingToField[$normalizedHeading];
                        $this->columnFieldMap[$colIndex] = $field;
                        $foundFields[$field] = true;
                    }
                }

                foreach (AccessionImportColumns::definitions() as $field => $def) {
                    if ($def['required'] && ! isset($foundFields[$field])) {
                        $this->headingErrors[] = "Missing required column heading \"{$def['heading']}\" in row 1.";
                    }
                }
            },
        ];
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $rowCollection) {
            $rowNumber = $this->nextExpectedRow++;
            $rawRow = $rowCollection->toArray();

            if ($this->validator->isEmptyRow($rawRow) || $this->validator->isTotalsOrFooterRow($rawRow)) {
                continue;
            }

            $mappedRow = [];
            foreach ($this->columnFieldMap as $colIndex => $field) {
                $mappedRow[$field] = $rawRow[$colIndex] ?? null;
            }

            $rowErrors = $this->validator->validateRow($mappedRow, $rowNumber);

            if ($rowErrors === []) {
                $this->validRows++;
            } else {
                foreach ($rowErrors as $error) {
                    $this->failures[] = [
                        'row'    => $rowNumber,
                        'column' => $error['column'],
                        'reason' => $error['reason'],
                    ];
                }
            }
        }
    }
}
