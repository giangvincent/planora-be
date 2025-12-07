<?php

namespace App\Filament\Resources\Learning;

use App\Enums\LearningTaskStatus;
use App\Enums\LearningTaskType;
use App\Filament\Resources\Learning\LearningTaskResource\Pages\CreateLearningTask;
use App\Filament\Resources\Learning\LearningTaskResource\Pages\EditLearningTask;
use App\Filament\Resources\Learning\LearningTaskResource\Pages\ListLearningTasks;
use App\Models\LearningTask;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
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

class LearningTaskResource extends Resource
{
    protected static ?string $model = LearningTask::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Learning';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        $types = collect(LearningTaskType::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();
        $statuses = collect(LearningTaskStatus::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();

        return $schema->components([
            SchemaForm::make([
                Section::make('Learning Task')
                    ->schema([
                        Select::make('step_id')
                            ->relationship('step', 'title')
                            ->label('Step')
                            ->required()
                            ->searchable(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options($types)
                            ->required(),
                        Select::make('status')
                            ->options($statuses)
                            ->required(),
                        TextInput::make('order')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        TextInput::make('estimated_minutes')
                            ->numeric()
                            ->minValue(1)
                            ->label('Estimated minutes')
                            ->nullable(),
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->nullable(),
                        Select::make('linked_task_id')
                            ->relationship('linkedTask', 'title')
                            ->label('Linked Task')
                            ->searchable()
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
                TextColumn::make('step.title')->label('Step')->sortable(),
                BadgeColumn::make('type'),
                BadgeColumn::make('status'),
                TextColumn::make('order')->sortable(),
                TextColumn::make('due_date')->date(),
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
            'index' => ListLearningTasks::route('/'),
            'create' => CreateLearningTask::route('/create'),
            'edit' => EditLearningTask::route('/{record}/edit'),
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
