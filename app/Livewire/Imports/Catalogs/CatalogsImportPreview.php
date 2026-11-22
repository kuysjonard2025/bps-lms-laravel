<?php // app/Livewire/Imports/Catalogs/CatalogsImportPreview.php

namespace App\Livewire\Imports\Catalogs;

use App\Imports\Catalogs\CatalogImportColumns;
use App\Imports\Catalogs\CatalogImportErrorRowsExport;
use App\Imports\Catalogs\CatalogImportPersister;
use App\Imports\Catalogs\CatalogImportRowValidator;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CatalogsImportPreview extends Component
{
    public ?string $stagedPath = null;

    public ?string $originalName = null;

    public int $fileSize = 0;

    public bool $hasPreview = false;

    public bool $headersValid = true;

    /** @var array<int, string> */
    public array $headerErrors = [];

    public int $totalRows = 0;

    public int $validCount = 0;

    public int $errorCount = 0;

    public int $importedCount = 0;

    public int $skippedCount = 0;

    /** @var array<int, string> */
    public array $importMessages = [];

    public string $filterStatus = 'all'; // all | errors | valid

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    #[On('catalog-import-file-staged')]
    public function loadStagedFile(string $path, ?string $originalName = null, int $size = 0): void
    {
        try {
            $user = auth()->user();

            if (! $user) {
                $this->dispatch('toast', message: 'You must be logged in to preview catalog imports.', type: 'error');
                return;
            }

            if (method_exists($user, 'hasRole') && ! $user->hasRole('librarian')) {
                $this->dispatch('toast', message: 'Only users with the librarian role can preview catalog imports.', type: 'error');
                return;
            }

            $validator = app(CatalogImportRowValidator::class);
            $report = $validator->validateStagedFile($path);

            $this->stagedPath = $path;
            $this->originalName = $originalName ?? basename($path);
            $this->fileSize = $size;
            $this->headersValid = $report['headers_valid'];
            $this->headerErrors = $report['header_errors'];
            $this->totalRows = $report['total_rows'];
            $this->validCount = $report['valid_count'];
            $this->errorCount = $report['error_count'];
            $this->importedCount = 0;
            $this->skippedCount = 0;
            $this->importMessages = [];
            $this->rows = $report['rows'];
            $this->filterStatus = $this->errorCount > 0 ? 'errors' : 'all';
            $this->hasPreview = true;
        } catch (Throwable $e) {
            Log::error('Failed validating staged catalog import file: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
                'path'      => $path,
            ]);

            $this->dispatch('toast', message: 'Unable to parse or validate the staged file right now.', type: 'error');
        }
    }

    public function importValidRows(): void
    {
        try {
            $user = auth()->user();

            if (! $user) {
                $this->dispatch('toast', message: 'You must be logged in to import catalogs.', type: 'error');
                return;
            }

            if (method_exists($user, 'hasRole') && ! $user->hasRole('librarian')) {
                $this->dispatch('toast', message: 'Only users with the librarian role can import catalogs.', type: 'error');
                return;
            }

            if (! $this->stagedPath || ! $this->headersValid || $this->validCount < 1) {
                $this->dispatch('toast', message: 'There are no valid rows ready to import.', type: 'error');
                return;
            }

            $persister = app(CatalogImportPersister::class);
            $result = $persister->persistStagedFile($this->stagedPath);

            $this->importedCount = (int) ($result['imported_count'] ?? 0);
            $this->skippedCount = (int) ($result['skipped_count'] ?? 0);
            $this->importMessages = is_array($result['messages'] ?? null) ? $result['messages'] : [];
            $this->stagedPath = null;

            $this->dispatch('catalogs-imported');
            $this->dispatch('catalog-import-reset');

            $summary = "Imported {$this->importedCount} catalog(s)";
            if ($this->skippedCount > 0) {
                $summary .= ", skipped {$this->skippedCount} duplicate(s)";
            }
            $summary .= '.';

            $this->dispatch(
                'toast',
                message: $summary,
                type: $this->importedCount > 0 ? 'success' : 'info',
            );
        } catch (Throwable $e) {
            Log::error('Failed importing catalog rows: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
                'path'      => $this->stagedPath,
            ]);

            $this->dispatch('toast', message: 'Unable to import valid catalog rows right now.', type: 'error');
        }
    }

    public function exportErrorRows(): BinaryFileResponse|null
    {
        try {
            $user = auth()->user();

            if (! $user) {
                $this->dispatch('toast', message: 'You must be logged in to export error rows.', type: 'error');
                return null;
            }

            if (method_exists($user, 'hasRole') && ! $user->hasRole('librarian')) {
                $this->dispatch('toast', message: 'Only users with the librarian role can export error rows.', type: 'error');
                return null;
            }

            $invalidRows = array_values(array_filter(
                $this->rows,
                static fn ($row) => empty($row['is_valid']),
            ));

            if (empty($invalidRows)) {
                $this->dispatch('toast', message: 'There are no invalid rows to export.', type: 'info');
                return null;
            }

            $baseName = pathinfo((string) ($this->originalName ?? 'catalogs-import'), PATHINFO_FILENAME);
            $safeBase = preg_replace('/[^A-Za-z0-9._-]+/', '-', $baseName) ?: 'catalogs-import';
            $fileName = "{$safeBase}-error-rows-" . now()->format('Y-m-d') . '.xlsx';

            return Excel::download(new CatalogImportErrorRowsExport($invalidRows), $fileName);
        } catch (Throwable $e) {
            Log::error('Failed exporting catalog import error rows: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
            ]);

            $this->dispatch('toast', message: 'Unable to export error rows right now.', type: 'error');
            return null;
        }
    }

    public function setFilter(string $status): void
    {
        if (in_array($status, ['all', 'errors', 'valid'], true)) {
            $this->filterStatus = $status;
        }
    }

    public function clearPreview(): void
    {
        $this->stagedPath = null;
        $this->originalName = null;
        $this->fileSize = 0;
        $this->hasPreview = false;
        $this->headersValid = true;
        $this->headerErrors = [];
        $this->totalRows = 0;
        $this->validCount = 0;
        $this->errorCount = 0;
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->importMessages = [];
        $this->filterStatus = 'all';
        $this->rows = [];

        $this->dispatch('catalog-import-reset');
    }

    public function getFilteredRowsProperty(): array
    {
        if ($this->filterStatus === 'errors') {
            return array_values(array_filter($this->rows, static fn ($row) => empty($row['is_valid'])));
        }

        if ($this->filterStatus === 'valid') {
            return array_values(array_filter($this->rows, static fn ($row) => ! empty($row['is_valid'])));
        }

        return $this->rows;
    }

    public function render()
    {
        return view('livewire.imports.catalogs.catalogs-import-preview', [
            'headings'     => CatalogImportColumns::headings(),
            'filteredRows' => $this->filteredRows,
        ]);
    }
}
