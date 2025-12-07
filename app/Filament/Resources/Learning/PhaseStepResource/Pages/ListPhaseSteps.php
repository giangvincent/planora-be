<?php

namespace App\Filament\Resources\Learning\PhaseStepResource\Pages;

use App\Filament\Resources\Learning\PhaseStepResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPhaseSteps extends ListRecords
{
    protected static string $resource = PhaseStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
