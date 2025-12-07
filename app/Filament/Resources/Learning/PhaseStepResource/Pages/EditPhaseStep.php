<?php

namespace App\Filament\Resources\Learning\PhaseStepResource\Pages;

use App\Filament\Resources\Learning\PhaseStepResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\SaveAction;
use Filament\Resources\Pages\EditRecord;

class EditPhaseStep extends EditRecord
{
    protected static string $resource = PhaseStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SaveAction::make(),
            DeleteAction::make(),
        ];
    }
}
