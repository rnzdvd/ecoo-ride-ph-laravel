<?php

namespace App\Livewire;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class UsersOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {

        $today = Carbon::today();
        $last7Days = Carbon::now()->subDays(7);
        $startOfMonth = Carbon::now()->startOfMonth();

        $stats = User::select([
            DB::raw('COUNT(*) as total_users'),
            DB::raw("SUM(CASE WHEN DATE(created_at) = '" . $today->toDateString() . "' THEN 1 ELSE 0 END) as today_users"),
            DB::raw("SUM(CASE WHEN created_at >= '" . $last7Days->toDateTimeString() . "' THEN 1 ELSE 0 END) as last_7_days_users"),
            DB::raw("SUM(CASE WHEN created_at >= '" . $startOfMonth->toDateTimeString() . "' THEN 1 ELSE 0 END) as this_month_users"),
        ])->first();


        return [
            Stat::make('Total Registered Users',   number_format($stats->total_users)),
            Stat::make('Today\'s Registered Users',  number_format($stats->today_users)),
            Stat::make('Last 7 Days Registered Users', number_format($stats->last_7_days_users)),
            Stat::make('This Month Registered Users',  number_format($stats->this_month_users)),
        ];
    }
}
