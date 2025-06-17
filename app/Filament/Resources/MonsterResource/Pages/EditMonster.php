<?php

namespace App\Filament\Resources\MonsterResource\Pages;

use App\Filament\Resources\MonsterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonster extends EditRecord
{
    protected static string $resource = MonsterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
