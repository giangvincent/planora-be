<?php

namespace App\Filament\Resources\Learning\LearningTaskResource\Pages;

use App\Filament\Resources\Learning\LearningTaskResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\SaveAction;
use Filament\Resources\Pages\EditRecord;

class EditLearningTask extends EditRecord
{
    protected static string $resource = LearningTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SaveAction::make(),
            DeleteAction::make(),
        ];
    }
}
