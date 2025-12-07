<?php

namespace App\Filament\Resources\Learning;

use App\Filament\Resources\Learning\RolePhaseResource\Pages\CreateRolePhase;
use App\Filament\Resources\Learning\RolePhaseResource\Pages\EditRolePhase;
use App\Filament\Resources\Learning\RolePhaseResource\Pages\ListRolePhases;
use App\Models\RolePhase;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RolePhaseResource extends Resource
{
    protected static ?string $model = RolePhase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Learning';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaForm::make([
                Section::make('Phase')
                    ->schema([
                        Select::make('role_id')
                            ->relationship('role', 'title')
                            ->required()
                            ->label('Role')
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
                        TextInput::make('estimated_duration_weeks')
                            ->numeric()
                            ->minValue(1)
                            ->nullable(),
                    ])->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('role.title')->label('Role')->searchable()->sortable(),
                TextColumn::make('order')->sortable(),
                TextColumn::make('estimated_duration_weeks')->label('Weeks'),
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
            'index' => ListRolePhases::route('/'),
            'create' => CreateRolePhase::route('/create'),
            'edit' => EditRolePhase::route('/{record}/edit'),
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
