<?php // app/Livewire/Registrations.php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Registrations')]
class Registrations extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'users';

    public function updatedActiveTab(): void
    {
        if (! in_array($this->activeTab, ['users', 'patrons'], true)) {
            $this->activeTab = 'users';
        }
    }

    public function render()
    {
        return view('livewire.registrations');
    }
}
