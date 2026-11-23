<?php

namespace App\Exports;

use App\Imports\Accessions\AccessionImportColumns;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AccessionsImportTemplateDataSheet extends DefaultValueBinder implements
    Export,
    FromArray,
    WithHeadings,
    WithColumnFormatting,
    WithCustomValueBinder,
    WithEvents,
    WithTitle,
    ShouldAutoSize
{
    private const VALIDATION_MAX_ROW = 500;

    public function title(): string
    {
        return 'Accessions Import';
    }

    public function headings(): array
    {
        return AccessionImportColumns::headings();
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        $exampleRow = [];

        foreach (AccessionImportColumns::definitions() as $def) {
            $exampleRow[] = (string) $def['example'];
        }

        return [$exampleRow];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        $textHeadings = AccessionImportColumns::textIdentifierHeadings();
        $colIndex = Coordinate::columnIndexFromString($cell->getColumn());
        $headings = $this->headings();
        $heading = $headings[$colIndex - 1] ?? null;

        if ($heading !== null && in_array($heading, $textHeadings, true) && $value !== null && $value !== '') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        $formats = [];
        $textHeadings = AccessionImportColumns::textIdentifierHeadings();

        foreach (array_values(AccessionImportColumns::headings()) as $index => $heading) {
            if (in_array($heading, $textHeadings, true)) {
                $colLetter = Coordinate::stringFromColumnIndex($index + 1);
                $formats[$colLetter] = NumberFormat::FORMAT_TEXT;
            }
        }

        return $formats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => static function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $headings = array_values(AccessionImportColumns::headings());
                $allowedByHeading = AccessionImportColumns::allowedValuesByHeading();

                foreach ($headings as $index => $heading) {
                    if (! isset($allowedByHeading[$heading])) {
                        continue;
                    }

                    $colLetter = Coordinate::stringFromColumnIndex($index + 1);
                    $formulaList = '"' . implode(',', $allowedByHeading[$heading]) . '"';

                    for ($row = 2; $row <= self::VALIDATION_MAX_ROW; $row++) {
                        $validation = $sheet->getCell("{$colLetter}{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1($formulaList);
                    }
                }
            },
        ];
    }
}
