<?php // app/Livewire/Imports/Catalogs/CatalogsImportTemplateAction.php

namespace App\Livewire\Imports\Catalogs;

use App\Imports\Catalogs\CatalogImportTemplateExport;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CatalogsImportTemplateAction extends Component
{
    public function downloadTemplate(): ?BinaryFileResponse
    {
        try {
            $user = auth()->user();

            if (! $user) {
                $this->dispatch('toast', message: 'You must be logged in to download the catalog import template.', type: 'error');
                return null;
            }

            if (method_exists($user, 'hasRole') && ! $user->hasRole('librarian')) {
                $this->dispatch('toast', message: 'Only users with the librarian role can download the catalog import template.', type: 'error');
                return null;
            }

            $fileName = 'catalogs-import-template-' . now()->format('Y-m-d') . '.xlsx';

            return Excel::download(new CatalogImportTemplateExport(), $fileName);
        } catch (Throwable $e) {
            Log::error('Failed downloading catalog import template: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
            ]);

            $this->dispatch('toast', message: 'Unable to download catalog import template right now.', type: 'error');
            return null;
        }
    }

    public function render()
    {
        return view('livewire.imports.catalogs.catalogs-import-template-action');
    }
}
