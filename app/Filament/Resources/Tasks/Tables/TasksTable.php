<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Owner')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('goal.title')
                    ->label('Goal')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state)),
                TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Str::headline($state)),
                TextColumn::make('due_at')
                    ->label('Due At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('all_day')
                    ->label('All Day')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all()),
                SelectFilter::make('priority')
                    ->options(collect(TaskPriority::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all()),
                SelectFilter::make('user_id')
                    ->label('Owner')
                    ->relationship('user', 'name')
                    ->searchable(),
                SelectFilter::make('goal_id')
                    ->label('Goal')
                    ->relationship('goal', 'title')
                    ->searchable(),
                TernaryFilter::make('all_day')
                    ->label('All Day'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn (Task $record) => $record->status === TaskStatus::Pending->value)
                    ->action(fn (Task $record) => $record->update(['status' => TaskStatus::Done->value])),
                Action::make('skip')
                    ->label('Skip')
                    ->icon('heroicon-o-forward')
                    ->requiresConfirmation()
                    ->visible(fn (Task $record) => $record->status === TaskStatus::Pending->value)
                    ->color('warning')
                    ->action(fn (Task $record) => $record->update(['status' => TaskStatus::Skipped->value])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
