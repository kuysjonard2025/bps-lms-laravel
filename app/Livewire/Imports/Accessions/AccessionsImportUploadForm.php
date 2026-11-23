<?php

namespace App\Livewire\Imports\Accessions;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class AccessionsImportUploadForm extends Component
{
    use WithFileUploads;

    #[Validate]
    public $file = null;

    public bool $isVerified = false;

    public ?string $stagedFileName = null;

    protected function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:5120',
                'extensions:xlsx',
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'file.required' => 'Please choose an .xlsx spreadsheet file before uploading.',
            'file.file' => 'The uploaded item must be a valid file.',
            'file.max' => 'The spreadsheet file must not exceed 5 MB.',
            'file.extensions' => 'Only .xlsx files are accepted. .csv and other formats are not allowed.',
            'file.mimetypes' => 'Only valid .xlsx Excel workbook files are accepted.',
        ];
    }

    public function updatedFile(): void
    {
        $this->isVerified = false;
        $this->stagedFileName = null;
        $this->resetErrorBag('file');

        if (! $this->file) {
            return;
        }

        $this->validateOnly('file');

        $this->stagedFileName = $this->file->getClientOriginalName();
        $this->isVerified = true;
    }

    public function clearFile(): void
    {
        $this->file = null;
        $this->isVerified = false;
        $this->stagedFileName = null;
        $this->resetErrorBag('file');
    }

    public function submitUpload(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('librarian')) {
            abort(403);
        }

        $this->validate();

        $storedPath = $this->file->store('imports/accessions', 'local');
        $originalName = $this->file->getClientOriginalName();
        $this->stagedFileName = $originalName;
        $this->isVerified = true;

        $this->dispatch('accessions-import-file-staged', storedPath: $storedPath, originalName: $originalName);
        $this->dispatch('toast', message: "Validated \"{$originalName}\" (no records saved).", type: 'success');
    }

    public function render()
    {
        return view('livewire.imports.accessions.accessions-import-upload-form');
    }
}
