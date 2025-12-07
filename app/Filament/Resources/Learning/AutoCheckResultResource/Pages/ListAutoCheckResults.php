<?php

namespace App\Filament\Resources\Learning\AutoCheckResultResource\Pages;

use App\Filament\Resources\Learning\AutoCheckResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAutoCheckResults extends ListRecords
{
    protected static string $resource = AutoCheckResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
