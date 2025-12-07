<?php

namespace App\Filament\Resources\Learning\RoleResource\Pages;

use App\Filament\Resources\Learning\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\SaveAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SaveAction::make(),
            DeleteAction::make(),
        ];
    }
}
