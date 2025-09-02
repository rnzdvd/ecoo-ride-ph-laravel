<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UsersList extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => User::query())
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('full_name')->label('Name'),
                TextColumn::make('phone_number')->label('Phone Number'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('created_at')->label('Date')->dateTime('M d, Y H:i'),
            ])
            ->filters([])
            ->headerActions([
                //
            ])
            ->recordActions([])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
