<x-filament-panels::page>
	<div class="p-4">
		@if ($record)
			<h1 class="text-2xl font-bold mb-4">Run Encounter: {{ $record->name }}</h1>
			<p class="text-lg mb-2">Round: {{ $record->current_round }}</p>
			<div id="encounter-{{ $record->id }}" class="mb-4">
				<ul class="space-y-2">
					@foreach ($record->characters->sortBy('pivot.order') as $character)
						<li class="p-3 rounded-lg flex items-center justify-between
						@if ($character->type == 'monster' && $character->pivot->order != $record->current_turn)
							monster-not-turn
						@elseif ($character->type == 'monster' && $character->pivot->order == $record->current_turn)
							monster-current-turn
						@elseif ($character->type == 'player' && $character->pivot->order != $record->current_turn)
							player-not-turn
						@else
							player-current-turn
						@endif
					" data-order="{{ $character->pivot->order }}">
							<div class="flex-grow">
								<span class="font-semibold">{{ $character->name }}</span>
							</div>
							<div class="flex-grow">
								<span class="font-semibold">AC: {{ $character->ac }}</span>
							</div>
							<div class="text-sm">
								<span>Init: {{ $character->pivot->initiative_roll }}</span>
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
	{{-- @vite(['resources/css/app.css', 'resources/js/app.js'])--}}
	@vite('resources/css/app.css')

</x-filament-panels::page>

