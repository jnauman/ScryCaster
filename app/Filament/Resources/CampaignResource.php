<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

	public static function getEloquentQuery(): Builder
	{
		// Start with the base query and restrict to the logged-in GM
		return parent::getEloquentQuery()->where('gm_user_id', Auth::id());
	}

    public static function form(Form $form): Form
    {
        return $form
			->schema([
				 // Field for Campaign Name
				 Forms\Components\TextInput::make('name')
										   ->required() // Make it required
										   ->maxLength(255)
										   ->columnSpanFull(), // Make it take the full width initially

				 // Field for Campaign Description
				 Forms\Components\Textarea::make('description')
										  ->rows(5) // Give it a bit more height
										  ->columnSpanFull(), // Make it take the full width

				 // Field to display the Join Code (only on the Edit page)
				 Forms\Components\TextInput::make('join_code')
										   ->label('Join Code') // Set a user-friendly label
										   ->readOnly() // Prevent editing, it's auto-generated
										   //->copyable() // Add a button to easily copy the code  THIS DIDNT WORK
										   ->visibleOn('edit') // Only show this field when editing, not creating
										   ->helperText('Share this code with players to allow them to join.')
										   ->columnSpanFull(), // Make it take the full width
			 ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
			   Tables\Columns\TextColumn::make('name')
										->searchable(),
			   // Assuming you have the 'gm' relationship defined in Campaign model
			   Tables\Columns\TextColumn::make('gm.name')
										->label('Game Master')
										->sortable(),
			   Tables\Columns\TextColumn::make('join_code')
										->label('Join Code')
										->copyable() // Make it easily copyable
										->searchable(),
			   Tables\Columns\TextColumn::make('created_at')
										->dateTime()
										->sortable()
										->toggleable(isToggledHiddenByDefault: true), // Hide by default
			   Tables\Columns\TextColumn::make('updated_at')
										->dateTime()
										->sortable()
										->toggleable(isToggledHiddenByDefault: true), // Hide by default
			])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
		return [
			RelationManagers\CharactersRelationManager::class,
			RelationManagers\EncountersRelationManager::class,
		];

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
			// You might have a 'view' page too:
			// 'view' => Pages\ViewCampaign::route('/{record}'),
        ];
    }
}
