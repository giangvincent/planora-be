<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\NotificationChannel;
use DateTimeZone;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $timezoneOptions = collect(DateTimeZone::listIdentifiers())
            ->mapWithKeys(fn (string $tz): array => [$tz => $tz])
            ->all();

        $roleOptions = Role::query()->pluck('name', 'name')->all();

        return $schema->components([
            SchemaForm::make([
                Section::make('Profile')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->required()
                            ->email()
                            ->maxLength(255),
                        Select::make('timezone')
                            ->label('Timezone')
                            ->options($timezoneOptions)
                            ->searchable()
                            ->required(),
                        Select::make('notification_channel')
                            ->label('Notification Channel')
                            ->options(collect(NotificationChannel::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)])->all())
                            ->required(),
                    ])->columns(2),
                Section::make('Roles')
                    ->schema([
                        CheckboxList::make('roles')
                            ->label('Roles')
                            ->options($roleOptions)
                            ->relationship('roles', 'name')
                            ->columns(3),
                        TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Only fill when you need to reset the password.'),
                    ])->columns(2),
            ])->columns(1),
        ]);
    }
}
