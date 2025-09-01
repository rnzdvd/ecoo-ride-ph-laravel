<?php

namespace App\Filament\Pages;

use App\Livewire\SalesList;
use App\Livewire\SalesOverview;
use Filament\Pages\Page;

class SalesDashboard extends Page
{
    protected static ?string $title = 'Sales';
    protected static ?int $navigationSort = -1; // puts near top
    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.pages.sales-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            SalesOverview::class,
            SalesList::class,
        ];
    }
}
