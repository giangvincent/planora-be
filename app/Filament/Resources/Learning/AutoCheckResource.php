<?php

namespace App\Filament\Resources\Learning;

use App\Enums\AutoCheckType;
use App\Filament\Resources\Learning\AutoCheckResource\Pages\CreateAutoCheck;
use App\Filament\Resources\Learning\AutoCheckResource\Pages\EditAutoCheck;
use App\Filament\Resources\Learning\AutoCheckResource\Pages\ListAutoChecks;
use App\Models\AutoCheck;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
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

class AutoCheckResource extends Resource
{
    protected static ?string $model = AutoCheck::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Learning';

    public static function form(Schema $schema): Schema
    {
        $types = collect(AutoCheckType::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();

        return $schema->components([
            SchemaForm::make([
                Section::make('Auto Check')
                    ->schema([
                        Select::make('learning_task_id')
                            ->relationship('learningTask', 'title')
                            ->label('Learning Task')
                            ->required()
                            ->searchable(),
                        Select::make('type')
                            ->options($types)
                            ->required(),
                        KeyValue::make('config')
                            ->label('Config (JSON)')
                            ->columnSpanFull()
                            ->nullable(),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('learningTask.title')->label('Learning Task')->searchable()->sortable(),
                BadgeColumn::make('type'),
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
            'index' => ListAutoChecks::route('/'),
            'create' => CreateAutoCheck::route('/create'),
            'edit' => EditAutoCheck::route('/{record}/edit'),
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
