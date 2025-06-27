<?php

namespace App\Filament\Resources\EncounterResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\EncounterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEncounters extends ListRecords
{
    protected static string $resource = EncounterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
