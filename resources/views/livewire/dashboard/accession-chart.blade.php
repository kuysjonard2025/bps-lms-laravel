<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-gray-900">Accessions by Asset Type</h2>
            <p class="text-xs text-gray-500">Distribution of holdings across formats</p>
        </div>
    </div>

    <div
        x-data="accessionBarChart(@js($chartData))"
        wire:ignore
    >
        <div x-ref="barChartCanvas"></div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('accessionBarChart', (initialData) => ({
        chart: null,

        init() {
            this.$nextTick(() => {
                const container = this.$refs.barChartCanvas;
                if (!container) return;

                if (typeof ApexCharts === 'undefined') {
                    console.error('ApexCharts library is not loaded on this page.');
                    return;
                }

                if (this.chart) {
                    this.chart.destroy();
                }

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
                            barHeight: '55%',
                            distributed: true,
                        }
                    },
                    colors: ['#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#A5B4FC', '#C7D2FE'],
                    dataLabels: {
                        enabled: true,
                        textAnchor: 'start',
                        style: { fontSize: '11px', fontWeight: '600' },
                        formatter: (val) => val ? val.toLocaleString() : '0',
                        dropShadow: { enabled: false }
                    },
                    legend: { show: false },
                    series: [{
                        name: 'Total Items',
                        data: initialData?.series || []
                    }],
                    xaxis: {
                        categories: initialData?.categories || [],
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

                this.chart = new ApexCharts(container, options);
                this.chart.render();
            });
        },

        updateChart(newData) {
            if (this.chart && newData) {
                this.chart.updateOptions({
                    series: [{ name: 'Total Items', data: newData.series || [] }],
                    xaxis: { categories: newData.categories || [] }
                });
            }
        }
    }));
});
</script>
