<div class="p-4">
    <div id="app">
    </div>
    @if ($encounter)
        <h1 class="text-3xl font-extrabold mb-4 text-center text-blue-400">Encounter: {{ $encounter->name }}</h1>
        <p class="text-xl mb-6 text-center text-gray-300">Round: {{ $encounter->current_round }}</p>

        <div class="flex flex-col lg:flex-row w-full items-start lg:h-[calc(100vh-200px)] gap-6"> {{-- Added responsive flex and gap --}}

            {{-- Combatants List --}}
            <div class="w-full lg:w-1/3 flex-shrink-0 lg:pr-4 overflow-y-auto h-96 lg:h-full bg-gray-800 p-4 rounded-lg shadow-lg"> {{-- Responsive width, padding, background --}}
                <h2 class="text-2xl font-bold mb-4 text-white">Turn Order</h2> {{-- Added heading for clarity --}}
                <div id="encounter-{{ $encounter->id }}-combatants">
                    <ul class="space-y-3"> {{-- Increased space --}}
                        @forelse ($combatants as $combatant)
                            <li class="p-4 rounded-lg flex flex-col md:flex-row items-start md:items-center justify-between transition-all duration-150 ease-in-out
                                {{ $combatant['css_classes'] }}
                                {{-- Highlight the current turn combatant --}}
                                @if (isset($encounter->current_turn) && $combatant['order'] == $encounter->current_turn)
                                    border-2 border-yellow-400 transform scale-105 shadow-xl
                                @endif
                            " data-order="{{ $combatant['order'] }}" wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}">
                                <div class="flex-grow">
                                    <span class="font-bold text-xl text-white">{{ $combatant['order'] }}. {{ $combatant['name'] }}</span> {{-- Added order number --}}
                                    <span class="text-sm ml-2 text-gray-400">({{ $combatant['type'] === 'player' ? 'Player' : 'Monster' }})</span>
                                    <div class="text-md mt-1 text-gray-300">
                                        AC: {{ $combatant['ac'] }}
                                    </div>
                                </div>
                                <div class="text-md md:ml-3 mt-2 md:mt-0 text-gray-300">
                                    <span>Init: {{ $combatant['original_model']->initiative_roll ?? ($combatant['original_model']->pivot->initiative_roll ?? 'N/A') }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="p-4 text-gray-400 text-center">No combatants in this encounter yet. Time to add some!</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Encounter Image Area --}}
            <div class="flex-grow w-full lg:w-2/3 flex flex-col self-stretch h-full bg-gray-800 rounded-lg shadow-lg p-4"> {{-- Responsive width, padding, background --}}
                <div class="flex justify-center items-center flex-grow h-full overflow-hidden"> {{-- Added overflow-hidden --}}
                    <img id="encounter-image"
                         src="{{ $imageUrl }}"
                         alt="Encounter Image"
                         class="max-w-full max-h-full object-contain rounded-lg shadow-md"> {{-- Changed to max-width/height for better scaling --}}
                </div>
            </div>
        </div>
    @else
        <p class="text-red-500 text-center text-xl mt-8">Encounter not found. Please check the URL.</p>
    @endif
</div>
<script>
    @if ($encounter)
        window.encounterId = {{ $encounter->id }};
        window.initialCurrentTurn = {{ $encounter->current_turn ?? 0 }};
    @endif
</script>