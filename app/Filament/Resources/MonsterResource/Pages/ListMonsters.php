<?php

namespace App\Filament\Resources\MonsterResource\Pages;

use App\Filament\Resources\MonsterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Livewire\BulkImportMonsters;
use Filament\Actions\Action;

class ListMonsters extends ListRecords
{
    protected static string $resource = MonsterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('bulkImportMonsters')
                ->label('Bulk Import Monsters')
                ->color('primary')
                ->modalContent(fn (): string => view('livewire.bulk-import-monsters')->render())
                ->modalHeading('Import Monsters from JSON')
                ->modalSubmitAction(false) // We handle submission within the Livewire component
                ->modalCancelAction(fn (Action $action) => $action->label('Close'))
                ->modalWidth('lg') // Removed semicolon here
                // Event listening for table refresh will be handled by the $listeners property.
        ];
    }

    protected $listeners = ['refreshMonstersTable' => '$refresh'];
}
