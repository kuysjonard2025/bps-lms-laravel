<?php // app/Livewire/Imports/Catalogs/CatalogsImportUploadForm.php

namespace App\Livewire\Imports\Catalogs;

use App\Imports\Catalogs\CatalogImportColumns;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;

class CatalogsImportUploadForm extends Component
{
    use WithFileUploads;

    private const MAX_FILE_SIZE_BYTES = 10485760; // 10 MB (10240 * 1024)

    private const ALLOWED_EXTENSIONS = ['xlsx', 'csv'];

    public ?TemporaryUploadedFile $importFile = null;

    public ?string $stagedFilePath = null;

    public ?string $stagedOriginalName = null;

    public int $stagedFileSize = 0;

    public bool $isUploading = false;

    public function updatedImportFile(): void
    {
        try {
            if (! $this->importFile) {
                return;
            }

            $this->resetErrorBag('importFile');
            $this->validateFileUpload();
            $this->stageUploadedFile();
        } catch (ValidationException $e) {
            $this->clearStagedFile();
            throw $e;
        } catch (Throwable $e) {
            $this->clearStagedFile();
            Log::error('Failed staging catalog import file: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
            ]);
            $this->dispatch('toast', message: 'Unable to stage the uploaded spreadsheet right now.', type: 'error');
        }
    }

    public function prepareUpload(): void
    {
        try {
            $user = auth()->user();

            if (! $user) {
                $this->dispatch('toast', message: 'You must be logged in to upload a catalog import file.', type: 'error');
                return;
            }

            if (method_exists($user, 'hasRole') && ! $user->hasRole('librarian')) {
                $this->dispatch('toast', message: 'Only users with the librarian role can upload catalog import files.', type: 'error');
                return;
            }

            if ($this->importFile) {
                $this->resetErrorBag('importFile');
                $this->validateFileUpload();

                if (! $this->stagedFilePath) {
                    $this->stageUploadedFile();
                }
            }

            if (! $this->stagedFilePath) {
                $this->dispatch('toast', message: 'Please select an .xlsx or .csv file first.', type: 'error');
                return;
            }

            $this->dispatch(
                'catalog-import-file-staged',
                path: $this->stagedFilePath,
                originalName: $this->stagedOriginalName,
                size: $this->stagedFileSize,
                columns: CatalogImportColumns::headings(),
            );

            $this->dispatch(
                'toast',
                message: 'File "' . ($this->stagedOriginalName ?? 'import') . '" is staged and ready for validation.',
                type: 'success',
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed preparing catalog import file: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
            ]);
            $this->dispatch('toast', message: 'Unable to prepare the catalog import file right now.', type: 'error');
        }
    }

    #[On('catalog-import-reset')]
    public function resetUpload(): void
    {
        try {
            $this->clearStagedFile();
            $this->resetValidation();
        } catch (Throwable $e) {
            Log::error('Failed resetting catalog import upload form: ' . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
            ]);
            $this->dispatch('toast', message: 'Unable to reset the upload form right now.', type: 'error');
        }
    }

    protected function validateFileUpload(): void
    {
        $rules = [
            'importFile' => [
                'required',
                'file',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $value instanceof TemporaryUploadedFile) {
                        $fail('Only Excel (.xlsx) or CSV (.csv) files are supported.');
                        return;
                    }

                    $ext = strtolower(
                        pathinfo((string) $value->getClientOriginalName(), PATHINFO_EXTENSION)
                            ?: (string) $value->getClientOriginalExtension()
                    );

                    if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                        $fail('Only Excel (.xlsx) or CSV (.csv) files are supported.');
                        return;
                    }

                    $size = $this->resolveUploadSize($value);
                    if ($size > self::MAX_FILE_SIZE_BYTES) {
                        $fail('The import file must not exceed 10 MB.');
                    }
                },
            ],
        ];

        $messages = [
            'importFile.required' => 'Please choose a catalog import spreadsheet (.xlsx or .csv).',
            'importFile.file'     => 'Please choose a valid catalog import file.',
        ];

        $attributes = [
            'importFile' => 'Catalog import file',
        ];

        $this->validate($rules, $messages, $attributes);
    }

    protected function resolveUploadSize(TemporaryUploadedFile $file, ?string $stagedPath = null): int
    {
        $realPath = $file->getRealPath();
        if ($realPath && is_file($realPath)) {
            $fsSize = filesize($realPath);
            if ($fsSize !== false) {
                return (int) $fsSize;
            }
        }

        $candidatePath = $stagedPath ?? $this->stagedFilePath;
        if ($candidatePath && Storage::disk('local')->exists($candidatePath)) {
            return (int) (Storage::disk('local')->size($candidatePath) ?: 0);
        }

        try {
            $size = $file->getSize();
            if ($size !== false && $size !== null) {
                return (int) $size;
            }
        } catch (Throwable) {
            // Avoid Flysystem UnableToRetrieveMetadata on Windows temporary paths
        }

        return 0;
    }

    protected function stageUploadedFile(): void
    {
        if (! $this->importFile) {
            return;
        }

        if ($this->stagedFilePath && Storage::disk('local')->exists($this->stagedFilePath)) {
            Storage::disk('local')->delete($this->stagedFilePath);
        }

        $userId = auth()->id() ?? 'guest';
        $extension = strtolower(
            pathinfo((string) $this->importFile->getClientOriginalName(), PATHINFO_EXTENSION)
                ?: ($this->importFile->getClientOriginalExtension() ?: 'xlsx')
        );
        $fileName = now()->format('Ymd_His') . '_' . uniqid('', true) . '.' . $extension;

        $storedPath = $this->importFile->storeAs(
            'imports/catalogs/staged/' . $userId,
            $fileName,
            'local',
        );

        $this->stagedFilePath = $storedPath;
        $this->stagedOriginalName = $this->importFile->getClientOriginalName();
        $this->stagedFileSize = $this->resolveUploadSize($this->importFile, $storedPath);
    }

    protected function clearStagedFile(): void
    {
        if ($this->stagedFilePath && Storage::disk('local')->exists($this->stagedFilePath)) {
            Storage::disk('local')->delete($this->stagedFilePath);
        }

        $this->importFile = null;
        $this->stagedFilePath = null;
        $this->stagedOriginalName = null;
        $this->stagedFileSize = 0;
    }

    public function render()
    {
        return view('livewire.imports.catalogs.catalogs-import-upload-form');
    }
}
