<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-gray-900">Accession Trend</h2>
            <p class="text-xs text-gray-500">Total physical/digital holdings added over time</p>
        </div>

        <div class="relative flex items-center">
            <div wire:loading wire:target="timeframe" class="absolute -left-6">
                <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <select
                wire:model.live="timeframe"
                class="text-xs border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-1.5 px-3 bg-white text-gray-700 cursor-pointer"
            >
                <option value="7_days">Last 7 Days</option>
                <option value="30_days">Last 30 Days</option>
                <option value="6_months">Last 6 Months</option>
                <option value="12_months">Last 12 Months</option>
            </select>
        </div>
    </div>

    <div
        x-data="accessionTrendChart(@js($chartData))"
        wire:ignore
    >
        <div x-ref="lineChartCanvas"></div>
    </div>
</div>

<script>
window.accessionTrendChart = function(initialData) {
    return {
        chart: null,
        init() {
            this.$nextTick(() => {
                const container = this.$refs.lineChartCanvas;
                if (!container || typeof ApexCharts === 'undefined') return;

                if (this.chart) this.chart.destroy();

                const options = {
                    chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                    stroke: { curve: 'smooth', width: 3 },
                    colors: ['#2563EB'],
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] }
                    },
                    dataLabels: { enabled: false },
                    series: [{ name: 'Accessions', data: initialData?.series || [] }],
                    xaxis: {
                        categories: initialData?.categories || [],
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: { style: { colors: '#6B7280', fontSize: '11px' } }
                    },
                    yaxis: {
                        forceNiceScale: true,
                        decimalsInFloat: 0,
                        labels: { style: { colors: '#6B7280', fontSize: '11px' }, formatter: (val) => Math.floor(val) }
                    },
                    grid: { borderColor: '#F3F4F6', strokeDashArray: 4 },
                    tooltip: { theme: 'light', y: { formatter: (val) => (val || 0).toLocaleString() + ' Records' } }
                };

                this.chart = new ApexCharts(container, options);
                this.chart.render();
            });
        }
    };
};
</script>
