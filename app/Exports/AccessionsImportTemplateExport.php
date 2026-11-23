<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AccessionsImportTemplateExport implements Export, WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new AccessionsImportTemplateDataSheet(),
            new AccessionsImportTemplateInstructionsSheet(),
        ];
    }
}
