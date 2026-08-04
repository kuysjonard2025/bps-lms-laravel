<?php

namespace App\Livewire\Dashboard;

use App\Models\Accession;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AccessionChart extends Component
{
    #[Computed]
    public function chartData(): array
    {
        $data = Accession::query()
            ->join('catalogs', 'accessions.catalog_id', '=', 'catalogs.id')
            ->join('asset_types', 'catalogs.asset_type_id', '=', 'asset_types.id')
            ->select(
                'asset_types.name as category_name',
                DB::raw('COUNT(accessions.id) as total')
            )
            ->groupBy('asset_types.id', 'asset_types.name')
            ->orderByDesc('total')
            ->get();

        if ($data->isEmpty()) {
            return [
                'categories' => [],
                'series'     => [],
            ];
        }

        return [
            'categories' => $data->pluck('category_name')->toArray(),
            'series'     => $data->pluck('total')->map(fn ($val) => (int) $val)->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.accession-chart', [
            'chartData' => $this->chartData,
        ]);
    }
}
