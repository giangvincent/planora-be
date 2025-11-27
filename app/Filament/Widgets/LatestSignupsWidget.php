<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestSignupsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'half';

    protected function getTableQuery(): ?Builder
    {
        return User::query()->latest()->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('Name')->sortable(),
            TextColumn::make('email')->label('Email'),
            TextColumn::make('created_at')->label('Joined')->dateTime(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest Signups')
            ->columns($this->getTableColumns())
            ->contentGrid(['md' => 1])
            ->paginated(false);
    }
}
