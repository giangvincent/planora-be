<?php

namespace App\Filament\Resources\Learning\RolePhaseResource\Pages;

use App\Filament\Resources\Learning\RolePhaseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\SaveAction;
use Filament\Resources\Pages\EditRecord;

class EditRolePhase extends EditRecord
{
    protected static string $resource = RolePhaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SaveAction::make(),
            DeleteAction::make(),
        ];
    }
}
