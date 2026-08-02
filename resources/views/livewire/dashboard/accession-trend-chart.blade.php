<div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5 space-y-4">
    {{-- Header & Timeframe Filter --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-gray-900">Accession Trend</h2>
            <p class="text-xs text-gray-500">Total physical/digital holdings added over time</p>
        </div>

        <div>
            <select
                wire:model.live="timeframe"
                class="text-xs border-gray-300 rounded-lg shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-1.5 px-3 bg-white text-gray-700 cursor-pointer"
            >
                <option value="7_days">Last 7 Days</option>
                <option value="30_days">Last 30 Days</option>
                <option value="6_months">Last 6 Months</option>
                <option value="12_months">Last 12 Months</option>
            </select>
        </div>
    </div>

    {{-- ApexCharts Container --}}
    <div
        x-data="{
            chart: null,
            initChart() {
                const initialData = @js($this->chartData);

                const options = {
                    chart: {
                        type: 'area',
                        height: 280,
                        toolbar: { show: false },
                        fontFamily: 'inherit',
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#2563EB'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.35,
                            opacityTo: 0.05,
                            stops: [0, 90, 100]
                        }
                    },
                    dataLabels: { enabled: false },
                    series: [{
                        name: 'Accessions',
                        data: initialData?.series || []
                    }],
                    xaxis: {
                        categories: initialData?.categories || [],
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: {
                            style: { colors: '#6B7280', fontSize: '11px' }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#6B7280', fontSize: '11px' }
                        }
                    },
                    grid: {
                        borderColor: '#F3F4F6',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: 'light',
                        y: {
                            formatter: (val) => (val || 0).toLocaleString() + ' Records'
                        }
                    }
                };

                this.chart = new ApexCharts(this.$refs.lineChartCanvas, options);
                this.chart.render();

                // Seamless reactive update via $wire watcher (removes need for dispatch events)
                $wire.$watch('chartData', (newData) => {
                    if (this.chart && newData) {
                        this.chart.updateOptions({
                            series: [{
                                name: 'Accessions',
                                data: newData.series || []
                            }],
                            xaxis: {
                                categories: newData.categories || []
                            }
                        });
                    }
                });

                // Memory cleanup
                this.$cleanup(() => {
                    if (this.chart) this.chart.destroy();
                });
            }
        }"
        x-init="initChart()"
        wire:ignore
    >
        <div x-ref="lineChartCanvas"></div>
    </div>
</div>
