<?php

namespace App\Filament\Resources\EncounterResource\Pages;

use App\Filament\Resources\EncounterResource;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use App\Events\TurnChanged;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;
use App\Events\EncounterImageUpdated;
use App\Models\CampaignImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class RunEncounter extends ViewRecord
{
    protected static string $resource = EncounterResource::class;
	protected static string $view = 'filament.resources.encounter-resource.pages.run-encounter';

	public function nextTurn()
	{
		// Load both player characters and monster instances to get the total count
		$playerCharacterCount = $this->record->playerCharacters()->count();
		$monsterInstanceCount = $this->record->monsterInstances()->count();
		$totalCombatants = $playerCharacterCount + $monsterInstanceCount;

		if ($totalCombatants === 0) {
			// No combatants, perhaps reset turn/round or do nothing
			$this->record->current_turn = 0;
			$this->record->current_round = $this->record->current_round > 0 ? $this->record->current_round : 1; // Keep round or set to 1
			$this->record->save();
			event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));
			return;
		}

		if ($this->record->current_turn < $totalCombatants) {
			$this->record->current_turn++;
		} else {
			$this->record->current_turn = 1;
			$this->record->current_round++;
		}

		$this->record->save();
		event(new TurnChanged($this->record->id, $this->record->current_turn, $this->record->current_round));
	}

	protected function getHeaderActions(): array
	{
		return [
            Action::make('selectCampaignImage')
                ->label('Select Image')
                ->form([
                    Select::make('selected_campaign_image_id')
                        ->label('Choose an Image')
                        ->options(function () {
                            if (!$this->record->campaign_id) {
                                return [];
                            }
                            return CampaignImage::where('campaign_id', $this->record->campaign_id)
                                ->get()
                                ->mapWithKeys(function (CampaignImage $image) {
                                    $caption = $image->caption ? " ({$image->caption})" : '';
                                    $imageUrl = $image->image_url; // Uses the accessor
                                    return [$image->id => new HtmlString(
                                        '<div style="display: flex; align-items: center;">' .
                                        '<img src="' . e($imageUrl) . '" alt="' . e($image->original_filename) . '" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px; border-radius: 4px;" />' .
                                        '<div>' .e($image->original_filename . $caption) . '</div>'.
                                        '</div>'
                                    )];
                                })
                                ->all();
                        })
                        ->allowHtml() // Important for rendering the image tag
                        ->searchable()
                        ->required()
                        ->default($this->record->selected_campaign_image_id),
                ])
                ->action(function (array $data) {
                    $this->record->update(['selected_campaign_image_id' => $data['selected_campaign_image_id']]);
                    $selectedImage = CampaignImage::find($data['selected_campaign_image_id']);
                    if ($selectedImage) {
                        event(new EncounterImageUpdated($this->record->id, $selectedImage->image_url));
                        \Filament\Notifications\Notification::make()
                            ->title('Image selected successfully')
                            ->success()
                            ->send();
                    }
                })
                ->icon('heroicon-o-photo'),

            Action::make('uploadAndSelectCampaignImage')
                ->label('Upload New Image')
                ->form([
                    FileUpload::make('new_campaign_image')
                        ->label('Upload Image')
                        ->image()
                        ->disk('public')
                        ->directory('campaign-images/' . $this->record->campaign_id)
                        ->preserveFilenames()
                        ->required(),
                    Textarea::make('caption')
                        ->label('Caption (Optional)')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    if (!$this->record->campaign_id) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error: Encounter not linked to a campaign.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $newImage = CampaignImage::create([
                        'campaign_id' => $this->record->campaign_id,
                        'uploader_user_id' => auth()->id(),
                        'image_path' => $data['new_campaign_image'],
                        'original_filename' => basename($data['new_campaign_image']), // Or use TemporaryUploadedFile methods if available
                        'caption' => $data['caption'],
                    ]);

                    $this->record->update(['selected_campaign_image_id' => $newImage->id]);
                    event(new EncounterImageUpdated($this->record->id, $newImage->image_url));

                    \Filament\Notifications\Notification::make()
                        ->title('Image uploaded and selected successfully')
                        ->success()
                        ->send();
                })
                ->icon('heroicon-o-arrow-up-tray'),
		];
	}
}
