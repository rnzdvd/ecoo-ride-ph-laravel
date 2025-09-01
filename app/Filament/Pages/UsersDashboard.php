<?php

namespace App\Filament\Pages;

use App\Livewire\UsersList;
use App\Livewire\UsersOverview;
use Filament\Pages\Page;

class UsersDashboard extends Page
{
    protected static ?string $title = 'Users';
    protected string $view = 'filament.pages.users-dashboard';
    protected static ?int $navigationSort = -1; // puts near top
    protected static bool $shouldRegisterNavigation = true;

    protected function getHeaderWidgets(): array
    {
        return [
            UsersOverview::class,
            UsersList::class,
        ];
    }
}
