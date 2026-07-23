<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Computed;
use Livewire\Component;

class AccessionTrendChart extends Component
{
    public string $timeframe = '6_months';

    public function updatedTimeframe()
    {
        $this->dispatch('chart-updated', $this->chartData);
    }

    #[Computed]
    public function chartData(): array
    {
        [$timeline, $accessionCounts] = match ($this->timeframe) {
            '7_days' => [
                ['Jul 15', 'Jul 16', 'Jul 17', 'Jul 18', 'Jul 19', 'Jul 20', 'Jul 21'],
                [12, 18, 15, 22, 30, 25, 40]
            ],
            '30_days' => [
                ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                [110, 145, 190, 230]
            ],
            '12_months' => [
                ['Aug 2025', 'Sep 2025', 'Oct 2025', 'Nov 2025', 'Dec 2025', 'Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026', 'May 2026', 'Jun 2026', 'Jul 2026'],
                [210, 340, 420, 510, 600, 720, 850, 930, 1020, 1110, 1180, 1245]
            ],
            default => [ // 6_months
                ['Feb 2026', 'Mar 2026', 'Apr 2026', 'May 2026', 'Jun 2026', 'Jul 2026'],
                [650, 780, 890, 990, 1120, 1245]
            ]
        };

        // We map our clear library variables ($timeline, $accessionCounts)
        // to the key names ApexCharts expects ('categories', 'series')
        return [
            'categories' => $timeline,
            'series'     => $accessionCounts,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.accession-trend-chart');
    }
}
