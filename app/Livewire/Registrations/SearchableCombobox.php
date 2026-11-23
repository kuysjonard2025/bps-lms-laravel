<?php // app/Livewire/Registrations/SearchableCombobox.php

namespace App\Livewire\Registrations;

use Livewire\Attributes\Modelable;
use Livewire\Component;

class SearchableCombobox extends Component
{
    #[Modelable]
    public string|int|null $value = null;

    /** @var array<int, array{id: string|int, label: string}> */
    public array $options = [];

    public string $label = '';
    public string $placeholder = 'Select an option';
    public string $searchPlaceholder = 'Type to search...';
    public string $emptyText = 'No matching records found.';
    public bool $required = false;
    public bool $disabled = false;
    public bool $uppercase = false;
    public string $errorKey = '';

    public function render()
    {
        return view('livewire.registrations.searchable-combobox');
    }
}
