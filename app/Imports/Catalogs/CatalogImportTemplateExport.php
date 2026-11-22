<?php // app/Imports/Catalogs/CatalogImportTemplateExport.php

namespace App\Imports\Catalogs;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CatalogImportTemplateExport implements Export, WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new CatalogTemplateDataSheet(),
            new CatalogTemplateInstructionsSheet(),
        ];
    }
}
