<?php

namespace App\Livewire;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SalesOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today     = Carbon::today();
        $last7days = Carbon::now()->subDays(7);
        $thisMonth = Carbon::now()->month;

        $totals = Sale::query()
            ->select([
                DB::raw("SUM(CASE WHEN status = 'succeeded' THEN amount ELSE 0 END) as total_sales"),
                DB::raw("SUM(CASE WHEN status = 'succeeded' AND DATE(created_at) = ? THEN amount ELSE 0 END) as today_sales"),
                DB::raw("SUM(CASE WHEN status = 'succeeded' AND created_at >= ? THEN amount ELSE 0 END) as last7days_sales"),
                DB::raw("SUM(CASE WHEN status = 'succeeded' AND MONTH(created_at) = ? THEN amount ELSE 0 END) as thismonth_sales"),
            ])
            ->addBinding($today, 'select')
            ->addBinding($last7days, 'select')
            ->addBinding($thisMonth, 'select')
            ->first();

        return [
            Stat::make('Total Sales', '₱' . number_format($totals->total_sales, 2)),
            Stat::make('Today\'s Sales', '₱' . number_format($totals->today_sales, 2)),
            Stat::make('Last 7 Days', '₱' . number_format($totals->last7days_sales, 2)),
            Stat::make('This Month', '₱' . number_format($totals->thismonth_sales, 2)),
        ];
    }
}
