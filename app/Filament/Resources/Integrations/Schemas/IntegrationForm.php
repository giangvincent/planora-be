<?php

namespace App\Filament\Resources\Integrations\Schemas;

use App\Enums\IntegrationProvider;
use App\Enums\IntegrationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class IntegrationForm
{
    public static function configure(Schema $schema): Schema
    {
        $providers = collect(IntegrationProvider::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();
        $statuses = collect(IntegrationStatus::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all();

        return $schema->components([
            SchemaForm::make([
                Section::make('Integration Details')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('User')
                            ->searchable()
                            ->required(),
                        Select::make('provider')
                            ->options($providers)
                            ->required(),
                        Select::make('status')
                            ->options($statuses)
                            ->required(),
                        DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->seconds(false),
                        TextInput::make('access_token')
                            ->label('Access Token')
                            ->password()
                            ->maxLength(65535)
                            ->nullable(),
                        TextInput::make('refresh_token')
                            ->label('Refresh Token')
                            ->password()
                            ->maxLength(65535)
                            ->nullable(),
                        Textarea::make('settings')
                            ->rows(4)
                            ->helperText('JSON configuration options for the integration.')
                            ->formatStateUsing(fn ($state) => empty($state) ? null : json_encode($state, JSON_PRETTY_PRINT))
                            ->dehydrateStateUsing(function (?string $state): array {
                                if (blank($state)) {
                                    return [];
                                }

                                return json_decode($state, true, 512, JSON_THROW_ON_ERROR);
                            })
                            ->nullable(),
                    ])->columns(2),
            ]),
        ]);
    }
}
