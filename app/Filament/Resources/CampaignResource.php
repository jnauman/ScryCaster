<?php

namespace App\Filament\Resources;

// Remove this: use Filament\Schemas\Schema;
use App\Filament\Resources\CampaignResource\Pages\CreateCampaign;
use App\Filament\Resources\CampaignResource\Pages\EditCampaign;
use App\Filament\Resources\CampaignResource\Pages\ListCampaigns;
use App\Filament\Resources\CampaignResource\RelationManagers\CampaignImagesRelationManager;
use App\Filament\Resources\CampaignResource\RelationManagers\CharactersRelationManager;
use App\Filament\Resources\CampaignResource\RelationManagers\EncountersRelationManager;
use App\Models\Campaign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

// Add this import for Filament\Forms\Form
// For creating hint actions

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
	protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

	/**
	 * Modifies the Eloquent query to filter campaigns for the currently authenticated Game Master.
	 *
	 * This ensures that users can only see campaigns where they are the GM.
	 *
	 * @return Builder
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
	 * @param \Filament\Forms\Form $form The form instance.  // *** CHANGED PARAMETER TYPE HINT ***
	 * @return \Filament\Forms\Form The configured form.   // *** CHANGED RETURN TYPE HINT ***
	 */
	public static function form(Schema $schema): Schema
	{
		return $schema
			->schema([
					   TextInput::make('name')
								->label('Campaign Name')
								->required()
								->maxLength(255)
								->columnSpanFull(), // Makes the field span the full width of the form grid.

					   Textarea::make('description')
							   ->label('Description')
							   ->rows(5) // Sets the visible number of lines in the textarea.
							   ->columnSpanFull(),

					   TextInput::make('join_code')
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
																'onclick' => new HtmlString(
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
	 * @param Table $table The table instance.
	 * @return Table The configured table.
	 */
	public static function table(Table $table): Table
	{
		return $table
			->columns([
						  TextColumn::make('name')
									->searchable(), // Allows searching by campaign name.
						  TextColumn::make('gm.name') // Assumes a 'gm' relationship on the Campaign model.
									->label('Game Master')
									->sortable(),
						  TextColumn::make('join_code')
									->label('Join Code')
									->copyable() // Adds a button to copy the join code to the clipboard.
									->searchable(),
						  TextColumn::make('created_at')
									->dateTime() // Formats the column as a date and time.
									->sortable()
									->toggleable(isToggledHiddenByDefault: true), // Allows column visibility to be toggled, hidden by default.
						  TextColumn::make('updated_at')
									->dateTime()
									->sortable()
									->toggleable(isToggledHiddenByDefault: true),
					  ])
			->filters([
						  //
					  ])
			->recordActions([
								EditAction::make(), // Adds an edit action for each row.
							])
			->toolbarActions([
								 BulkActionGroup::make([
														   DeleteBulkAction::make(), // Adds a bulk delete action.
													   ]),
							 ]);
	}

	/**
	 * Defines the relation managers for this resource.
	 *
	 * Relation managers allow managing related models (e.g., Characters, Encounters)
	 * directly within the Campaign resource's edit page.
	 *
	 * @return array<class-string<RelationManager>> An array of relation manager class strings.
	 */
	public static function getRelations(): array
	{
		return [
			CharactersRelationManager::class, // Manages Characters related to the Campaign.
			EncountersRelationManager::class, // Manages Encounters related to the Campaign.
			CampaignImagesRelationManager::class, // Manages Images related to the Campaign.
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
			'index' => ListCampaigns::route('/'), // The main listing page.
			'create' => CreateCampaign::route('/create'), // The campaign creation page.
			'edit' => EditCampaign::route('/{record}/edit'), // The campaign editing page.
		];
	}
}