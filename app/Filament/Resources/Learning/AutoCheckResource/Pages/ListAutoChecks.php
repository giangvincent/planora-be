<?php

namespace App\Filament\Resources\Learning\AutoCheckResource\Pages;

use App\Filament\Resources\Learning\AutoCheckResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAutoChecks extends ListRecords
{
    protected static string $resource = AutoCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
