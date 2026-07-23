<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Computed;
use Livewire\Component;

class AccessionChart extends Component
{
    #[Computed]
    public function chartData(): array
    {
        // Mock data representing accession counts per asset format
        return [
            'categories' => ['Books', 'Journals', 'Magazines', 'Theses & Dissertations', 'News Paper'],
            'series'     => [680, 240, 180, 105, 40],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.accession-chart');
    }
}
