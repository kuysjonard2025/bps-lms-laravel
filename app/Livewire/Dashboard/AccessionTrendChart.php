<?php

namespace App\Livewire\Dashboard;

use App\Models\Accession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Throwable;

class AccessionTrendChart extends Component
{
    public string $timeframe = '6_months';

    public function placeholder(): string
    {
        return <<<'BLADE'
        <div class="bg-surface-card rounded-2xl border border-surface-border shadow-xs p-5 space-y-4 animate-pulse">
            <div class="flex items-center justify-between gap-3">
                <div class="space-y-2">
                    <div class="h-4 w-36 rounded bg-surface-border"></div>
                    <div class="h-3 w-52 rounded bg-surface-border/70"></div>
                </div>
                <div class="h-8 w-36 rounded-xl bg-surface-border"></div>
            </div>
            <div class="h-[280px] w-full rounded-xl bg-surface-canvas"></div>
        </div>
        BLADE;
    }

    public function chartData(): array
    {
        try {
            return match ($this->timeframe) {
                '7_days'    => $this->getDailyTrend(7),
                '30_days'   => $this->getDailyTrend(30),
                '12_months' => $this->getMonthlyTrend(12),
                default     => $this->getMonthlyTrend(6),
            };
        } catch (Throwable $e) {
            Log::error('AccessionTrendChart query failed: ' . $e->getMessage(), [
                'timeframe' => $this->timeframe,
                'exception' => $e,
            ]);

            $this->dispatch(
                'toast',
                type: 'error',
                message: 'Unable to load accession trend data.'
            );

            return [
                'categories' => [],
                'series'     => [],
            ];
        }
    }

    private function getDailyTrend(int $days): array
    {
        $driver = config('database.default');
        $dateExpression = $driver === 'pgsql' ? 'created_at::date' : 'DATE(created_at)';

        $startDate = Carbon::today()->subDays($days - 1);
        $endDate   = Carbon::today()->endOfDay();

        $rawResults = Accession::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw("{$dateExpression} as date"), DB::raw('COUNT(id) as total'))
            ->groupBy(DB::raw($dateExpression))
            ->pluck('total', 'date');

        $categories = [];
        $series = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateKey = $date->toDateString();

            $categories[] = $date->format('M d');
            $series[]     = (int) ($rawResults[$dateKey] ?? 0);
        }

        return [
            'categories' => $categories,
            'series'     => $series,
        ];
    }

    private function getMonthlyTrend(int $months): array
    {
        $driver = config('database.default');
        $yearExpression  = $driver === 'pgsql' ? 'EXTRACT(YEAR FROM created_at)' : 'YEAR(created_at)';
        $monthExpression = $driver === 'pgsql' ? 'EXTRACT(MONTH FROM created_at)' : 'MONTH(created_at)';

        $startDate = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $endDate   = Carbon::now()->endOfMonth();

        $rawResults = Accession::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("{$yearExpression} as year"),
                DB::raw("{$monthExpression} as month"),
                DB::raw('COUNT(id) as total')
            )
            ->groupBy(
                DB::raw($yearExpression),
                DB::raw($monthExpression)
            )
            ->get()
            ->keyBy(fn ($item) => sprintf('%04d-%02d', (int) $item->year, (int) $item->month));

        $categories = [];
        $series = [];

        for ($i = 0; $i < $months; $i++) {
            $monthDate = $startDate->copy()->addMonths($i);
            $monthKey  = $monthDate->format('Y-m');

            $categories[] = $monthDate->format('M Y');
            $series[]     = isset($rawResults[$monthKey]) ? (int) $rawResults[$monthKey]->total : 0;
        }

        return [
            'categories' => $categories,
            'series'     => $series,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.accession-trend-chart', [
            'chartData' => $this->chartData(),
        ]);
    }
}
