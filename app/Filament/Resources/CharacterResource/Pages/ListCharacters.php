<?php

namespace App\Filament\Resources\CharacterResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CharacterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCharacters extends ListRecords
{
    protected static string $resource = CharacterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
