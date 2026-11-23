<?php

namespace App\Exports;

use App\Imports\Accessions\AccessionImportColumns;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AccessionsImportTemplateInstructionsSheet implements Export, FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'Instructions';
    }

    public function headings(): array
    {
        return [
            'Column',
            'Required',
            'Max Length',
            'Allowed Values / Lookup',
            'Example',
            'Notes',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        $rows = [];

        foreach (AccessionImportColumns::definitions() as $def) {
            $constraint = '—';
            if ($def['allowed_values'] !== null) {
                $constraint = 'Allowed: ' . implode(', ', $def['allowed_values']);
            } elseif ($def['lookup'] !== null) {
                $constraint = 'Lookup: ' . $def['lookup'];
            }

            $rows[] = [
                $def['heading'],
                $def['required'] ? 'Yes' : 'No',
                $def['max_length'] !== null ? (string) $def['max_length'] : '—',
                $constraint,
                $def['example'],
                $def['description'],
            ];
        }

        return $rows;
    }
}
