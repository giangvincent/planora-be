<?php

namespace App\Filament\Resources\Goals\Schemas;

use App\Enums\GoalStatus;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Slider;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GoalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            SchemaForm::make([
                Section::make('Goal Details')
                    ->schema([
                        Select::make('user_id')
                            ->label('Owner')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options(collect(GoalStatus::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all())
                            ->required(),
                        Slider::make('progress')
                            ->min(0)
                            ->max(100)
                            ->step(5)
                            ->label('Progress (%)'),
                        DatePicker::make('target_date')
                            ->label('Target Date'),
                        ColorPicker::make('color')
                            ->label('Color'),
                    ])->columns(2),
            ]),
        ]);
    }
}
