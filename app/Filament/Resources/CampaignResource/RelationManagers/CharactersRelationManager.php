<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use App\Models\Character;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CharactersRelationManager extends RelationManager
{
	protected static string $relationship = 'characters'; // Matches the relationship method name in Campaign

	// No form needed for basic attach/detach
	// public function form(Form $form): Form
	// {
	//     return $form
	//         ->schema([
	//             Forms\Components\TextInput::make('name') // Example if you wanted to edit characters here
	//                 ->required()
	//                 ->maxLength(255),
	//         ]);
	// }

	public function table(Table $table): Table
	{
		return $table
			// Use recordTitleAttribute if your Character model has one, otherwise remove this line
			// ->recordTitleAttribute('name')
			->columns([
						  Tables\Columns\TextColumn::make('name')
												   ->searchable()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('type') // Show character type (PC/Monster)
												   ->searchable()
												   ->sortable(),
						  // Add other character columns as needed, e.g., Owner:
						  Tables\Columns\TextColumn::make('user.name')
												   ->label('Player') // Assuming user relationship is defined
												   ->searchable()
												   ->sortable()
												   ->toggleable(isToggledHiddenByDefault: true),
					  ])
			->filters([
						  //
					  ])
			->headerActions([
								// Action to attach existing characters
								Tables\Actions\AttachAction::make()
														   ->preloadRecordSelect() // Preloads options for better performance/UX
														   ->multiple() // Allow attaching multiple characters at once
															->recordTitle(fn (Character $record): string => "{$record->name} - {$record->type}")
														   ->recordSelectSearchColumns(['name']),
								// Optional: Action to create a NEW character and attach it
								// Tables\Actions\CreateAction::make(),
							])
			->actions([
						  // Action to detach a character from the campaign
						  Tables\Actions\DetachAction::make(),
						  // Optional: Action to view the character resource page
						  // Tables\Actions\ViewAction::make(),
						  // Optional: Action to edit the character resource page (not pivot data)
						  // Tables\Actions\EditAction::make(),
					  ])
			->bulkActions([
							  Tables\Actions\BulkActionGroup::make([
																	   // Action to detach multiple selected characters
																	   Tables\Actions\DetachBulkAction::make(),
																	   // Optional: Action to delete characters entirely (use with caution)
																	   // Tables\Actions\DeleteBulkAction::make(),
																   ]),
						  ]);
	}
}