<?php

namespace App\Filament\Resources\Notifications\Tables;

use App\Enums\NotificationStatus;
use App\Enums\NotificationTransport;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('task.title')
                    ->label('Task')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('channel')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state)),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state)),
                TextColumn::make('scheduled_for')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('error')
                    ->label('Error')
                    ->boolean()
                    ->tooltip(fn ($state) => $state),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->options(collect(NotificationTransport::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all()),
                SelectFilter::make('status')
                    ->options(collect(NotificationStatus::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all()),
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable(),
                TernaryFilter::make('has_error')
                    ->label('Has Error')
                    ->queries(
                        fn ($query) => $query->whereNotNull('error'),
                        fn ($query) => $query->whereNull('error'),
                        fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye'),
            ]);
    }
}
