<?php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Vendor;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Vendors extends Component
{
    use WithPagination, SanitizesInputs;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $vendorIdBeingEdited = null;
    public ?int $vendorIdBeingDeleted = null;

    // Form inputs matching migration & model schema
    public string $type = 'supplier';
    public string $company_name = '';
    public ?string $company_address = null;
    public string $contact_first_name = '';
    public ?string $contact_middle_name = null;
    public string $contact_last_name = '';
    public ?string $contact_number = null;
    public ?string $contact_email = null;

    protected function rules(): array
    {
        $isSupplier = $this->type === 'supplier';

        return [
            'type' => [
                'required',
                'string',
                'in:supplier,donor',
            ],
            'company_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('vendors', 'company_name')
                    ->where('company_address', trim($this->company_address))
                    ->ignore($this->vendorIdBeingEdited),
            ],
            'company_address' => [
                $isSupplier ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
            'contact_first_name' => [
                'required',
                'string',
                'max:100',
            ],
            'contact_middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'contact_last_name' => [
                'required',
                'string',
                'max:100',
            ],
            'contact_number' => [
                $isSupplier ? 'required' : 'nullable',
                'nullable',
                'digits_between:10,15',
                Rule::unique('vendors', 'contact_number')
                    ->ignore($this->vendorIdBeingEdited),
            ],
            'contact_email' => [
                $isSupplier ? 'required' : 'nullable',
                'nullable',
                'email',
                'max:100',
                Rule::unique('vendors', 'contact_email')
                    ->ignore($this->vendorIdBeingEdited),
            ],
        ];
    }

    protected array $validationAttributes = [
        'type'                => 'Vendor / Donor Type',
        'company_name'        => 'Company / Organization Name',
        'company_address'     => 'Address',
        'contact_first_name'  => 'Contact First Name',
        'contact_middle_name' => 'Contact Middle Name',
        'contact_last_name'   => 'Contact Last Name',
        'contact_number'      => 'Contact Number',
        'contact_email'       => 'Contact Email',
    ];

    public function updatedContactMiddleName($value): void
    {
        if (blank($value)) $this->contact_middle_name = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(Vendor $vendor): void
    {
        $this->resetValidation();
        $this->vendorIdBeingEdited = $vendor->id;
        $this->type                = $vendor->type;
        $this->company_name        = $vendor->company_name;
        $this->company_address     = $vendor->company_address;
        $this->contact_first_name  = $vendor->contact_first_name;
        $this->contact_middle_name = $vendor->contact_middle_name;
        $this->contact_last_name   = $vendor->contact_last_name;
        $this->contact_number      = $vendor->contact_number;
        $this->contact_email       = $vendor->contact_email;
        $this->showModal = true;
    }

    public function saveVendor(): void
    {
        $this->cleanFields([
            'company_name',
            'company_address',
            'contact_first_name',
            'contact_middle_name',
            'contact_last_name',
            'contact_number',
            'contact_email',
        ]);

        $this->contact_middle_name = blank($this->contact_middle_name) ? null : $this->contact_middle_name;
        $this->company_address     = blank($this->company_address) ? null : $this->company_address;
        $this->contact_number      = blank($this->contact_number) ? null : $this->contact_number;
        $this->contact_email       = blank($this->contact_email) ? null : $this->contact_email;

        $validated = $this->validate();

        try {
            Vendor::updateOrCreate(
                ['id' => $this->vendorIdBeingEdited],
                $validated
            );
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'company_name' => 'A vendor or donor with this exact name and address combination already exists.',
            ]);
        }

        $message = $this->vendorIdBeingEdited ? 'Record updated successfully.' : 'Record created successfully.';

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->vendorIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deleteVendor(): void
    {
        if ($this->vendorIdBeingDeleted) {
            try {
                Vendor::destroy($this->vendorIdBeingDeleted);
                $this->dispatch('toast', message: 'Record deleted successfully.', type: 'success');
            } catch (QueryException $e) {
                $this->dispatch('toast', message: 'Cannot delete record: It is currently linked to existing transactions or records.', type: 'error');
            }
        }

        $this->showDeleteModal = false;
        $this->vendorIdBeingDeleted = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'type',
            'company_name',
            'company_address',
            'contact_first_name',
            'contact_middle_name',
            'contact_last_name',
            'contact_number',
            'contact_email',
            'vendorIdBeingEdited',
        ]);
        $this->type = 'supplier';
        $this->resetValidation();
    }

    #[Layout('components.layouts.app')]
    #[Title('Vendors & Donors')]
    public function render()
    {
        $vendors = Vendor::query()
            ->when(trim($this->search), fn ($query, $term) => $query->search($term))
            ->latest()
            ->paginate(10);

        return view('livewire.vendors', [
            'vendors' => $vendors,
        ]);
    }
}
