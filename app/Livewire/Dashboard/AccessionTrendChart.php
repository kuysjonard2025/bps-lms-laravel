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

    public function updatedTimeframe(): void
    {
        $this->dispatch('chart-updated', $this->chartData);
    }

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

    /**
     * Build daily trend count for N days leading up to today.
     */
    private function getDailyTrend(int $days): array
    {
        $startDate = Carbon::today()->subDays($days - 1);
        $endDate   = Carbon::today()->endOfDay();

        // Query total counts grouped by date
        $rawResults = Accession::query()
            ->whereBetween('acquired_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select(DB::raw('DATE(acquired_date) as date'), DB::raw('COUNT(id) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        $categories = [];
        $series = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateKey = $date->toDateString();

            $categories[] = $date->format('M d'); // e.g., "Jul 26"
            $series[] = (int) ($rawResults[$dateKey] ?? 0);
        }

        return [
            'categories' => $categories,
            'series'     => $series,
        ];
    }

    /**
     * Build monthly trend count for N months leading up to current month.
     */
    private function getMonthlyTrend(int $months): array
    {
        $startDate = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $endDate   = Carbon::now()->endOfMonth();

        // Fetch counts within date range
        $rawResults = Accession::query()
            ->whereBetween('acquired_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select(
                DB::raw('YEAR(acquired_date) as year'),
                DB::raw('MONTH(acquired_date) as month'),
                DB::raw('COUNT(id) as total')
            )
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn ($item) => sprintf('%04d-%02d', $item->year, $item->month));

        $categories = [];
        $series = [];

        for ($i = 0; $i < $months; $i++) {
            $monthDate = $startDate->copy()->addMonths($i);
            $monthKey  = $monthDate->format('Y-m');

            $categories[] = $monthDate->format('M Y'); // e.g., "Feb 2026"
            $series[]     = isset($rawResults[$monthKey]) ? (int) $rawResults[$monthKey]->total : 0;
        }

        return [
            'categories' => $categories,
            'series'     => $series,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.accession-trend-chart');
    }
}
