<?php

namespace App\Filament\Resources\Learning;

use App\Filament\Resources\Learning\AutoCheckResultResource\Pages\CreateAutoCheckResult;
use App\Filament\Resources\Learning\AutoCheckResultResource\Pages\EditAutoCheckResult;
use App\Filament\Resources\Learning\AutoCheckResultResource\Pages\ListAutoCheckResults;
use App\Models\AutoCheckResult;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
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
use UnitEnum;

class AutoCheckResultResource extends Resource
{
    protected static ?string $model = AutoCheckResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Learning';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaForm::make([
                Section::make('Result')
                    ->schema([
                        Select::make('auto_check_id')
                            ->relationship('autoCheck', 'id')
                            ->label('Auto Check')
                            ->required()
                            ->searchable(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        TextInput::make('score')
                            ->numeric()
                            ->required(),
                        TextInput::make('max_score')
                            ->numeric()
                            ->required(),
                        Select::make('passed')
                            ->options([
                                1 => 'Passed',
                                0 => 'Failed',
                            ])
                            ->required(),
                        KeyValue::make('attempt_data')
                            ->label('Attempt Data')
                            ->columnSpanFull()
                            ->nullable(),
                    ])->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('autoCheck.learningTask.title')->label('Learning Task')->wrap()->sortable(),
                TextColumn::make('user.name')->label('User')->sortable(),
                TextColumn::make('score'),
                TextColumn::make('max_score'),
                BadgeColumn::make('passed')->colors([
                    'success' => true,
                    'danger' => false,
                ])->formatStateUsing(fn ($state) => $state ? 'Passed' : 'Failed'),
                TextColumn::make('created_at')->dateTime()->sortable(),
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
            'index' => ListAutoCheckResults::route('/'),
            'create' => CreateAutoCheckResult::route('/create'),
            'edit' => EditAutoCheckResult::route('/{record}/edit'),
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
