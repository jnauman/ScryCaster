<?php

namespace App\Filament\Resources\EncounterResource\Pages;

use App\Filament\Resources\EncounterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Encounter;

class CreateEncounter extends CreateRecord
{
	protected static string $resource = EncounterResource::class;

	/**
	 * Hook that runs after the Encounter record is created.
	 * Use this to automatically add characters from the associated campaign.
	 */
	protected function afterCreate(): void
	{
		// Get the newly created Encounter record
		$encounter = $this->record;

		// Check if the encounter is linked to a campaign
		// Ensure the campaign relationship is loaded if needed
		if ($encounter->campaign_id && $encounter->loadMissing('campaign')->campaign) {

			// Get the IDs of characters linked to the campaign
			// Use 'characters.id' because pluck works on the related model's table directly
			$characterIds = $encounter->campaign->characters()->pluck('characters.id')->toArray();

			// If there are characters in the campaign, attach them to the encounter
			if (!empty($characterIds)) {
				// syncWithoutDetaching avoids errors if a character was somehow already attached
				$encounter->characters()->syncWithoutDetaching($characterIds);

				// Optional: You might want to calculate the initial order right away
				// $encounter->calculateOrder();
			}
		}
	}
}