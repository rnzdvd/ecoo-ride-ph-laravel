<?php

namespace App\Filament\Pages;

use App\Livewire\ScootersList;
use Filament\Pages\Page;

class ScootersDashboard extends Page
{
    protected static ?string $title = 'Scooters';
    protected string $view = 'filament.pages.scooters-dashboard';

    protected static ?int $navigationSort = -1; // puts near top
    protected static bool $shouldRegisterNavigation = true;

    protected function getHeaderWidgets(): array
    {
        return [
            ScootersList::class,
        ];
    }
}
