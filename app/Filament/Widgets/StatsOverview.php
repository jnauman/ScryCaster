<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\Character;
use App\Models\Monster;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
	protected function getStats(): array
	{
		return [
			Stat::make('Total Campaigns', Campaign::count())
				->description('All active campaigns')
				->icon('heroicon-o-book-open')
				->color('success'),

			Stat::make('Player Characters', Character::count())
				->description('All registered player characters')
				->icon('heroicon-o-user-group')
				->color('primary'),

			Stat::make('Monster Library', Monster::count())
				->description('Unique monsters available')
				->icon('heroicon-o-shield-exclamation')
				->color('danger'),
		];
	}
}