<?php

namespace App\Livewire\Dashboard;

use App\Models\Accession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AccessionTrendChart extends Component
{
    public string $timeframe = '6_months';

    #[Computed]
    public function chartData(): array
    {
        return match ($this->timeframe) {
            '7_days'    => $this->getDailyTrend(7),
            '30_days'   => $this->getDailyTrend(30),
            '12_months' => $this->getMonthlyTrend(12),
            default     => $this->getMonthlyTrend(6),
        };
    }

    private function getDailyTrend(int $days): array
    {
        $startDate = Carbon::today()->subDays($days - 1);
        $endDate   = Carbon::today()->endOfDay();

        $rawResults = Accession::query()
            ->whereBetween('acquired_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select(DB::raw('acquired_date::date as date'), DB::raw('COUNT(id) as total'))
            ->groupBy(DB::raw('acquired_date::date'))
            ->pluck('total', 'date');

        $categories = [];
        $series = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateKey = $date->toDateString();

            $categories[] = $date->format('M d');
            $series[] = (int) ($rawResults[$dateKey] ?? 0);
        }

        return [
            'categories' => $categories,
            'series'     => $series,
        ];
    }

    private function getMonthlyTrend(int $months): array
    {
        $startDate = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $endDate   = Carbon::now()->endOfMonth();

        $rawResults = Accession::query()
            ->whereBetween('acquired_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select(
                DB::raw('EXTRACT(YEAR FROM acquired_date) as year'),
                DB::raw('EXTRACT(MONTH FROM acquired_date) as month'),
                DB::raw('COUNT(id) as total')
            )
            ->groupBy(
                DB::raw('EXTRACT(YEAR FROM acquired_date)'),
                DB::raw('EXTRACT(MONTH FROM acquired_date)')
            )
            ->get()
            ->keyBy(fn ($item) => sprintf('%04d-%02d', (int)$item->year, (int)$item->month));

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
            'chartData' => $this->chartData,
        ]);
    }
}
