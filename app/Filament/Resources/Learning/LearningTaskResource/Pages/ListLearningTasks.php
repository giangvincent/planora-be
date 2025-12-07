<?php

namespace App\Filament\Resources\Learning\LearningTaskResource\Pages;

use App\Filament\Resources\Learning\LearningTaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLearningTasks extends ListRecords
{
    protected static string $resource = LearningTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
