<?php

namespace App\Livewire;

use App\Models\Vendor;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Vendors extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $vendorIdBeingEdited = null;
    public ?int $vendorIdBeingDeleted = null;

    public string $company_name = '';
    public string $contact_person = '';
    public string $address = '';
    public string $contact_number = '';
    public string $email = '';

    protected function rules(): array
    {
        return [
            'company_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('vendors', 'company_name')
                    ->where('address', trim($this->address))
                    ->ignore($this->vendorIdBeingEdited),
            ],
            'contact_person' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('vendors', 'contact_person')->ignore($this->vendorIdBeingEdited),
            ],
            'address' => [
                'required',
                'string',
                'max:100',
            ],
            'contact_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('vendors', 'contact_number')->ignore($this->vendorIdBeingEdited),
            ],
            'email' => [
                'nullable',
                'email',
                'max:50',
                Rule::unique('vendors', 'email')->ignore($this->vendorIdBeingEdited),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'company_name.unique' => 'A vendor with this exact company name and address combination already exists.',
            'contact_person.unique' => 'This contact person is already assigned to another vendor.',
            'contact_number.unique' => 'This contact number is already registered to another vendor.',
            'email.unique' => 'This email address is already registered to another vendor.',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['company_name', 'contact_person', 'address', 'contact_number', 'email', 'vendorIdBeingEdited']);
        $this->showModal = true;
    }

    public function openEditModal(Vendor $vendor): void
    {
        $this->resetValidation();
        $this->vendorIdBeingEdited = $vendor->id;
        $this->company_name = $vendor->company_name;
        $this->contact_person = $vendor->contact_person ?? '';
        $this->address = $vendor->address;
        $this->contact_number = $vendor->contact_number ?? '';
        $this->email = $vendor->email ?? '';
        $this->showModal = true;
    }

    public function saveVendor(): void
    {
        $this->validate();

        Vendor::updateOrCreate(
            ['id' => $this->vendorIdBeingEdited],
            [
                'company_name' => trim($this->company_name),
                'contact_person' => trim($this->contact_person) ?: null,
                'address' => trim($this->address),
                'contact_number' => trim($this->contact_number) ?: null,
                'email' => trim($this->email) ?: null,
            ]
        );

        $message = $this->vendorIdBeingEdited ? 'Vendor updated successfully.' : 'Vendor created successfully.';

        $this->showModal = false;
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
            Vendor::destroy($this->vendorIdBeingDeleted);
            $this->dispatch('toast', message: 'Vendor deleted successfully.', type: 'success');
        }

        $this->showDeleteModal = false;
        $this->vendorIdBeingDeleted = null;
    }

    #[Layout('components.layouts.app')]
    #[Title('Vendors')]
    public function render()
    {
        $vendors = Vendor::query()
            ->when($this->search, function ($query) {
                $query->where('company_name', 'like', "%{$this->search}%")
                      ->orWhere('contact_person', 'like', "%{$this->search}%")
                      ->orWhere('address', 'like', "%{$this->search}%")
                      ->orWhere('contact_number', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.vendors', [
            'vendors' => $vendors,
        ]);
    }
}
