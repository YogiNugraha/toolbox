<div>
    @section('title', 'Admin Analytics & Overview - ' . config('app.name'))
    @section('page_title', 'Admin Overview')
    @section('page_breadcrumb', 'Admin Dashboard')

    {{-- Top Header & Period Filtering Toolbar --}}
    <div class="flex flex-col justify-between gap-4 py-4 sm:flex-row sm:items-center sm:py-5">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl flex items-center gap-2.5">
                <span>Dashboard Analitik Platform</span>
                <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-2.5 py-0.5">
                    Live Analytics
                </span>
            </h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                Pantau tren omset, performa penggunaan seluruh tools, dan konversi pelanggan secara real-time.
            </p>
        </div>

        {{-- Filter Periode (Segmented Pill Buttons) --}}
        <div class="flex items-center space-x-2">
            <div class="inline-flex rounded-full bg-slate-100 p-1 dark:bg-navy-700 shadow-inner">
                <button 
                    wire:click="setPeriod('7_days')"
                    class="btn h-7.5 rounded-full px-3 text-xs font-semibold transition-all {{ $period === '7_days' ? 'bg-white text-primary shadow-xs dark:bg-navy-600 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-white' }}"
                >
                    7 Hari
                </button>
                <button 
                    wire:click="setPeriod('30_days')"
                    class="btn h-7.5 rounded-full px-3 text-xs font-semibold transition-all {{ $period === '30_days' ? 'bg-white text-primary shadow-xs dark:bg-navy-600 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-white' }}"
                >
                    30 Hari
                </button>
                <button 
                    wire:click="setPeriod('this_month')"
                    class="btn h-7.5 rounded-full px-3 text-xs font-semibold transition-all {{ $period === 'this_month' ? 'bg-white text-primary shadow-xs dark:bg-navy-600 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-white' }}"
                >
                    Bulan Ini
                </button>
                <button 
                    wire:click="setPeriod('6_months')"
                    class="btn h-7.5 rounded-full px-3 text-xs font-semibold transition-all {{ $period === '6_months' ? 'bg-white text-primary shadow-xs dark:bg-navy-600 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-white' }}"
                >
                    6 Bulan
                </button>
                <button 
                    wire:click="setPeriod('12_months')"
                    class="btn h-7.5 rounded-full px-3 text-xs font-semibold transition-all {{ $period === '12_months' ? 'bg-white text-primary shadow-xs dark:bg-navy-600 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-white' }}"
                >
                    1 Tahun
                </button>
            </div>
        </div>
    </div>

    {{-- 4 Stat Metric Cards (Lineone Style with Badges) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-5 mb-6">
        {{-- Total Users --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Pengguna Baru</span>
                <div class="mask is-squircle flex size-10 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                    <x-lucide-users class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-navy-100">
                    {{ number_format($periodUsers) }}
                </p>
                <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-navy-300">
                    <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-[10px] font-bold px-1.5 py-0.5">
                        {{ $periodLabel }}
                    </span>
                    <span>Total {{ number_format($totalAllUsers) }} terdaftar</span>
                </div>
            </div>
        </div>

        {{-- Active Subscribed Users --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Member PRO Aktif</span>
                <div class="mask is-squircle flex size-10 items-center justify-center bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold">
                    <x-lucide-crown class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl sm:text-3xl font-extrabold text-purple-600 dark:text-purple-400">
                    {{ number_format($activeProUsers) }}
                </p>
                <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-navy-300">
                    <span class="badge rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 text-[10px] font-bold px-1.5 py-0.5">
                        +{{ $periodProSubs }} di periode ini
                    </span>
                    <span>{{ $conversionRate }}% conversion</span>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Pendapatan Periode Ini</span>
                <div class="mask is-squircle flex size-10 items-center justify-center bg-success/10 text-success font-bold">
                    <x-lucide-dollar-sign class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-navy-100">
                    <span class="text-xs font-semibold text-slate-400 dark:text-navy-300">Rp</span>
                    {{ number_format($periodRevenue, 0, ',', '.') }}
                </p>
                <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-navy-300">
                    <span class="badge rounded-full bg-success/10 text-success text-[10px] font-bold px-1.5 py-0.5">
                        Total Rp {{ number_format($totalAllRevenue, 0, ',', '.') }}
                    </span>
                    <span>akumulasi</span>
                </div>
            </div>
        </div>

        {{-- Total Files Processed --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">File Diproses</span>
                <div class="mask is-squircle flex size-10 items-center justify-center bg-info/10 text-info font-bold">
                    <x-lucide-sparkles class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-navy-100">
                    {{ number_format($periodFiles) }}
                </p>
                <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-navy-300">
                    <span class="badge rounded-full bg-info/10 text-info text-[10px] font-bold px-1.5 py-0.5">
                        {{ $periodLabel }}
                    </span>
                    <span>Total {{ number_format($totalAllFiles) }} file</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Analytics Charts Section (Lineone CRM Analytics Style) --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 sm:gap-5 mb-6">
        
        {{-- Left: Revenue & Transactions Spline Area Chart (8 Columns) --}}
        <div class="card lg:col-span-8 p-4 sm:p-5">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center pb-4 border-b border-slate-150 dark:border-navy-600">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-navy-100 flex items-center gap-2">
                        <x-lucide-trending-up class="size-4.5 text-primary dark:text-accent-light" />
                        <span>Tren Pendapatan & Volume Transaksi</span>
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                        Statistik pertumbuhan omset dan transaksi pembayaran platform.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-3 py-1">
                        {{ $periodLabel }}
                    </span>
                </div>
            </div>

            {{-- ApexChart Area Container with Smooth Animations --}}
            <div 
                wire:ignore
                class="pt-4 min-h-[330px]"
                x-data="{
                    chart: null,
                    init() {
                        this.$nextTick(() => {
                            let options = {
                                series: [
                                    {
                                        name: 'Pendapatan (Rp)',
                                        type: 'area',
                                        data: {{ json_encode($revenueTrend) }}
                                    },
                                    {
                                        name: 'Transaksi Sukses',
                                        type: 'line',
                                        data: {{ json_encode($transactionsTrend) }}
                                    }
                                ],
                                chart: {
                                    height: 320,
                                    type: 'area',
                                    toolbar: { show: false },
                                    zoom: { enabled: false },
                                    fontFamily: 'inherit',
                                    animations: {
                                        enabled: true,
                                        easing: 'easeinout',
                                        speed: 1000,
                                        animateGradually: {
                                            enabled: true,
                                            delay: 200
                                        },
                                        dynamicAnimation: {
                                            enabled: true,
                                            speed: 500
                                        }
                                    }
                                },
                                colors: ['#4f46e5', '#10b981'],
                                fill: {
                                    type: ['gradient', 'solid'],
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.45,
                                        opacityTo: 0.05,
                                        stops: [20, 100]
                                    }
                                },
                                dataLabels: { enabled: false },
                                stroke: { 
                                    curve: 'smooth', 
                                    width: [2.5, 2.5],
                                    dashArray: [0, 4]
                                },
                                xaxis: {
                                    categories: {{ json_encode($chartCategories) }},
                                    axisBorder: { show: false },
                                    axisTicks: { show: false },
                                    labels: {
                                        style: {
                                            colors: '#94a3b8',
                                            fontSize: '11px',
                                            fontWeight: 500
                                        }
                                    }
                                },
                                yaxis: [
                                    {
                                        title: { 
                                            text: 'Pendapatan (Rp)', 
                                            style: { fontSize: '11px', fontWeight: 600, color: '#4f46e5' } 
                                        },
                                        labels: {
                                            style: { colors: '#94a3b8', fontSize: '11px' },
                                            formatter: (val) => 'Rp ' + Number(val).toLocaleString('id-ID')
                                        }
                                    },
                                    {
                                        opposite: true,
                                        title: { 
                                            text: 'Transaksi', 
                                            style: { fontSize: '11px', fontWeight: 600, color: '#10b981' } 
                                        },
                                        labels: {
                                            style: { colors: '#94a3b8', fontSize: '11px' },
                                            formatter: (val) => Math.round(val)
                                        }
                                    }
                                ],
                                tooltip: {
                                    shared: true,
                                    intersect: false
                                },
                                grid: {
                                    borderColor: '#e2e8f0',
                                    strokeDashArray: 4
                                },
                                legend: {
                                    position: 'top',
                                    horizontalAlign: 'right',
                                    fontSize: '12px',
                                    fontWeight: 600
                                }
                            };
                            if (this.chart) {
                                this.chart.destroy();
                            }
                            this.chart = new ApexCharts(this.$refs.splineChart, options);
                            this.chart.render();
                        });

                        this.$watch('$wire.revenueTrend', () => this.syncChart());
                        this.$watch('$wire.transactionsTrend', () => this.syncChart());
                        this.$watch('$wire.chartCategories', () => this.syncChart());
                    },
                    syncChart() {
                        if (this.chart) {
                            this.chart.updateOptions({
                                xaxis: {
                                    categories: this.$wire.chartCategories
                                },
                                series: [
                                    {
                                        name: 'Pendapatan (Rp)',
                                        type: 'area',
                                        data: this.$wire.revenueTrend
                                    },
                                    {
                                        name: 'Transaksi Sukses',
                                        type: 'line',
                                        data: this.$wire.transactionsTrend
                                    }
                                ]
                            }, true, true);
                        }
                    }
                }"
            >
                <div x-ref="splineChart"></div>
            </div>
        </div>

        {{-- Right: Tool Usage Breakdown Donut Chart (4 Columns) --}}
        <div class="card lg:col-span-4 p-4 sm:p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-150 dark:border-navy-600">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-navy-100 flex items-center gap-2">
                            <x-lucide-pie-chart class="size-4.5 text-secondary" />
                            <span>Distribusi Penggunaan</span>
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                            Proporsi pemrosesan file antar tool.
                        </p>
                    </div>
                </div>

                {{-- Donut Chart with Smooth Animations --}}
                <div 
                    wire:ignore
                    class="pt-3 min-h-[220px]"
                    x-data="{
                        donutChart: null,
                        init() {
                            this.$nextTick(() => {
                                let donutOptions = {
                                    series: {{ json_encode($donutSeries) }},
                                    labels: {{ json_encode($donutLabels) }},
                                    chart: {
                                        type: 'donut',
                                        height: 220,
                                        fontFamily: 'inherit',
                                        animations: {
                                            enabled: true,
                                            easing: 'easeinout',
                                            speed: 1000,
                                            dynamicAnimation: {
                                                enabled: true,
                                                speed: 500
                                            }
                                        }
                                    },
                                    colors: ['#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#06b6d4', '#8b5cf6'],
                                    dataLabels: { enabled: false },
                                    legend: { show: false },
                                    plotOptions: {
                                        pie: {
                                            donut: {
                                                size: '72%',
                                                labels: {
                                                    show: true,
                                                    total: {
                                                        show: true,
                                                        formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID')
                                                    }
                                                }
                                            }
                                        }
                                    },
                                    stroke: { width: 0 }
                                };
                                if (this.donutChart) {
                                    this.donutChart.destroy();
                                }
                                this.donutChart = new ApexCharts(this.$refs.donutChart, donutOptions);
                                this.donutChart.render();
                            });

                            this.$watch('$wire.donutSeries', (newVal) => {
                                if (this.donutChart) {
                                    this.donutChart.updateSeries(newVal, true);
                                }
                            });
                        }
                    }"
                >
                    <div x-ref="donutChart"></div>
                </div>

                {{-- Breakdown List with Progress Bars --}}
                <div class="mt-4 space-y-2.5 max-h-48 overflow-y-auto is-scrollbar-hidden">
                    @foreach($toolDetails as $tool)
                        <div class="rounded-lg border border-slate-150 dark:border-navy-600 p-2.5 bg-slate-50/50 dark:bg-navy-800/40">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="font-bold text-slate-700 dark:text-navy-100 flex items-center gap-1.5">
                                    <span>{{ $tool['name'] }}</span>
                                    @if($tool['is_pro_only'])
                                        <span class="badge rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 text-[9px] font-bold px-1.5 py-0.2 uppercase">PRO</span>
                                    @endif
                                </span>
                                <span class="font-semibold text-slate-500 dark:text-navy-300">
                                    {{ number_format($tool['count']) }} kali ({{ $tool['percent'] }}%)
                                </span>
                            </div>
                            <div class="progress h-1.5 rounded-full bg-slate-200 dark:bg-navy-600">
                                <div class="rounded-full bg-primary dark:bg-accent" style="width: {{ max(4, $tool['percent']) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Grid: Recent Subscriptions & Platform Health --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 sm:gap-5">
        
        {{-- Left: Recent Transactions Table (8 Columns) --}}
        <div class="card lg:col-span-8 p-4 sm:p-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-150 dark:border-navy-600">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-navy-100 flex items-center gap-2">
                        <x-lucide-receipt class="size-4.5 text-success" />
                        <span>Transaksi & Pembayaran Langganan Terkini</span>
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                        Daftar transaksi pembayaran pengguna yang masuk ke platform.
                    </p>
                </div>
                <a href="{{ route('admin.transactions') }}" wire:navigate class="text-xs font-bold text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <x-lucide-chevron-right class="size-3.5" />
                </a>
            </div>

            <div class="is-scrollbar-hidden min-w-full overflow-x-auto pt-2">
                <table class="is-hoverable w-full text-left">
                    <thead>
                        <tr>
                            <th class="rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 text-xs">
                                PENGGUNA
                            </th>
                            <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 text-xs">
                                PAKET
                            </th>
                            <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 text-xs text-right">
                                NOMINAL
                            </th>
                            <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 text-xs text-center">
                                STATUS
                            </th>
                            <th class="rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 text-xs text-right">
                                TANGGAL
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-150 dark:divide-navy-500 text-xs">
                        @forelse($recentTransactions as $tx)
                            @php
                                $planName = $tx->plan->name ?? ucfirst($tx->plan_slug ?? 'PRO');
                                $isProMax = ($tx->plan_slug === 'pro-max' || strtolower((string)$planName) === 'pro max');
                            @endphp
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 hover:bg-slate-50/80 dark:hover:bg-navy-700/50 transition-colors">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="avatar size-8 shrink-0">
                                            @if($tx->user && $tx->user->profile_photo_path)
                                                <img class="rounded-full object-cover" src="{{ Storage::url($tx->user->profile_photo_path) }}" alt="{{ $tx->user->name }}" />
                                            @else
                                                <div class="is-initial rounded-full bg-primary/10 text-xs font-bold uppercase text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                                    {{ substr($tx->user->name ?? 'U', 0, 2) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-700 dark:text-navy-100 text-xs">
                                                {{ $tx->user->name ?? 'Pengguna Terhapus' }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">
                                                {{ $tx->user->email ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs">
                                    @if($isProMax)
                                        <span class="badge rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 text-white font-black text-[9px] px-2 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                            <x-lucide-crown class="size-2.5 stroke-[2.5]" />
                                            <span>{{ $planName }}</span>
                                        </span>
                                    @else
                                        <span class="badge rounded-full bg-linear-to-r from-amber-500 to-orange-500 text-white font-black text-[9px] px-2 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                            <x-lucide-star class="size-2.5 stroke-[2.5] fill-current" />
                                            <span>{{ $planName }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-slate-700 dark:text-navy-100">
                                    Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    @if($tx->status === 'active' || $tx->status === 'settlement' || $tx->status === 'capture' || $tx->status === 'paid')
                                        <span class="badge space-x-1.5 rounded-full bg-success/10 text-success text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                            <span class="size-1.5 rounded-full bg-current"></span>
                                            <span>Berhasil</span>
                                        </span>
                                    @elseif($tx->status === 'pending')
                                        <span class="badge space-x-1.5 rounded-full bg-warning/10 text-warning text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                            <span class="size-1.5 rounded-full bg-current animate-ping"></span>
                                            <span>Menunggu</span>
                                        </span>
                                    @elseif($tx->status === 'expired')
                                        <span class="badge space-x-1.5 rounded-full bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                            <span class="size-1.5 rounded-full bg-current"></span>
                                            <span>Expired</span>
                                        </span>
                                    @elseif($tx->status === 'cancelled')
                                        <span class="badge space-x-1.5 rounded-full bg-info/10 text-info dark:bg-info/15 text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                            <span class="size-1.5 rounded-full bg-current"></span>
                                            <span>Dibatalkan</span>
                                        </span>
                                    @else
                                        <span class="badge space-x-1.5 rounded-full bg-error/10 text-error text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                            <span class="size-1.5 rounded-full bg-current"></span>
                                            <span>{{ ucfirst($tx->status) }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-[11px] text-slate-400 dark:text-navy-300">
                                    {{ $tx->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 dark:text-navy-300 text-xs">
                                    Belum ada transaksi pembayaran yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right: Platform Health & Plans Breakdown (4 Columns) --}}
        <div class="card lg:col-span-4 p-4 sm:p-5 space-y-5">
            
            {{-- Platform Tools Operational Status --}}
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-navy-100 flex items-center gap-2 pb-3 border-b border-slate-150 dark:border-navy-600">
                    <x-lucide-activity class="size-4.5 text-warning" />
                    <span>Status Operasional Tools</span>
                </h3>

                <div class="mt-3.5 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between rounded-lg border border-slate-150 dark:border-navy-600 p-2.5 bg-slate-50/50 dark:bg-navy-800/40">
                        <span class="text-slate-600 dark:text-navy-200 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-success"></span>
                            <span>Tools Aktif & Normal</span>
                        </span>
                        <span class="font-extrabold text-success">{{ $activeToolsCount }}</span>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-slate-150 dark:border-navy-600 p-2.5 bg-slate-50/50 dark:bg-navy-800/40">
                        <span class="text-slate-600 dark:text-navy-200 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-purple-500"></span>
                            <span>Khusus Member PRO (Gated)</span>
                        </span>
                        <span class="font-extrabold text-purple-600 dark:text-purple-400">{{ $proOnlyToolsCount }}</span>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-slate-150 dark:border-navy-600 p-2.5 bg-slate-50/50 dark:bg-navy-800/40">
                        <span class="text-slate-600 dark:text-navy-200 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-warning"></span>
                            <span>Mode Maintenance</span>
                        </span>
                        <span class="font-extrabold text-warning">{{ $maintenanceToolsCount }}</span>
                    </div>
                </div>
            </div>

            {{-- Plans Distribution --}}
            <div class="pt-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300 mb-3">
                    Distribusi Paket Langganan
                </h4>
                <div class="space-y-2">
                    @foreach($plans as $plan)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 flex items-center gap-1.5">
                                @if($plan->price == 0)
                                    <span>{{ $plan->name }} (Gratis)</span>
                                @else
                                    <x-lucide-crown class="size-3 text-amber-500" />
                                    <span>{{ $plan->name }}</span>
                                @endif
                            </span>
                            <span class="font-bold text-slate-600 dark:text-navy-200">
                                {{ $plan->subscriptions_count }} pelanggan
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>
