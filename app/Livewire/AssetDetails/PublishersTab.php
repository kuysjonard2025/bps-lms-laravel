<?php

namespace App\Livewire\AssetDetails;

use App\Models\Publisher;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PublishersTab extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $publisherIdBeingEdited = null;
    public ?int $publisherIdBeingDeleted = null;

    public string $name = '';
    public string $address = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('publishers', 'name')
                    ->where('address', trim($this->address))
                    ->ignore($this->publisherIdBeingEdited),
            ],
            'address' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique' => 'A publisher with this exact name and address combination already exists.',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'address', 'publisherIdBeingEdited']);
        $this->showModal = true;
    }

    public function openEditModal(Publisher $publisher): void
    {
        $this->resetValidation();
        $this->publisherIdBeingEdited = $publisher->id;
        $this->name = $publisher->name;
        $this->address = $publisher->address;
        $this->showModal = true;
    }

    public function savePublisher(): void
    {
        $this->validate();

        Publisher::updateOrCreate(
            ['id' => $this->publisherIdBeingEdited],
            [
                'name' => trim($this->name),
                'address' => trim($this->address),
            ]
        );

        $message = $this->publisherIdBeingEdited ? 'Publisher updated successfully.' : 'Publisher created successfully.';

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->publisherIdBeingDeleted = $id;
        $this->showDeleteModal = true;
    }

    public function deletePublisher(): void
    {
        if ($this->publisherIdBeingDeleted) {
            Publisher::destroy($this->publisherIdBeingDeleted);
            $this->dispatch('toast', message: 'Publisher deleted successfully.', type: 'success');
        }

        $this->showDeleteModal = false;
        $this->publisherIdBeingDeleted = null;
    }

    public function render()
    {
        $publishers = Publisher::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('address', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.asset-details.publishers-tab', [
            'publishers' => $publishers,
        ]);
    }
}
