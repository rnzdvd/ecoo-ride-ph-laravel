<?php

namespace App\Livewire;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class ScootersList extends TableWidget
{
    protected int $cacheSeconds = 60;
    protected int|string|array $columnSpan = 'full';


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('status')
                    ->color(fn($record) => $record['status'] == 'online' ? 'success' : 'danger'),
                TextColumn::make('battery')->formatStateUsing(fn($state) => $state . '%'),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public function getTableRecords(): Collection
    {
        // Call API
        $response = (new \App\Http\Controllers\ScooterController)->getOnlineScooters();
        $json = json_decode($response->getContent(), true);
        $data = $json['data'] ?? [];

        $records = collect($data)->map(fn($item) => array_merge(['key' => (string) $item['id']], $item));

        // Manually apply filter from query string
        $status = request()->input('status');
        if ($status) {
            $records = $records->filter(fn($record) => $record['status'] === $status);
        }

        return $records;
    }

    // Tell Filament how to get a unique key for each row
    public function getTableRecordKey($record): string
    {
        return (string) $record['id']; // must return string
    }
}
