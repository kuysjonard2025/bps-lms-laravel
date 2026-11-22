<?php

namespace App\Livewire\Imports;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ImportsIndex extends Component
{
    public string $activeTab = 'catalogs'; // catalogs | accessions | acquisitions | patrons

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['catalogs', 'accessions', 'acquisitions', 'patrons'], true)) {
            $this->activeTab = $tab;
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Imports')]
    public function render()
    {
        return view('livewire.imports.imports-index');
    }
}
