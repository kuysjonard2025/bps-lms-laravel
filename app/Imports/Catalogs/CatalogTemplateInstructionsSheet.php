<?php // app/Imports/Catalogs/CatalogTemplateInstructionsSheet.php

namespace App\Imports\Catalogs;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CatalogTemplateInstructionsSheet implements
    FromArray,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    public function title(): string
    {
        return 'Instructions';
    }

    public function headings(): array
    {
        return [
            'Column Heading',
            'Field Label',
            'Required / Rule',
            'Unique Key Part',
            'Example Value',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach (CatalogImportColumns::definitions() as $def) {
            $rows[] = [
                (string) ($def['heading'] ?? ''),
                (string) ($def['label'] ?? ''),
                (string) ($def['required_note'] ?? (! empty($def['required']) ? 'Required' : 'Optional')),
                ! empty($def['part_of_unique_key']) ? 'Yes' : 'No',
                (string) ($def['example'] ?? ''),
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F172A'],
                ],
            ],
        ];
    }
}
