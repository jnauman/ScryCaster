<?php

namespace App\Filament\Pages;


use App\Filament\Widgets\RecentEncounters;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
	// By removing the `$view` property, we bypass the environmental bug.
	// protected static string $view = 'filament.pages.dashboard';

	public function getWidgets(): array
	{
		return [
			AccountWidget::class,
			StatsOverview::class,
			RecentEncounters::class,
		];
	}
}