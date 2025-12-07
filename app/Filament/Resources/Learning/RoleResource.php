<?php

namespace App\Filament\Resources\Learning;

use App\Enums\RoleStatus;
use App\Enums\RoleVisibility;
use App\Filament\Resources\Learning\RoleResource\Pages\CreateRole;
use App\Filament\Resources\Learning\RoleResource\Pages\EditRole;
use App\Filament\Resources\Learning\RoleResource\Pages\ListRoles;
use App\Models\Role;
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

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Learning';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        $visibility = collect(RoleVisibility::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();
        $status = collect(RoleStatus::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();

        return $schema->components([
            SchemaForm::make([
                Section::make('Role')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->label('Owner')
                            ->searchable(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->maxLength(255)
                            ->helperText('Optional; generated from title when blank.'),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('visibility')
                            ->options($visibility)
                            ->default(RoleVisibility::Private->value)
                            ->required(),
                        Select::make('status')
                            ->options($status)
                            ->default(RoleStatus::Draft->value)
                            ->required(),
                        TextInput::make('estimated_duration_weeks')
                            ->numeric()
                            ->minValue(1)
                            ->label('Estimated weeks')
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
                TextColumn::make('user.name')->label('Owner')->sortable(),
                BadgeColumn::make('visibility')->colors([
                    'primary',
                    'warning' => RoleVisibility::Unlisted->value,
                    'success' => RoleVisibility::Public->value,
                ]),
                BadgeColumn::make('status')->colors([
                    'success' => RoleStatus::Active->value,
                    'warning' => RoleStatus::Draft->value,
                    'secondary' => RoleStatus::Archived->value,
                ]),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([])
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
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
