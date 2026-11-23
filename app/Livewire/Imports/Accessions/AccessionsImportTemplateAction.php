<?php

namespace App\Livewire\Imports\Accessions;

use App\Exports\AccessionsImportTemplateExport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AccessionsImportTemplateAction extends Component
{
    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(
            new AccessionsImportTemplateExport(),
            'accessions-import-template.xlsx'
        );
    }

    public function render()
    {
        return view('livewire.imports.accessions.accessions-import-template-action');
    }
}
