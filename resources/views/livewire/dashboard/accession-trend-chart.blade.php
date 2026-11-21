{{-- resources/views/livewire/dashboard/accession-trend-chart.blade.php --}}
<div class="bg-surface-card rounded-2xl border border-surface-border shadow-xs p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-content-heading">Accession Trend</h2>
            <p class="text-xs text-content-body">Holdings added over the selected timeframe</p>
        </div>

        <div
            x-id="['trend-timeframe-combobox']"
            x-data="{
                open: false,
                query: '',
                options: [
                    { value: '7_days', label: 'Last 7 Days' },
                    { value: '30_days', label: 'Last 30 Days' },
                    { value: '6_months', label: 'Last 6 Months' },
                    { value: '12_months', label: 'Last 12 Months' },
                ],
                get filteredOptions() {
                    if (!this.query.trim()) return this.options;
                    const q = this.query.toLowerCase();
                    return this.options.filter(o => o.label.toLowerCase().includes(q));
                },
                selectedLabel() {
                    const match = this.options.find(o => o.value === $wire.timeframe);
                    return match ? match.label : 'Last 6 Months';
                },
                selectOption(option) {
                    $wire.set('timeframe', option.value);
                    this.query = '';
                    this.open = false;
                }
            }"
            @click.outside="open = false"
            @keydown.escape.prevent="open = false"
            class="relative flex items-center gap-2"
        >
            <div wire:loading wire:target="timeframe" class="flex items-center">
                <span class="w-3.5 h-3.5 rounded-full border-2 border-brand-primary border-t-transparent animate-spin" aria-hidden="true"></span>
            </div>

            <div class="relative w-44">
                <label :for="$id('trend-timeframe-combobox')" class="sr-only">Filter accession trend timeframe</label>
                <input
                    :id="$id('trend-timeframe-combobox')"
                    type="text"
                    role="combobox"
                    aria-autocomplete="list"
                    :aria-expanded="open.toString()"
                    :aria-controls="$id('trend-timeframe-combobox') + '-listbox'"
                    :placeholder="selectedLabel()"
                    x-model="query"
                    @focus="open = true"
                    @click="open = true"
                    class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-heading placeholder:font-medium border border-surface-border focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none py-1.5 pl-3 pr-7 cursor-pointer transition"
                />
                <span class="pointer-events-none absolute right-2.5 top-2 text-[10px] text-content-muted" aria-hidden="true">▾</span>

                <ul
                    :id="$id('trend-timeframe-combobox') + '-listbox'"
                    role="listbox"
                    x-show="open"
                    x-transition.opacity
                    style="display: none;"
                    class="absolute right-0 mt-1.5 w-44 rounded-xl bg-surface-card border border-surface-border shadow-lg py-1 z-30"
                >
                    <template x-for="opt in filteredOptions" :key="opt.value">
                        <li role="option" :aria-selected="($wire.timeframe === opt.value).toString()">
                            <button
                                type="button"
                                @click="selectOption(opt)"
                                :class="$wire.timeframe === opt.value
                                    ? 'bg-brand-tint-light text-brand-primary font-semibold'
                                    : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                                class="w-full text-left px-3 py-1.5 text-xs flex items-center justify-between cursor-pointer transition-colors"
                            >
                                <span x-text="opt.label"></span>
                                <span x-show="$wire.timeframe === opt.value" class="text-[11px] font-bold" aria-hidden="true">✓</span>
                            </button>
                        </li>
                    </template>
                    <li x-show="filteredOptions.length === 0" class="px-3 py-2 text-xs text-content-muted">
                        No matching timeframe
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div
        x-data="{
            chart: null,
            observer: null,
            latestData: @js($chartData),
            isDark() {
                return document.documentElement.classList.contains('dark');
            },
            buildOptions(data) {
                const dark = this.isDark();
                const labelColor = dark ? '#94A3B8' : '#64748B';
                const gridColor = dark ? '#334155' : '#E1E9F2';
                const categories = Array.isArray(data?.categories) ? data.categories : [];
                const seriesData = Array.isArray(data?.series) ? data.series.map(n => Number(n) || 0) : [];

                return {
                    chart: {
                        type: 'area',
                        height: 280,
                        toolbar: { show: false },
                        fontFamily: 'inherit',
                        background: 'transparent',
                        animations: { enabled: true, speed: 350 },
                    },
                    theme: {
                        mode: dark ? 'dark' : 'light',
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2.5,
                    },
                    markers: {
                        size: seriesData.length <= 12 ? 4 : 0,
                        strokeWidth: 2,
                        hover: { size: 6 },
                    },
                    colors: ['#4D9EFF'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: dark ? 0.25 : 0.32,
                            opacityTo: 0.03,
                            stops: [0, 90, 100],
                        },
                    },
                    dataLabels: { enabled: false },
                    series: [{ name: 'Accessions', data: seriesData }],
                    xaxis: {
                        categories: categories,
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: {
                            style: {
                                colors: labelColor,
                                fontSize: '11px',
                            },
                        },
                    },
                    yaxis: {
                        min: 0,
                        forceNiceScale: true,
                        decimalsInFloat: 0,
                        labels: {
                            style: {
                                colors: labelColor,
                                fontSize: '11px',
                            },
                            formatter: (val) => Math.round(Number(val) || 0).toLocaleString(),
                        },
                    },
                    grid: {
                        borderColor: gridColor,
                        strokeDashArray: 4,
                    },
                    tooltip: {
                        theme: dark ? 'dark' : 'light',
                        y: {
                            formatter: (val) => (Number(val) || 0).toLocaleString() + ' Accessions',
                        },
                    },
                };
            },
            renderChart(data, attempt = 0) {
                const el = this.$refs.trendChartCanvas;
                if (!el) return;

                if (typeof ApexCharts === 'undefined') {
                    if (attempt < 40) {
                        setTimeout(() => this.renderChart(data, attempt + 1), 100);
                    }
                    return;
                }

                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }

                this.latestData = data || { categories: [], series: [] };
                this.chart = new ApexCharts(el, this.buildOptions(this.latestData));
                this.chart.render();
            },
            mount() {
                this.$nextTick(() => {
                    this.renderChart(this.latestData);
                });

                this.observer = new MutationObserver(() => {
                    if (this.chart) {
                        this.chart.updateOptions(this.buildOptions(this.latestData), false, false);
                    }
                });
                this.observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class'],
                });
            }
        }"
        x-init="mount()"
        class="w-full min-h-[280px]"
    >
        <div x-ref="trendChartCanvas" wire:ignore></div>
    </div>
</div>
