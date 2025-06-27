<?php

namespace App\Filament\Widgets;

use Filament\Actions\ViewAction;
use App\Filament\Resources\EncounterResource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentEncounters extends BaseWidget
{
	protected static ?string $heading = 'Recent Encounters';

	protected int | string | array $columnSpan = 'full';

	public function table(Table $table): Table
	{
		return $table
			->query(
				EncounterResource::getEloquentQuery()->latest()->limit(5)
			)
			->columns([
						  TextColumn::make('name')
									->label('Encounter Name'),
						  TextColumn::make('campaign.name')
									->label('Campaign'),
						  TextColumn::make('created_at')
									->label('Created')
									->since(),
					  ])
			->recordActions([
						  ViewAction::make()->url(fn ($record) => EncounterResource::getUrl('run', ['record' => $record])),
					  ]);
	}
}