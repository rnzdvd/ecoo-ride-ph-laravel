<?php

namespace App\Livewire;

use App\Models\Sale;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class SalesList extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => Sale::query())
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('user.full_name')->label('Customer'),
                TextColumn::make('amount')->label('Amount')->money('php'),
                TextColumn::make('status')->label('Status')
                    ->color(fn($record) => $record->status == 'succeeded' ? 'success' : 'danger'),
                TextColumn::make('payment_method')->label('Channel'),
                TextColumn::make('created_at')->label('Date')->dateTime('M d, Y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
