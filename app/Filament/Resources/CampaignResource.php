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
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification; // For displaying notifications
use Filament\Forms\Components\Actions\Action; // For creating hint actions

/**
 * Filament Resource for managing Campaign models.
 *
 * This resource defines the form schema, table columns, relations, and pages
 * for CRUD operations on Campaigns within the Filament admin panel.
 * It ensures that Game Masters (GMs) can only see and manage their own campaigns.
 */
class CampaignResource extends Resource
{
	/** @var string The Eloquent model this resource manages. */
    protected static ?string $model = Campaign::class;

	/** @var string|null The icon to use for navigation. Uses Heroicons. */
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

	/**
	 * Modifies the Eloquent query to filter campaigns for the currently authenticated Game Master.
	 *
	 * This ensures that users can only see campaigns where they are the GM.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public static function getEloquentQuery(): Builder
	{
		// Start with the base query from the parent class.
		// Filter campaigns to only include those where 'gm_user_id' matches the authenticated user's ID.
		return parent::getEloquentQuery()->where('gm_user_id', Auth::id());
	}

	/**
	 * Defines the form schema for creating and editing Campaigns.
	 *
	 * @param \Filament\Forms\Form $form The form instance.
	 * @return \Filament\Forms\Form The configured form.
	 */
    public static function form(Form $form): Form
    {
        return $form
			->schema([
				Forms\Components\TextInput::make('name')
					->label('Campaign Name')
					->required()
					->maxLength(255)
					->columnSpanFull(), // Makes the field span the full width of the form grid.

				Forms\Components\Textarea::make('description')
					->label('Description')
					->rows(5) // Sets the visible number of lines in the textarea.
					->columnSpanFull(),

				Forms\Components\TextInput::make('join_code')
					->label('Join Code')
					->readOnly() // The join code is auto-generated and should not be editable.
					->visibleOn('edit') // This field is only visible when editing an existing campaign.
					->helperText('Share this code with players to allow them to join the campaign.')
					->hintAction( // Adds an action (button) to the hint area of the field.
						Action::make('copyJoinCode') // Use imported Action class
							->label('Copy')
							->icon('heroicon-o-clipboard-document') // Icon for the copy button.
							->requiresConfirmation(false) // No confirmation dialog needed for copying.
							->action(null) // The action is handled by client-side JavaScript.
							->extraAttributes([
								// JavaScript to copy the input's value to the clipboard
								// and show a success notification using Filament's JS Notification class.
								'onclick' => new \Illuminate\Support\HtmlString(
									// Find the input field within the same wrapper and get its value.
									'window.navigator.clipboard.writeText(this.closest(\'.fi-input-wrp\').querySelector(\'input\').value);' .
									// Display a success notification using Filament's built-in JS Notification.
									'new Notification().title(\'Copied to clipboard\').success().send();'
								),
							])
					)
					->columnSpanFull(),
			]);
    }

	/**
	 * Defines the table columns for listing Campaigns.
	 *
	 * @param \Filament\Tables\Table $table The table instance.
	 * @return \Filament\Tables\Table The configured table.
	 */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
				Tables\Columns\TextColumn::make('name')
					->searchable(), // Allows searching by campaign name.
				Tables\Columns\TextColumn::make('gm.name') // Assumes a 'gm' relationship on the Campaign model.
					->label('Game Master')
					->sortable(),
				Tables\Columns\TextColumn::make('join_code')
					->label('Join Code')
					->copyable() // Adds a button to copy the join code to the clipboard.
					->searchable(),
				Tables\Columns\TextColumn::make('created_at')
					->dateTime() // Formats the column as a date and time.
					->sortable()
					->toggleable(isToggledHiddenByDefault: true), // Allows column visibility to be toggled, hidden by default.
				Tables\Columns\TextColumn::make('updated_at')
					->dateTime()
					->sortable()
					->toggleable(isToggledHiddenByDefault: true),
			])
            ->filters([
                // Filters can be defined here if needed.
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Adds an edit action for each row.
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Adds a bulk delete action.
                ]),
            ]);
    }

	/**
	 * Defines the relation managers for this resource.
	 *
	 * Relation managers allow managing related models (e.g., Characters, Encounters)
	 * directly within the Campaign resource's edit page.
	 *
	 * @return array<class-string<\Filament\Resources\RelationManagers\RelationManager>> An array of relation manager class strings.
	 */
    public static function getRelations(): array
    {
		return [
			RelationManagers\CharactersRelationManager::class, // Manages Characters related to the Campaign.
			RelationManagers\EncountersRelationManager::class, // Manages Encounters related to the Campaign.
		];
    }

	/**
	 * Defines the pages (routes) for this resource.
	 *
	 * @return array<string, string> An array mapping page names to routes.
	 */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'), // The main listing page.
            'create' => Pages\CreateCampaign::route('/create'), // The campaign creation page.
            'edit' => Pages\EditCampaign::route('/{record}/edit'), // The campaign editing page.
        ];
    }
}
