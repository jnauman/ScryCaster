<x-filament-panels::page>

	<div class="p-4">
		@if ($record)
			{{-- Page Header --}}
			<h1 class="text-2xl font-bold mb-4">Run Encounter: {{ $record->name }}</h1>
			<p class="text-lg mb-2">Round: <span class="font-semibold">{{ $record->current_round ?? 0 }}</span></p>
			<p class="text-lg mb-4">Turn: <span class="font-semibold">{{ $record->current_turn ?? 'Not Started' }}</span></p>

			{{-- Monster Detail Modal --}}
			@if ($this->showMonsterDetailModal && $this->selectedMonsterForModal)
				<div class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 p-4" wire:transition.opacity>
					<div class="bg-gray-900 p-6 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
						<div class="flex justify-between items-center mb-4">
							<h2 class="text-2xl font-bold text-white">{{ $this->selectedMonsterForModal['name'] ?? 'Monster Details' }}</h2>
							<button wire:click="$set('showMonsterDetailModal', false)" class="text-gray-400 hover:text-gray-200">
								<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
							</button>
						</div>
						<div class="space-y-4 text-gray-300">
							{{-- Core Info --}}
							<div class="grid grid-cols-2 md:grid-cols-3 gap-4 p-3 bg-gray-800 rounded-md">
								<div><strong>AC:</strong> {{ $this->selectedMonsterForModal['ac'] ?? 'N/A' }}</div>
								<div><strong>Movement:</strong> {{ $this->selectedMonsterForModal['movement'] ?? 'N/A' }}</div>
								<div><strong>Alignment:</strong> {{ Str::title($this->selectedMonsterForModal['alignment'] ?? 'N/A') }}</div>
							</div>

							{{-- Stats --}}
							<div class="p-3 bg-gray-800 rounded-md">
								<h4 class="text-md font-semibold text-gray-100 mb-2">Stats</h4>
								<div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 text-sm">
									<span><strong>STR:</strong> {{ $this->selectedMonsterForModal['strength'] ?? 'N/A' }}</span>
									<span><strong>DEX:</strong> {{ $this->selectedMonsterForModal['dexterity'] ?? 'N/A' }}</span>
									<span><strong>CON:</strong> {{ $this->selectedMonsterForModal['constitution'] ?? 'N/A' }}</span>
									<span><strong>INT:</strong> {{ $this->selectedMonsterForModal['intelligence'] ?? 'N/A' }}</span>
									<span><strong>WIS:</strong> {{ $this->selectedMonsterForModal['wisdom'] ?? 'N/A' }}</span>
									<span><strong>CHA:</strong> {{ $this->selectedMonsterForModal['charisma'] ?? 'N/A' }}</span>
								</div>
							</div>

							{{-- Description --}}
							@if (!empty($this->selectedMonsterForModal['description']))
								<div class="p-3 bg-gray-800 rounded-md">
									<h4 class="text-md font-semibold text-gray-100 mb-1">Description</h4>
									<p class="text-sm whitespace-pre-wrap">{{ $this->selectedMonsterForModal['description'] }}</p>
								</div>
							@endif

							{{-- Attacks --}}
							@if (!empty($this->selectedMonsterForModal['attacks']))
								<div class="p-3 bg-gray-800 rounded-md">
									<h4 class="text-md font-semibold text-gray-100 mb-1">Attacks</h4>
									<p class="text-sm whitespace-pre-wrap">{{ $this->selectedMonsterForModal['attacks'] }}</p>
								</div>
							@endif

							{{-- Traits --}}
							@if (!empty($this->selectedMonsterForModal['traits']))
								<div class="p-3 bg-gray-800 rounded-md">
									<h4 class="text-md font-semibold text-gray-100 mb-2">Traits</h4>
									<ul class="space-y-2">
										@foreach ($this->selectedMonsterForModal['traits'] as $trait)
											<li class="text-sm">
												<strong class="text-gray-200">{{ $trait['name'] ?? 'Unknown Trait' }}:</strong>
												<span class="text-gray-400">{{ $trait['description'] ?? 'No description provided.' }}</span>
											</li>
										@endforeach
									</ul>
								</div>
							@endif
						</div>
						<div class="mt-6 flex justify-end">
							<button type="button" wire:click="$set('showMonsterDetailModal', false)" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-md">
								Close
							</button>
						</div>
					</div>
				</div>
			@endif

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
											   style="background-color: var(--color-havelock-blue-900);"
											   class="w-24 text-white border border-gray-600 rounded-md p-2 focus:ring-primary-500 focus:border-primary-500"
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
							{{-- ★★★ START OF MODIFIED BLOCK ★★★ --}}
							<li class="relative p-4 rounded-lg flex flex-col sm:flex-row items-start sm:items-center justify-between bg-gray-800 shadow ring-2 {{ $baseRingColor }} transition-all {{ $isCurrentTurn ? 'transform scale-105 shadow-lg' : '' }}"
								wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}">

								{{-- AC Block (Absolutely Positioned) --}}
								@if ($combatant['type'] === 'monster_instance')
									<div class="absolute top-4 right-4 flex items-center space-x-1.5 text-white" title="Armor Class">
										<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
											<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.286zm0 13.036h.008v.008h-.008v-.008z" />
										</svg>
										<span class="text-2xl font-bold">{{ $combatant['ac'] ?? 'N/A' }}</span>
									</div>
								@endif

								{{-- Left Side: Name, Type, Stats --}}
								{{-- Added pr-24 to prevent text from overlapping with the absolute AC block --}}
								<div class="flex-grow mb-3 sm:mb-0 w-full sm:w-auto pr-24">
									<div class="flex items-center">
                               <span class="font-bold text-xl {{ $isCurrentTurn ? 'text-white' : 'text-gray-100' }} mr-2 {{ $combatant['type'] === 'monster_instance' ? 'cursor-pointer hover:text-primary-400' : '' }}"
									 @if ($combatant['type'] === 'monster_instance')
										 wire:click="showMonsterModal({{ $combatant['id'] }})"
                                  @endif
                               >
                                  {{ $combatant['name'] }}
                               </span>
										<span class="text-xs px-2 py-0.5 rounded-full {{ $combatant['type'] === 'player' ? 'bg-blue-600' : 'bg-red-600' }} text-white">
                                  {{ Str::studly($combatant['type']) }}
                               </span>
									</div>
									<div class="text-sm {{ $turnTextColor }}">
										Turn Order: {{ $combatant['order'] }} | Initiative: {{ $combatant['initiative_roll'] ?? 'N/A' }}
									</div>

									{{-- GM Only Monster Stats --}}
									@if ($combatant['type'] === 'monster_instance')
										<span class="flex items-center" title="Movement Speed">
                                      <strong class="text-gray-400 font-medium">Movement: </strong>
                                      {{ $combatant['movement'] ?? 'N/A' }}
                               </span>
										<div class="mt-3 pt-2 text-sm font-medium text-gray-200 grid grid-cols-3 md:grid-cols-3 gap-x-4 gap-y-2">
											<span><strong class="text-gray-400 font-medium">STR:</strong> {{ $combatant['strength'] ?? 'N/A' }}</span>
											<span><strong class="text-gray-400 font-medium">DEX:</strong> {{ $combatant['dexterity'] ?? 'N/A' }}</span>
											<span><strong class="text-gray-400 font-medium">CON:</strong> {{ $combatant['constitution'] ?? 'N/A' }}</span>
											<span><strong class="text-gray-400 font-medium">INT:</strong> {{ $combatant['intelligence'] ?? 'N/A' }}</span>
											<span><strong class="text-gray-400 font-medium">WIS:</strong> {{ $combatant['wisdom'] ?? 'N/A' }}</span>
											<span><strong class="text-gray-400 font-medium">CHA:</strong> {{ $combatant['charisma'] ?? 'N/A' }}</span>
										</div>
									@endif
								</div>

								{{-- Right Side: HP Controls & Remove Button --}}
								@if ($combatant['type'] === 'monster_instance')
									<div class="flex items-center space-x-3 mt-2 sm:mt-0 sm:ml-4 self-end sm:self-center">
										{{-- HP Controls Group --}}
										<div class="flex items-center space-x-1.5" title="Health Points">
											<svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
												<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
											</svg>
											<input type="number"
												   id="hp-{{ $combatant['id'] }}"
												   wire:key="hp-input-{{ $combatant['id'] }}"
												   value="{{ $combatant['current_health'] }}"
												   wire:change="updateMonsterHp({{ $combatant['id'] }}, $event.target.value)"
												   style="background-color: var(--color-havelock-blue-900);"
												   class="w-20 rounded-md border-2 border-gray-600 p-1
                                               text-center text-lg font-bold text-white
                                               transition-colors duration-200 ease-in-out
                                               focus:border-yellow-400 focus:outline-none focus:ring-0"
												   min="0"
												   max="{{ $combatant['max_health'] }}">
											{{--<span class="text-lg text-gray-400">/ {{ $combatant['max_health'] }}</span>--}}
										</div>

										{{-- Remove Monster Button --}}
										<button wire:click="removeMonsterInstance({{ $combatant['id'] }})"
												wire:confirm="Are you sure you want to remove {{ $combatant['name'] }} from the encounter?"
												type="button"
												class="p-1 text-red-500 hover:text-red-400 focus:outline-none">
											<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
												<path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
											</svg>
										</button>
									</div>
								@endif
							</li>
							{{-- ★★★ END OF MODIFIED BLOCK ★★★ --}}
						@endforeach
					</ul>
				</div>

				{{-- Next Turn Button --}}
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

	{{-- If not using Vite for CSS, ensure styles for dynamic classes are available --}}
	{{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
	@vite('resources/css/app.css')

</x-filament-panels::page>