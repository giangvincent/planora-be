<?php

namespace App\Filament\Resources\Learning;

use App\Enums\DifficultyLevel;
use App\Filament\Resources\Learning\PhaseStepResource\Pages\CreatePhaseStep;
use App\Filament\Resources\Learning\PhaseStepResource\Pages\EditPhaseStep;
use App\Filament\Resources\Learning\PhaseStepResource\Pages\ListPhaseSteps;
use App\Models\PhaseStep;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class PhaseStepResource extends Resource
{
    protected static ?string $model = PhaseStep::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static string|UnitEnum|null $navigationGroup = 'Learning';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        $difficulty = collect(DifficultyLevel::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();

        return $schema->components([
            SchemaForm::make([
                Section::make('Step')
                    ->schema([
                        Select::make('phase_id')
                            ->relationship('phase', 'title')
                            ->label('Phase')
                            ->required()
                            ->searchable(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('order')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        Select::make('difficulty_level')
                            ->options($difficulty)
                            ->default(DifficultyLevel::Intro->value),
                    ])->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('phase.title')->label('Phase')->sortable(),
                BadgeColumn::make('difficulty_level'),
                TextColumn::make('order')->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPhaseSteps::route('/'),
            'create' => CreatePhaseStep::route('/create'),
            'edit' => EditPhaseStep::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
