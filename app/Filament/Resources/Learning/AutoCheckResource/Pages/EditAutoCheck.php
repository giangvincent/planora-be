<?php

namespace App\Filament\Resources\Learning\AutoCheckResource\Pages;

use App\Filament\Resources\Learning\AutoCheckResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\SaveAction;
use Filament\Resources\Pages\EditRecord;

class EditAutoCheck extends EditRecord
{
    protected static string $resource = AutoCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SaveAction::make(),
            DeleteAction::make(),
        ];
    }
}
