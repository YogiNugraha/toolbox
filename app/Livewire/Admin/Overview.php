<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Tool;
use App\Models\Activity;
use App\Models\Plan;

#[Layout('layouts.admin')]
class Overview extends Component
{
    public $period = '6_months'; // 7_days, 30_days, this_month, 6_months, 12_months
    public $chartCategories = [];
    public $revenueTrend = [];
    public $transactionsTrend = [];
    public $donutSeries = [];
    public $donutLabels = [];
    public $periodLabel = '6 Bulan Terakhir';

    public function mount()
    {
        $this->loadChartData();
    }

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->loadChartData();
    }

    public function loadChartData()
    {
        $this->chartCategories = [];
        $this->revenueTrend = [];
        $this->transactionsTrend = [];

        if ($this->period === '7_days') {
            $this->periodLabel = '7 Hari Terakhir';
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $this->chartCategories[] = $date->translatedFormat('d M');

                $rev = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('amount');
                $this->revenueTrend[] = (int) $rev;

                $txCount = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereDate('created_at', $date->toDateString())
                    ->count();
                $this->transactionsTrend[] = (int) $txCount;
            }
        } elseif ($this->period === '30_days') {
            $this->periodLabel = '30 Hari Terakhir';
            for ($i = 28; $i >= 0; $i -= 4) {
                $start = now()->subDays($i);
                $end = now()->subDays(max(0, $i - 3));
                $this->chartCategories[] = $start->translatedFormat('d M');

                $rev = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                    ->sum('amount');
                $this->revenueTrend[] = (int) $rev;

                $txCount = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                    ->count();
                $this->transactionsTrend[] = (int) $txCount;
            }
        } elseif ($this->period === 'this_month') {
            $this->periodLabel = 'Bulan Ini (' . now()->translatedFormat('F Y') . ')';
            $daysInMonth = min(now()->day, now()->daysInMonth);
            $step = max(1, (int) ceil($daysInMonth / 6));

            for ($d = 1; $d <= $daysInMonth; $d += $step) {
                $date = now()->setDay($d);
                $this->chartCategories[] = $date->translatedFormat('d M');

                $rev = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('amount');
                $this->revenueTrend[] = (int) $rev;

                $txCount = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereDate('created_at', $date->toDateString())
                    ->count();
                $this->transactionsTrend[] = (int) $txCount;
            }
        } elseif ($this->period === '12_months') {
            $this->periodLabel = '12 Bulan Terakhir';
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $this->chartCategories[] = $date->translatedFormat('M Y');

                $rev = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount');
                $this->revenueTrend[] = (int) $rev;

                $txCount = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $this->transactionsTrend[] = (int) $txCount;
            }
        } else { // 6_months (default)
            $this->periodLabel = '6 Bulan Terakhir';
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $this->chartCategories[] = $date->translatedFormat('M Y');

                $rev = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount');
                $this->revenueTrend[] = (int) $rev;

                $txCount = Subscription::where(function ($q) {
                    $q->where('status', 'active')->orWhereNotNull('starts_at');
                })
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $this->transactionsTrend[] = (int) $txCount;
            }
        }

        // 3. Tool Usage Breakdown (Filtered by Period)
        $startDate = match ($this->period) {
            '7_days' => now()->subDays(7)->startOfDay(),
            '30_days' => now()->subDays(30)->startOfDay(),
            'this_month' => now()->startOfMonth(),
            '12_months' => now()->subYear()->startOfDay(),
            default => now()->subMonths(6)->startOfDay(),
        };
        $endDate = now()->endOfDay();

        $tools = Tool::orderBy('sort_order')->orderBy('name')->get();
        $toolLabels = [];
        $toolSeries = [];

        foreach ($tools as $t) {
            $toolLabels[] = $t->name;
            $actCount = Activity::where('tool_slug', $t->slug)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            $toolSeries[] = $actCount;
        }

        // If activity records in that window are 0, fallback to total_usage_count
        if (array_sum($toolSeries) === 0) {
            $toolSeries = $tools->pluck('total_usage_count')->map(fn ($c) => (int) $c)->toArray();
        }
        if (array_sum($toolSeries) === 0) {
            $toolSeries = array_fill(0, max(1, count($toolLabels)), 1);
        }

        $this->donutSeries = $toolSeries;
        $this->donutLabels = count($toolLabels) > 0 ? $toolLabels : ['Belum Ada Data'];
    }

    public function render()
    {
        // Compute Date Range for Filter
        $startDate = match ($this->period) {
            '7_days' => now()->subDays(7)->startOfDay(),
            '30_days' => now()->subDays(30)->startOfDay(),
            'this_month' => now()->startOfMonth(),
            '12_months' => now()->subYear()->startOfDay(),
            default => now()->subMonths(6)->startOfDay(),
        };
        $endDate = now()->endOfDay();

        // 1. Core Platform Stats - Filtered for Current Period
        $totalAllUsers = User::count();
        $periodUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();

        $activeProUsers = User::whereHas('subscriptions', function ($query) {
            $query->where('status', 'active')->where('expires_at', '>', now());
        })->count();
        $periodProSubs = Subscription::where(function ($q) {
            $q->where('status', 'active')->orWhereNotNull('starts_at');
        })->whereBetween('created_at', [$startDate, $endDate])->count();

        $conversionRate = $totalAllUsers > 0 ? round(($activeProUsers / $totalAllUsers) * 100, 1) : 0;

        $totalAllRevenue = Subscription::where(function ($q) {
            $q->where('status', 'active')->orWhereNotNull('starts_at');
        })->sum('amount');
        $periodRevenue = Subscription::where(function ($q) {
            $q->where('status', 'active')->orWhereNotNull('starts_at');
        })->whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        $totalAllFiles = Activity::count();
        $periodFiles = Activity::whereBetween('created_at', [$startDate, $endDate])->count();

        // 2. Tool Details (Filtered by Period)
        $tools = Tool::orderBy('sort_order')->orderBy('name')->get();
        $toolDetails = [];
        $totalPeriodActivity = Activity::whereBetween('created_at', [$startDate, $endDate])->count();

        foreach ($tools as $t) {
            $count = Activity::where('tool_slug', $t->slug)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // If 0 in this window, show total usage fallback for reference
            $displayCount = $count > 0 ? $count : (int) $t->total_usage_count;
            $percent = $totalPeriodActivity > 0 ? round(($count / $totalPeriodActivity) * 100, 1) : round(($t->total_usage_count / max(1, $tools->sum('total_usage_count'))) * 100, 1);

            $toolDetails[] = [
                'name' => $t->name,
                'slug' => $t->slug,
                'category' => $t->category,
                'count' => $displayCount,
                'period_count' => $count,
                'percent' => $percent,
                'is_pro_only' => $t->is_pro_only,
                'is_maintenance' => $t->is_maintenance,
            ];
        }

        // 3. System Operational Health
        $totalToolsCount = $tools->count();
        $activeToolsCount = $tools->where('is_active', true)->where('is_maintenance', false)->count();
        $proOnlyToolsCount = $tools->where('is_pro_only', true)->count();
        $maintenanceToolsCount = $tools->where('is_maintenance', true)->count();

        // 4. Recent Subscriptions / Transactions Table (Filtered by Period with Fallback)
        $recentTransactions = Subscription::with(['user', 'plan'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        if ($recentTransactions->isEmpty()) {
            $recentTransactions = Subscription::with(['user', 'plan'])
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();
        }

        // 5. Popular Plans Distribution
        $plans = Plan::withCount(['subscriptions' => function ($q) {
            $q->where('status', 'active')->where('expires_at', '>', now());
        }])->orderBy('sort_order')->get();

        return view('livewire.admin.overview', compact(
            'totalAllUsers',
            'periodUsers',
            'activeProUsers',
            'periodProSubs',
            'conversionRate',
            'totalAllRevenue',
            'periodRevenue',
            'totalAllFiles',
            'periodFiles',
            'toolDetails',
            'totalToolsCount',
            'activeToolsCount',
            'proOnlyToolsCount',
            'maintenanceToolsCount',
            'recentTransactions',
            'plans'
        ));
    }
}
