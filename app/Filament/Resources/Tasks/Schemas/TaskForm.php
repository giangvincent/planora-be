<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        $statusOptions = collect(TaskStatus::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();
        $priorityOptions = collect(TaskPriority::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();

        return $schema->components([
            SchemaForm::make([
                Section::make('Task Details')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Owner')
                            ->required()
                            ->searchable(),
                        Select::make('goal_id')
                            ->relationship('goal', 'title')
                            ->label('Goal')
                            ->searchable()
                            ->nullable(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options($statusOptions)
                            ->required(),
                        Select::make('priority')
                            ->options($priorityOptions)
                            ->required(),
                        Toggle::make('all_day')
                            ->label('All day task')
                            ->reactive(),
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->visible(fn (callable $get) => $get('all_day')),
                        DateTimePicker::make('due_at')
                            ->label('Due At')
                            ->seconds(false)
                            ->visible(fn (callable $get) => ! $get('all_day')),
                        TextInput::make('estimated_minutes')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1440)
                            ->nullable()
                            ->suffix('min'),
                        TextInput::make('actual_minutes')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1440)
                            ->nullable()
                            ->suffix('min'),
                        TextInput::make('repeat_rule')
                            ->label('Repeat Rule')
                            ->maxLength(255)
                            ->nullable(),
                    ])->columns(2),
            ]),
        ]);
    }
}
