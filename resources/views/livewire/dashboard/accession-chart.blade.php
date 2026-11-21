{{-- resources/views/livewire/dashboard/accession-chart.blade.php --}}
<div class="bg-surface-card rounded-2xl border border-surface-border shadow-xs p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-content-heading">Accessions by Asset Type</h2>
            <p class="text-xs text-content-body">Distribution of holdings across formats</p>
        </div>
        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-brand-tint-light text-brand-primary self-start sm:self-auto tabular-nums">
            {{ number_format(array_sum($chartData['series'] ?? [])) }} Total
        </span>
    </div>

    @if (empty($chartData['series']))
        <div class="h-[280px] rounded-xl bg-surface-canvas border border-dashed border-surface-border flex flex-col items-center justify-center text-center px-4">
            <p class="text-xs font-semibold text-content-heading">No accession holdings yet</p>
            <p class="text-[11px] text-content-muted mt-1">Chart data will appear once cataloged accessions are recorded.</p>
        </div>
    @else
        <div
            wire:ignore
            x-data="{
                chart: null,
                observer: null,
                chartPayload: @js($chartData),
                isDark() {
                    return document.documentElement.classList.contains('dark');
                },
                palette() {
                    return this.isDark()
                        ? ['#4D9EFF', '#3A8EFF', '#FF6B52', '#7DB8FF', '#FF8E7A', '#A6CCFF']
                        : ['#4D9EFF', '#3A8EFF', '#FF6B52', '#73B2FF', '#FF8570', '#9EC9FF'];
                },
                buildOptions(data) {
                    const dark = this.isDark();
                    const labelColor = dark ? '#94A3B8' : '#64748B';
                    const headingColor = dark ? '#F1F5F9' : '#1E293B';
                    const gridColor = dark ? '#334155' : '#E1E9F2';

                    return {
                        chart: {
                            type: 'bar',
                            height: 280,
                            toolbar: { show: false },
                            fontFamily: 'inherit',
                            background: 'transparent',
                        },
                        theme: {
                            mode: dark ? 'dark' : 'light',
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 6,
                                horizontal: true,
                                barHeight: '55%',
                                distributed: true,
                            },
                        },
                        colors: this.palette(),
                        dataLabels: {
                            enabled: true,
                            textAnchor: 'start',
                            style: {
                                fontSize: '11px',
                                fontWeight: '600',
                                colors: [headingColor],
                            },
                            formatter: (val) => (val ? Number(val).toLocaleString() : '0'),
                            dropShadow: { enabled: false },
                        },
                        legend: { show: false },
                        series: [{ name: 'Total Items', data: data?.series || [] }],
                        xaxis: {
                            categories: data?.categories || [],
                            labels: {
                                style: {
                                    colors: labelColor,
                                    fontSize: '11px',
                                },
                            },
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: headingColor,
                                    fontSize: '11px',
                                    fontWeight: 600,
                                },
                            },
                        },
                        grid: {
                            borderColor: gridColor,
                            strokeDashArray: 4,
                        },
                        tooltip: {
                            theme: dark ? 'dark' : 'light',
                            y: {
                                formatter: (val) => (val || 0).toLocaleString() + ' Items',
                            },
                        },
                    };
                },
                init() {
                    this.renderWhenReady(this.chartPayload);

                    this.observer = new MutationObserver(() => {
                        if (this.chart) {
                            this.chart.updateOptions(this.buildOptions(this.chartPayload));
                        }
                    });
                    this.observer.observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class'],
                    });
                },
                renderWhenReady(data, attempts = 0) {
                    const container = this.$refs.barChartCanvas;
                    if (!container) return;

                    if (typeof ApexCharts === 'undefined') {
                        if (attempts < 30) {
                            setTimeout(() => this.renderWhenReady(data, attempts + 1), 100);
                        }
                        return;
                    }

                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }

                    this.chart = new ApexCharts(container, this.buildOptions(data));
                    this.chart.render();
                },
                updateChart(newData) {
                    this.chartPayload = newData || { categories: [], series: [] };
                    if (!this.chart) {
                        this.renderWhenReady(this.chartPayload);
                        return;
                    }

                    const opts = this.buildOptions(this.chartPayload);
                    this.chart.updateOptions({
                        ...opts,
                        xaxis: {
                            ...opts.xaxis,
                            categories: this.chartPayload.categories || [],
                        },
                        series: [{ name: 'Total Items', data: this.chartPayload.series || [] }],
                    });
                },
            }"
            x-effect="updateChart(@js($chartData))"
        >
            <div x-ref="barChartCanvas"></div>
        </div>
    @endif
</div>
