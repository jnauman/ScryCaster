<x-filament-panels::page>

	<div class="p-4">
		@if ($record)
			{{-- Page Header --}}
			<h1 class="text-2xl font-bold mb-4">Run Encounter: {{ $record->name }}</h1>
			<p class="text-lg mb-2">Round: <span class="font-semibold">{{ $record->current_round ?? 0 }}</span></p>
			<p class="text-lg mb-2">Turn: <span class="font-semibold">{{ $record->current_turn ?? 'Not Started' }}</span></p>

            {{-- Torch Timer Controls --}}
            <div class="my-4">
                @livewire('torch-timer-controls', ['encounter' => $record])
            </div>

			{{-- Action Buttons Container --}}
			<div class="my-4 flex space-x-3">
				<x-filament::button wire:click="displayInitiativeModal" icon="heroicon-o-play">
					Roll Initiative!
				</x-filament::button>

				{{-- Next Turn Button --}}
				<x-filament::button wire:click="nextTurn"
									color="primary"
									icon="heroicon-o-arrow-path"
									:disabled="empty($this->combatantsForView) || $record->current_turn === null || $record->current_turn === 0">
					Next Turn
				</x-filament::button>
			</div>

			{{-- Monster Detail Modal REMOVED --}}

			{{-- Initiative Modal --}}
			@if ($this->showInitiativeModal)
				<div class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 p-4" wire:transition.opacity>
					<div class="bg-gray-900 p-6 rounded-lg shadow-xl w-full max-w-lg max-h-[80vh] overflow-y-auto">
						<h2 class="text-xl font-bold mb-4 text-white">Enter Initiative Rolls</h2>
						<form wire:submit.prevent="saveInitiativesAndStartEncounter">
							<div class="space-y-4">
								@forelse ($this->initiativeInputs as $inputKey => $inputData)
									<div class="flex items-center space-x-3 p-3 bg-gray-800 rounded-md">
										<label for="initiative-{{ $inputKey }}" class="text-gray-300 flex-1">
											{{ $inputData['name'] }}
											@if ($inputData['type'] === 'player')
												(Player)
											@elseif ($inputData['type'] === 'monster_instance')
												(Monster)
											@elseif ($inputData['type'] === 'monster_group')
												(Group)
											@endif
										</label>
										<input type="number"
											   id="initiative-{{ $inputKey }}"
											   wire:model.defer="initiativeInputs.{{ $inputKey }}.initiative"
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
						@foreach ($this->combatantsForView as $groupOrIndividualIndex => $groupOrIndividual)
							@if ($groupOrIndividual['type'] === 'group')
								<li class="rounded-lg overflow-hidden shadow-md"
									style="border-left: 5px solid {{ $groupOrIndividual['group_css_classes'] ?: '#4b5563' }}; background-color: rgba(55, 65, 81, 0.3);"
									wire:key="group-{{ $groupOrIndividual['name'] }}-{{ $groupOrIndividualIndex }}">
									<h4 class="text-md font-semibold py-2 px-3 text-gray-300 bg-gray-700 bg-opacity-50">
										Group: {{ $groupOrIndividual['name'] }}
									</h4>
									<ul class="space-y-px pt-px pb-1"> {{-- Inner list for combatants within the group --}}
										@foreach ($groupOrIndividual['combatants'] as $combatantIndex => $combatant)
											@php
												$isCurrentTurn = $combatant['order'] == $record->current_turn;
												$baseRingColor = $isCurrentTurn ? 'ring-yellow-400' : 'ring-gray-700';
												$turnTextColor = $isCurrentTurn ? 'text-yellow-300' : 'text-gray-400';
											@endphp
											{{-- Individual Combatant LI - Reusing existing structure --}}
											<li class="relative p-4 mx-1 rounded-md flex flex-col sm:flex-row items-start sm:items-center justify-between bg-gray-800 shadow ring-1 {{ $baseRingColor }} transition-all {{ $isCurrentTurn ? 'transform scale-[1.02] shadow-lg' : '' }}"
												wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}-group-{{ $groupOrIndividual['name'] }}">
												{{-- AC Block --}}
												@if ($combatant['type'] === 'monster_instance')
													<div class="absolute top-3 right-3 flex items-center space-x-1.5 text-white" title="Armor Class">
														<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
															<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.286zm0 13.036h.008v.008h-.008v-.008z" />
														</svg>
														<span class="text-xl font-bold">{{ $combatant['ac'] ?? 'N/A' }}</span>
													</div>
												@endif
												{{-- Left Side: Name, Type, Stats --}}
												<div class="flex-grow mb-3 sm:mb-0 w-full sm:w-auto pr-16 sm:pr-20">
													<div class="flex items-center">
														<span class="font-bold text-lg {{ $isCurrentTurn ? 'text-white' : 'text-gray-100' }} mr-2">
															{{ $combatant['name'] }}
                                                            {{-- Initiative group name is now part of the parent group display --}}
														</span>
														<span class="text-xs px-2 py-0.5 rounded-full {{ $combatant['type'] === 'player' ? 'bg-blue-600' : 'bg-red-600' }} text-white mr-2">
															{{ Str::studly($combatant['type']) }}
														</span>
														@if ($combatant['type'] === 'monster_instance')
															<button wire:click="toggleMonsterDetail({{ $combatant['id'] }})" class="text-gray-400 hover:text-yellow-400 transition-colors focus:outline-none" title="Toggle Details">
																@if ($this->expandedMonsterInstances[$combatant['id']] ?? false)
																	<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
																@else
																	<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
																@endif
															</button>
														@endif
													</div>
													<div class="text-sm {{ $turnTextColor }}">Turn Order: {{ $combatant['order'] }} | Init: {{ $combatant['initiative_roll'] ?? 'N/A' }}</div>
													@if ($combatant['type'] === 'monster_instance')
														<div class="mt-1">
															<span class="flex items-center text-xs" title="Movement Speed"><strong class="text-gray-400 font-medium mr-1">Mov:</strong> {{ $combatant['movement'] ?? 'N/A' }}</span>
															<div class="mt-1 text-xs font-medium text-gray-300 grid grid-cols-3 gap-x-2 gap-y-0.5">
																<span><strong class="text-gray-400">STR:</strong> {{ $combatant['strength'] ?? 'N/A' }}</span>
																<span><strong class="text-gray-400">DEX:</strong> {{ $combatant['dexterity'] ?? 'N/A' }}</span>
																<span><strong class="text-gray-400">CON:</strong> {{ $combatant['constitution'] ?? 'N/A' }}</span>
																<span><strong class="text-gray-400">INT:</strong> {{ $combatant['intelligence'] ?? 'N/A' }}</span>
																<span><strong class="text-gray-400">WIS:</strong> {{ $combatant['wisdom'] ?? 'N/A' }}</span>
																<span><strong class="text-gray-400">CHA:</strong> {{ $combatant['charisma'] ?? 'N/A' }}</span>
															</div>
														</div>
													@endif
												</div>
												{{-- Right Side: HP Controls & Remove Button --}}
												@if ($combatant['type'] === 'monster_instance')
													<div class="flex items-center space-x-2 mt-2 sm:mt-0 sm:ml-3 self-end sm:self-center">
														<div class="flex items-center space-x-1" title="Health Points">
															<svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
															<input type="number" id="hp-{{ $combatant['id'] }}" wire:key="hp-input-{{ $combatant['id'] }}" value="{{ $combatant['current_health'] }}" wire:change="updateMonsterHp({{ $combatant['id'] }}, $event.target.value)" style="background-color: var(--color-havelock-blue-900);" class="w-16 rounded border-gray-600 p-1 text-center text-md font-bold text-white focus:border-yellow-400 focus:ring-0" min="0" max="{{ $combatant['max_health'] }}">
														</div>
														<button wire:click="removeMonsterInstance({{ $combatant['id'] }})" wire:confirm="Are you sure you want to remove {{ $combatant['name'] }} from the encounter?" type="button" class="p-1 text-red-500 hover:text-red-400 focus:outline-none">
															<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
														</button>
													</div>
												@endif
											</li>
											{{-- Collapsible Monster Details Section --}}
											@if ($combatant['type'] === 'monster_instance' && ($this->expandedMonsterInstances[$combatant['id']] ?? false))
												<li class="bg-gray-850 p-3 mx-1 rounded-b-md -mt-px mb-px shadow-inner ring-1 {{ $baseRingColor }}" wire:key="monster-detail-{{ $combatant['id'] }}">
													<div class="space-y-2 text-xs text-gray-300">
														@if (!empty($combatant['description']))
															<div><h5 class="text-sm font-semibold text-gray-100 mb-0.5">Desc</h5><p class="whitespace-pre-wrap">{{ $combatant['description'] }}</p></div>
														@endif
														@if (!empty($combatant['traits']))
															<div><h5 class="text-sm font-semibold text-gray-100 mb-0.5">Traits</h5><ul class="space-y-0.5 list-disc list-inside pl-1">@foreach ($combatant['traits'] as $trait)<li><strong class="text-gray-200">{{ $trait['name'] ?? 'Trait' }}:</strong> <span class="text-gray-400">{{ $trait['description'] ?? 'N/A' }}</span></li>@endforeach</ul></div>
														@endif
														@if (!empty($combatant['attacks']))
															<div><h5 class="text-sm font-semibold text-gray-100 mb-0.5">Attacks</h5><p class="whitespace-pre-wrap">{{ is_array($combatant['attacks']) ? json_encode($combatant['attacks']) : $combatant['attacks'] }}</p></div>
														@endif
													</div>
												</li>
											@endif
										@endforeach
									</ul>
								</li>
							@else {{-- Item is an Individual Player or Monster --}}
								@php
									// There's only one combatant in the 'combatants' array for individuals
									$combatant = $groupOrIndividual['combatants'][0];
									$isCurrentTurn = $combatant['order'] == $record->current_turn;
									$baseRingColor = $isCurrentTurn ? 'ring-yellow-400' : 'ring-gray-700';
									$turnTextColor = $isCurrentTurn ? 'text-yellow-300' : 'text-gray-400';
								@endphp
								{{-- Individual Combatant LI - Reusing existing structure --}}
								<li class="relative p-4 rounded-lg flex flex-col sm:flex-row items-start sm:items-center justify-between bg-gray-800 shadow ring-2 {{ $baseRingColor }} transition-all {{ $isCurrentTurn ? 'transform scale-105 shadow-lg' : '' }}"
									wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}-individual">
									{{-- AC Block --}}
									@if ($combatant['type'] === 'monster_instance')
										<div class="absolute top-4 right-4 flex items-center space-x-1.5 text-white" title="Armor Class">
											<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.286zm0 13.036h.008v.008h-.008v-.008z" /></svg>
											<span class="text-2xl font-bold">{{ $combatant['ac'] ?? 'N/A' }}</span>
										</div>
									@endif
									{{-- Left Side: Name, Type, Stats --}}
									<div class="flex-grow mb-3 sm:mb-0 w-full sm:w-auto pr-24">
										<div class="flex items-center">
											<span class="font-bold text-xl {{ $isCurrentTurn ? 'text-white' : 'text-gray-100' }} mr-2">
												{{ $combatant['name'] }}
												{{-- Initiative group name is not shown for individual monsters not in a defined group --}}
											</span>
											<span class="text-xs px-2 py-0.5 rounded-full {{ $combatant['type'] === 'player' ? 'bg-blue-600' : 'bg-red-600' }} text-white mr-2">
												{{ Str::studly($combatant['type']) }}
											</span>
											@if ($combatant['type'] === 'monster_instance')
												<button wire:click="toggleMonsterDetail({{ $combatant['id'] }})" class="text-gray-400 hover:text-yellow-400 transition-colors focus:outline-none" title="Toggle Details">
													@if ($this->expandedMonsterInstances[$combatant['id']] ?? false)
														<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
													@else
														<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
													@endif
												</button>
											@endif
										</div>
										<div class="text-sm {{ $turnTextColor }}">Turn Order: {{ $combatant['order'] }} | Initiative: {{ $combatant['initiative_roll'] ?? 'N/A' }}</div>
										@if ($combatant['type'] === 'monster_instance')
											<div class="mt-1">
												<span class="flex items-center text-sm" title="Movement Speed"><strong class="text-gray-400 font-medium mr-1">Movement:</strong> {{ $combatant['movement'] ?? 'N/A' }}</span>
												<div class="mt-2 text-xs font-medium text-gray-200 grid grid-cols-3 md:grid-cols-3 gap-x-3 gap-y-1">
													<span><strong class="text-gray-400 font-medium">STR:</strong> {{ $combatant['strength'] ?? 'N/A' }}</span>
													<span><strong class="text-gray-400 font-medium">DEX:</strong> {{ $combatant['dexterity'] ?? 'N/A' }}</span>
													<span><strong class="text-gray-400 font-medium">CON:</strong> {{ $combatant['constitution'] ?? 'N/A' }}</span>
													<span><strong class="text-gray-400 font-medium">INT:</strong> {{ $combatant['intelligence'] ?? 'N/A' }}</span>
													<span><strong class="text-gray-400 font-medium">WIS:</strong> {{ $combatant['wisdom'] ?? 'N/A' }}</span>
													<span><strong class="text-gray-400 font-medium">CHA:</strong> {{ $combatant['charisma'] ?? 'N/A' }}</span>
												</div>
											</div>
										@endif
									</div>
									{{-- Right Side: HP Controls & Remove Button --}}
									@if ($combatant['type'] === 'monster_instance')
										<div class="flex items-center space-x-3 mt-2 sm:mt-0 sm:ml-4 self-end sm:self-center">
											<div class="flex items-center space-x-1.5" title="Health Points">
												<svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
												<input type="number" id="hp-{{ $combatant['id'] }}" wire:key="hp-input-{{ $combatant['id'] }}" value="{{ $combatant['current_health'] }}" wire:change="updateMonsterHp({{ $combatant['id'] }}, $event.target.value)" style="background-color: var(--color-havelock-blue-900);" class="w-20 rounded-md border-2 border-gray-600 p-1 text-center text-lg font-bold text-white transition-colors duration-200 ease-in-out focus:border-yellow-400 focus:outline-none focus:ring-0" min="0" max="{{ $combatant['max_health'] }}">
											</div>
											<button wire:click="removeMonsterInstance({{ $combatant['id'] }})" wire:confirm="Are you sure you want to remove {{ $combatant['name'] }} from the encounter?" type="button" class="p-1 text-red-500 hover:text-red-400 focus:outline-none">
												<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
											</button>
										</div>
									@endif
								</li>
								{{-- Collapsible Monster Details Section for Individual Monsters --}}
								@if ($combatant['type'] === 'monster_instance' && ($this->expandedMonsterInstances[$combatant['id']] ?? false))
									<li class="bg-gray-850 p-4 rounded-b-lg -mt-3 mb-3 shadow-inner ring-2 {{ $baseRingColor }}" wire:key="monster-detail-{{ $combatant['id'] }}-individual">
										<div class="space-y-3 text-sm text-gray-300">
											@if (!empty($combatant['description']))
												<div><h4 class="text-md font-semibold text-gray-100 mb-1">Description</h4><p class="text-xs whitespace-pre-wrap">{{ $combatant['description'] }}</p></div>
											@endif
											@if (!empty($combatant['traits']))
												<div><h4 class="text-md font-semibold text-gray-100 mb-1">Traits</h4><ul class="space-y-1 list-disc list-inside pl-2">@foreach ($combatant['traits'] as $trait)<li><strong class="text-gray-200">{{ $trait['name'] ?? 'Trait' }}:</strong> <span class="text-gray-400">{{ $trait['description'] ?? 'N/A' }}</span></li>@endforeach</ul></div>
											@endif
											@if (!empty($combatant['attacks']))
												<div><h4 class="text-md font-semibold text-gray-100 mb-1">Attacks</h4><p class="text-xs whitespace-pre-wrap">{{ is_array($combatant['attacks']) ? json_encode($combatant['attacks']) : $combatant['attacks'] }}</p></div>
											@endif
										</div>
									</li>
								@endif
							@endif
						@endforeach
					</ul>
				</div>

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