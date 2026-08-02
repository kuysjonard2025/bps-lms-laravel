<div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-gray-900">Accessions by Asset Type</h2>
            <p class="text-xs text-gray-500">Distribution of holdings across formats</p>
        </div>
    </div>

    <div
        x-data="{
            chart: null,
            initChart() {
                const chartData = @js($this->chartData);

                const options = {
                    chart: {
                        type: 'bar',
                        height: 280,
                        toolbar: { show: false },
                        fontFamily: 'inherit',
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            horizontal: true,
                            barHeight: '50%',
                            distributed: true,
                        }
                    },
                    colors: ['#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#A5B4FC', '#C7D2FE'],
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px', colors: ['#FFFFFF'] },
                        formatter: (val) => val ? val.toLocaleString() : '0'
                    },
                    legend: { show: false },
                    series: [{
                        name: 'Total Items',
                        data: chartData?.series || []
                    }],
                    xaxis: {
                        categories: chartData?.categories || [],
                        labels: { style: { colors: '#6B7280', fontSize: '11px' } }
                    },
                    yaxis: {
                        labels: { style: { colors: '#374151', fontSize: '11px', fontWeight: 600 } }
                    },
                    grid: { borderColor: '#F3F4F6', strokeDashArray: 4 },
                    tooltip: {
                        theme: 'light',
                        y: { formatter: (val) => (val || 0).toLocaleString() + ' Items' }
                    }
                };

                this.chart = new ApexCharts(this.$refs.barChartCanvas, options);
                this.chart.render();

                // Reactive updates via Livewire $wire watcher
                $wire.$watch('chartData', (newData) => {
                    if (this.chart && newData) {
                        this.chart.updateOptions({
                            series: [{ name: 'Total Items', data: newData.series || [] }],
                            xaxis: { categories: newData.categories || [] }
                        });
                    }
                });
            },
            destroy() {
                if (this.chart) {
                    this.chart.destroy();
                }
            }
        }"
        x-init="initChart()"
        wire:ignore
    >
        <div x-ref="barChartCanvas"></div>
    </div>
</div>
