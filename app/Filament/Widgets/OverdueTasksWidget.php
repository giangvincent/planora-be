<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Task;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class OverdueTasksWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'half';

    protected function getTableQuery(): ?Builder
    {
        $now = CarbonImmutable::now('UTC');

        return Task::query()
            ->with('user')
            ->where('status', 'pending')
            ->where(function ($query) use ($now) {
                $query
                    ->whereNotNull('due_at')
                    ->where('due_at', '<', $now)
                    ->orWhere(function ($q) use ($now) {
                        $q->whereNotNull('due_date')
                            ->where('due_date', '<', $now->toDateString());
                    });
            })
            ->orderByRaw('COALESCE(due_at, due_date) DESC')
            ->limit(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Overdue Tasks')
            ->columns([
                TextColumn::make('title')->label('Task')->wrap(),
                TextColumn::make('user.name')->label('Owner'),
                TextColumn::make('due_at')
                    ->label('Due')
                    ->formatStateUsing(function ($state, Task $record) {
                        if ($record->due_at) {
                            return $record->due_at->diffForHumans();
                        }

                        return optional($record->due_date)->format('Y-m-d');
                    }),
            ])
            ->paginated(false);
    }
}
