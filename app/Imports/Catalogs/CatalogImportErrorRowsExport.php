<?php // app/Imports/Catalogs/CatalogImportErrorRowsExport.php

namespace App\Imports\Catalogs;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CatalogImportErrorRowsExport implements Export, FromArray, WithHeadings, WithColumnFormatting, WithStyles, ShouldAutoSize
{
    /**
     * @param  array<int, array<string, mixed>>  $errorRows
     */
    public function __construct(
        private readonly array $errorRows,
    ) {}

    public function headings(): array
    {
        return [
            ...CatalogImportColumns::headings(),
            'validation_notes',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        $headings = CatalogImportColumns::headings();
        $rows = [];

        foreach ($this->errorRows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $data = is_array($row['data'] ?? null) ? $row['data'] : [];
            $rowErrors = is_array($row['errors'] ?? null) ? $row['errors'] : [];

            $notes = [];
            foreach ($rowErrors as $fieldMessages) {
                if (! is_array($fieldMessages)) {
                    continue;
                }
                foreach ($fieldMessages as $message) {
                    if ($message !== null && trim((string) $message) !== '') {
                        $notes[] = trim((string) $message);
                    }
                }
            }

            $sourceRow = (int) ($row['row_number'] ?? 0);
            $notesText = implode(' | ', $notes);
            if ($sourceRow > 0 && $notesText !== '') {
                $notesText = "Row #{$sourceRow}: {$notesText}";
            } elseif ($sourceRow > 0) {
                $notesText = "Row #{$sourceRow}: Invalid row";
            }

            $line = [];
            foreach ($headings as $heading) {
                $line[] = (string) ($data[$heading] ?? '');
            }
            $line[] = $notesText;

            $rows[] = $line;
        }

        return $rows;
    }

    public function columnFormats(): array
    {
        $formats = [];
        $headings = $this->headings();
        $definitions = CatalogImportColumns::definitions();

        foreach ($headings as $index => $heading) {
            $colLetter = Coordinate::stringFromColumnIndex($index + 1);
            $formatType = $definitions[$heading]['excel_format'] ?? 'text';

            $formats[$colLetter] = match ($formatType) {
                'integer' => NumberFormat::FORMAT_NUMBER,
                default   => NumberFormat::FORMAT_TEXT,
            };
        }

        return $formats;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
            ],
            "A1:{$lastColumn}1" => [
                'alignment' => ['vertical' => 'center'],
            ],
        ];
    }
}
