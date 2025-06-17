<x-filament-panels::page>

	<div class="p-4">
		@if ($record)
			<h1 class="text-2xl font-bold mb-4">Run Encounter: {{ $record->name }}</h1>
			<p class="text-lg mb-2">Round: {{ $record->current_round }}</p>
			<div id="encounter-{{ $record->id }}" class="mb-4">
				<ul class="space-y-2">
					{{-- The getCombatants() method now returns a single, sorted list --}}
					@foreach ($record->getCombatants() as $combatant)
						@php
							// Determine the type of the combatant for easier logic
							$isPlayer = $combatant instanceof \App\Models\Character;
							$isMonster = $combatant instanceof \App\Models\MonsterInstance;

							// Determine if it's the current combatant's turn
							$order = $isPlayer ? $combatant->pivot->order : $combatant->order;
							$isCurrentTurn = $order == $record->current_turn;

							// Build the CSS classes for highlighting
							$cssClasses = '';
							if ($isPlayer) {
								$cssClasses = $isCurrentTurn ? 'player-current-turn' : 'player-not-turn';
							} elseif ($isMonster) {
								$cssClasses = $isCurrentTurn ? 'monster-current-turn' : 'monster-not-turn';
							}
						@endphp

						<li class="p-3 rounded-lg flex items-center justify-between {{ $cssClasses }}" data-order="{{ $order }}">
							<div class="flex-grow">
								{{-- Get name from the base monster or the character itself --}}
								<span class="font-semibold">{{ $isPlayer ? $combatant->name : $combatant->monster->name }}</span>
							</div>
							<div class="flex-grow">
								{{-- Get AC from the base monster or the character itself --}}
								<span class="font-semibold">AC: {{ $isPlayer ? $combatant->ac : $combatant->monster->ac }}</span>
							</div>
							<div class="text-sm">
								{{-- Get initiative from the pivot for players or the instance for monsters --}}
								<span>Init: {{ $isPlayer ? $combatant->pivot->initiative_roll : $combatant->initiative_roll }}</span>
							</div>
						</li>
					@endforeach
				</ul>
			</div>

			<button wire:click="nextTurn" class="mt-4 bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-4 rounded-lg">
				Next Turn
			</button>
		@else
			<p class="text-red-500">Encounter not found.</p>
		@endif
	</div>

	@vite('resources/css/app.css')

</x-filament-panels::page>