<?php

namespace App\Filament\Resources\Learning\AutoCheckResultResource\Pages;

use App\Filament\Resources\Learning\AutoCheckResultResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\SaveAction;
use Filament\Resources\Pages\EditRecord;

class EditAutoCheckResult extends EditRecord
{
    protected static string $resource = AutoCheckResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SaveAction::make(),
            DeleteAction::make(),
        ];
    }
}
