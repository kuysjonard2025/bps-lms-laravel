<?php // app/Imports/Catalogs/CatalogTemplateDataSheet.php

namespace App\Imports\Catalogs;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class CatalogTemplateDataSheet implements
    FromArray,
    WithHeadings,
    WithColumnFormatting,
    WithStyles,
    WithEvents,
    ShouldAutoSize,
    WithTitle
{
    public function title(): string
    {
        return 'Catalogs Import';
    }

    public function headings(): array
    {
        return CatalogImportColumns::headings();
    }

    public function array(): array
    {
        $definitions = CatalogImportColumns::definitions();
        $exampleRow = [];

        foreach ($definitions as $def) {
            $exampleRow[] = (string) ($def['example'] ?? '');
        }

        return [$exampleRow];
    }

    public function columnFormats(): array
    {
        $formats = [];
        $index = 1;

        foreach (CatalogImportColumns::definitions() as $def) {
            $colLetter = Coordinate::stringFromColumnIndex($index);

            if (! empty($def['excel_text'])) {
                $formats[$colLetter] = NumberFormat::FORMAT_TEXT;
            }

            $index++;
        }

        return $formats;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColLetter = Coordinate::stringFromColumnIndex(count(CatalogImportColumns::headings()));

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A'],
                ],
            ],
            "A2:{$lastColLetter}2" => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                try {
                    $sheet = $event->sheet->getDelegate();
                    $definitions = array_values(CatalogImportColumns::definitions());
                    $lastColIndex = count($definitions);
                    $lastColLetter = Coordinate::stringFromColumnIndex($lastColIndex);

                    foreach ($definitions as $idx => $def) {
                        $colIndex = $idx + 1;
                        $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                        $cellCoordinate = "{$colLetter}2";
                        $val = (string) ($def['example'] ?? '');

                        if (! empty($def['excel_text'])) {
                            $sheet->setCellValueExplicit($cellCoordinate, $val, DataType::TYPE_STRING);
                            $sheet->getStyle("{$colLetter}2:{$colLetter}1001")
                                ->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_TEXT);
                        }
                    }

                    $sheet->freezePane('A2');
                    $sheet->getStyle("A1:{$lastColLetter}1")->getAlignment()->setWrapText(true);
                } catch (Throwable $e) {
                    Log::error('Error styling CatalogTemplateDataSheet: ' . $e->getMessage(), [
                        'exception' => $e,
                    ]);
                }
            },
        ];
    }
}
