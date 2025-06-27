<?php

namespace App\Filament\Resources\MonsterResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\MonsterResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use App\Livewire\BulkImportMonsters;
use Filament\Actions\Action;

class ListMonsters extends ListRecords
{
    protected static string $resource = MonsterResource::class;

	protected function getHeaderActions(): array
	{
		return [
			CreateAction::make(),
			Action::make('bulkImportMonsters')
				  ->label('Bulk Import Monsters')
				  ->color('primary')
				// CORRECTED LINE: Use the dedicated ->view() method
				  ->view('livewire.bulk-import-monsters-wrapper')
				  ->modalHeading('Import Monsters from JSON')
				  ->modalSubmitAction(false)
				  ->modalCancelAction(fn (Action $action) => $action->label('Close'))
				  ->modalWidth('lg')
		];
	}

    protected $listeners = ['refreshMonstersTable' => '$refresh'];
}
