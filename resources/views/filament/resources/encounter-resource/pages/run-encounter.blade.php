<x-filament-panels::page>

	<div class="p-4">
		@if ($record)
			<h1 class="text-2xl font-bold mb-4">Run Encounter: {{ $record->name }}</h1>
			<p class="text-lg mb-2">Round: <span class="font-semibold">{{ $record->current_round ?? 0 }}</span></p>
			<p class="text-lg mb-4">Turn: <span class="font-semibold">{{ $record->current_turn ?? 'Not Started' }}</span></p>

			{{-- Initiative Modal --}}
			@if ($this->showInitiativeModal)
				<div class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 p-4" wire:transition.opacity>
					<div class="bg-gray-900 p-6 rounded-lg shadow-xl w-full max-w-lg max-h-[80vh] overflow-y-auto">
						<h2 class="text-xl font-bold mb-4 text-white">Enter Initiative Rolls</h2>
						<form wire:submit.prevent="saveInitiativesAndStartEncounter">
							<div class="space-y-4">
								@forelse ($this->initiativeInputs as $index => $combatantInput)
									<div class="flex items-center space-x-3 p-3 bg-gray-800 rounded-md">
										<label for="initiative-{{ $combatantInput['key'] }}" class="text-gray-300 flex-1">
											{{ $combatantInput['name'] }} ({{ Str::studly($combatantInput['type']) }})
										</label>
										<input type="number"
											   id="initiative-{{ $combatantInput['key'] }}"
											   wire:model.defer="initiativeInputs.{{ $index }}.initiative"
											   class="w-24 bg-gray-700 text-white border border-gray-600 rounded-md p-2 focus:ring-primary-500 focus:border-primary-500"
											   placeholder="Roll">
									</div>
								@empty
									<p class="text-gray-400">No combatants found to set initiative for.</p>
								@endforelse
							</div>
							<div class="mt-6 flex justify-end space-x-3">
                                <button type="button" wire:click="$set('showInitiativeModal', false)" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-md">
                                    Cancel
                                </button>
								<button type="submit"
										class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md disabled:opacity-50"
										@if(empty($this->initiativeInputs)) disabled @endif>
									Save Initiatives & Start Encounter
								</button>
							</div>
						</form>
					</div>
				</div>
			@endif

			{{-- Combatant List --}}
			@if (!$this->showInitiativeModal && !empty($this->combatantsForView))
				<div id="encounter-{{ $record->id }}-combatants" class="mb-4">
					<h2 class="text-xl font-semibold mb-3 text-gray-200">Combatants (Turn Order)</h2>
					<ul class="space-y-3">
						@foreach ($this->combatantsForView as $index => $combatant)
							@php
								$isCurrentTurn = $combatant['order'] == $record->current_turn;
								$baseRingColor = $isCurrentTurn ? 'ring-yellow-400' : 'ring-gray-700';
								$turnTextColor = $isCurrentTurn ? 'text-yellow-300' : 'text-gray-400';
							@endphp
							<li class="p-4 rounded-lg flex flex-col sm:flex-row items-start sm:items-center justify-between bg-gray-800 shadow ring-2 {{ $baseRingColor }} transition-all {{ $isCurrentTurn ? 'transform scale-105 shadow-lg' : '' }}"
								wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}">

								<div class="flex-grow mb-3 sm:mb-0">
									<div class="flex items-center">
										<span class="font-bold text-xl {{ $isCurrentTurn ? 'text-white' : 'text-gray-100' }} mr-2">
											{{ $combatant['name'] }}
										</span>
										<span class="text-xs px-2 py-0.5 rounded-full {{ $combatant['type'] === 'player' ? 'bg-blue-600' : 'bg-red-600' }} text-white">
											{{ Str::studly($combatant['type']) }}
										</span>
									</div>
									<div class="text-sm {{ $turnTextColor }}">
										Turn Order: {{ $combatant['order'] }} | Initiative: {{ $combatant['initiative_roll'] ?? 'N/A' }}
									</div>
								</div>

								@if ($combatant['type'] === 'monster_instance')

									<div class="flex items-center space-x-2 mt-2 sm:mt-0">
										<label for="hp-{{ $combatant['id'] }}" class="text-sm text-gray-300">HP:</label>

										<input type="number"
											   id="hp-{{ $combatant['id'] }}"
											   wire:key="hp-input-{{ $combatant['id'] }}"
											   value="{{ $combatant['current_health'] }}"
											   wire:change="updateMonsterHp({{ $combatant['id'] }}, $event.target.value)"
											   style="color:#1b1b18"
											   class="w-24 rounded-lg border-2 border-gray-600 bg-gray-800 p-1
												text-center text-xl font-bold text-white
												transition-colors duration-200 ease-in-out
												focus:border-yellow-400 focus:outline-none focus:ring-0"
											   min="0"
											   max="{{ $combatant['max_health'] }}">
										<span class="text-sm text-gray-400"> / {{ $combatant['max_health'] }}</span>
									</div>
								@endif
							</li>
						@endforeach
					</ul>
				</div>

				<button wire:click="nextTurn"
						class="mt-6 bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-50"
						@if(empty($this->combatantsForView)) disabled @endif>
					Next Turn
				</button>
			@elseif (!$this->showInitiativeModal && empty($this->combatantsForView))
				<p class="text-gray-400">No combatants in this encounter yet, or initiative has not been set.</p>
				<p class="text-gray-500 text-sm">If you've added combatants, try refreshing. The initiative modal should appear if needed.</p>
			@endif
		@else
			<p class="text-red-500">Encounter record not found.</p>
		@endif
	</div>

	{{-- Ensure Tailwind styles for dynamic classes are available if not using @vite --}}
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
	@vite('resources/css/app.css') {{-- Assuming Vite is used for CSS --}}

</x-filament-panels::page>