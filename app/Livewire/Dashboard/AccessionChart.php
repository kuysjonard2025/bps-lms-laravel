<?php

namespace App\Livewire\Dashboard;

use App\Models\Accession;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

class AccessionChart extends Component
{
    public function placeholder(): string
    {
        return <<<'BLADE'
        <div class="bg-surface-card rounded-2xl border border-surface-border shadow-xs p-5 space-y-4 animate-pulse">
            <div class="space-y-2">
                <div class="h-4 w-44 rounded bg-surface-border"></div>
                <div class="h-3 w-60 rounded bg-surface-border/70"></div>
            </div>
            <div class="h-[280px] w-full rounded-xl bg-surface-canvas"></div>
        </div>
        BLADE;
    }

    #[Computed]
    public function chartData(): array
    {
        try {
            $accessions = Accession::query()
                ->with(['catalog.assetType'])
                ->get();

            if ($accessions->isEmpty()) {
                return [
                    'categories' => [],
                    'series'     => [],
                ];
            }

            $grouped = $accessions
                ->groupBy(function (Accession $accession): string {
                    $assetType = $accession->catalog?->assetType;
                    $label = $assetType?->name
                        ?? $assetType?->title
                        ?? $accession->catalog?->asset_type
                        ?? 'Uncategorized';

                    $cleaned = trim((string) $label);

                    return $cleaned !== '' ? $cleaned : 'Uncategorized';
                })
                ->map(fn ($items) => $items->count())
                ->sortDesc();

            return [
                'categories' => $grouped->keys()->values()->toArray(),
                'series'     => $grouped->values()->map(fn ($val) => (int) $val)->toArray(),
            ];
        } catch (Throwable $e) {
            Log::error('AccessionChart query failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $this->dispatch(
                'toast',
                type: 'error',
                message: 'Unable to load accessions by asset type.'
            );

            return [
                'categories' => [],
                'series'     => [],
            ];
        }
    }

    public function render()
    {
        return view('livewire.dashboard.accession-chart', [
            'chartData' => $this->chartData,
        ]);
    }
}
