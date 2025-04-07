<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

	protected function mutateFormDataBeforeCreate(array $data): array
	{
		// Set the gm_user_id to the currently authenticated user
		$data['gm_user_id'] = Auth::id();

		return $data;
	}
}
