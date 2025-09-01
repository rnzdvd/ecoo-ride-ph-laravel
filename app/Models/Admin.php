<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Passwords\CanResetPassword;

class Admin extends Authenticatable implements FilamentUser
{
    protected $table = 'admins';

    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function canAccessFilament(): bool
    {
        return true;
    }

    public function canAccessPanel($panel): bool
    {
        return true;
    }
}
