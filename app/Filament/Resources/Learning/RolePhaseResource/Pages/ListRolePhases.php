<?php

namespace App\Filament\Resources\Learning\RolePhaseResource\Pages;

use App\Filament\Resources\Learning\RolePhaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRolePhases extends ListRecords
{
    protected static string $resource = RolePhaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
