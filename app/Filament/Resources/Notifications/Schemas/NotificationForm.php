<?php

namespace App\Filament\Resources\Notifications\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            SchemaForm::make([
                Section::make('Notification')
                    ->schema([
                        TextInput::make('user.name')
                            ->label('User')
                            ->disabled(),
                        TextInput::make('task.title')
                            ->label('Task')
                            ->disabled(),
                        TextInput::make('channel')
                            ->label('Channel')
                            ->formatStateUsing(fn ($state) => \Illuminate\Support\Str::headline($state))
                            ->disabled(),
                        TextInput::make('status')
                            ->formatStateUsing(fn ($state) => \Illuminate\Support\Str::headline($state))
                            ->disabled(),
                        TextInput::make('scheduled_for')
                            ->label('Scheduled For')
                            ->disabled(),
                        TextInput::make('sent_at')
                            ->label('Sent At')
                            ->disabled(),
                        Textarea::make('payload')
                            ->rows(4)
                            ->formatStateUsing(fn ($state) => empty($state) ? null : json_encode($state, JSON_PRETTY_PRINT))
                            ->disabled()
                            ->columnSpanFull(),
                        Textarea::make('error')
                            ->rows(3)
                            ->disabled(),
                    ])->columns(2),
            ]),
        ]);
    }
}
