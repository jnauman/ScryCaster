<?php

namespace App\Filament\Resources\MonsterResource\Pages;

use App\Filament\Resources\MonsterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMonsters extends ListRecords
{
    protected static string $resource = MonsterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
