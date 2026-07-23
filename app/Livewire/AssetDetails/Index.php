<?php

namespace App\Livewire\AssetDetails;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Index extends Component
{
    public string $currentTab = 'authors';

    public array $tabs = [
        'authors'            => 'Authors',
        'publishers'         => 'Publishers',
        'general-references' => 'General References',
        'asset-types'        => 'Asset Types',
    ];

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs)) {
            $this->currentTab = $tab;
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Asset Details')]
    public function render()
    {
        return view('livewire.asset-details.index');
    }
}
