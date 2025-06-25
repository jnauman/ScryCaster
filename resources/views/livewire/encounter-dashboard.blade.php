<div class="p-4">
    <div id="app">
    </div>
    @if ($encounter)
        <script>
            // This sets the global JavaScript variable that your Vue app needs to subscribe to the correct channel.
            window.encounterId = {{ $encounter->id }};
        </script>
    @endif

@if ($encounter)
        <h1 class="text-2xl font-bold mb-4">Encounter: {{ $encounter->name }}</h1>
        <p class="text-lg mb-2">Round: {{ $encounter->current_round }}</p>

        <div class="flex w-full items-start h-[calc(100vh-200px)]">
            {{-- Combatants List --}}
            <div class="w-[500px] flex-shrink-0 pr-4 overflow-y-auto h-full">
                <div id="encounter-{{ $encounter->id }}-combatants">
                    <ul class="space-y-2">
                        @forelse ($combatants as $combatant)
                            <li class="p-3 rounded-lg flex items-center justify-between transition-all duration-150 ease-in-out
                                {{ $combatant['css_classes'] }}
                            " data-order="{{ $combatant['order'] }}" wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}">
                                <div class="flex-grow">
                                    <span class="font-semibold text-lg">{{ $combatant['name'] }}</span>
                                    <span class="text-xs ml-1">({{ $combatant['type'] === 'player' ? 'Player' : 'Monster' }})</span>
                                    <div class="text-sm">
                                        AC: {{ $combatant['ac'] }}
                                    </div>
                                </div>

                                <div class="text-sm ml-3">
                                    <span>Init: {{ $combatant['original_model']->initiative_roll ?? ($combatant['original_model']->pivot->initiative_roll ?? 'N/A') }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="p-3 text-gray-500">No combatants in this encounter yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Encounter Image Area --}}
            <div class="flex-grow pl-4 flex flex-col self-stretch h-full">
                <div class="flex justify-center items-center flex-grow h-full">
                    {{--<img id="encounter-image" src="/images/placeholder.jpg" alt="Encounter Image" class="max-w-full h-auto rounded-lg shadow-md">--}}
                    <img id="encounter-image"
                         src="{{ $imageUrl }}"
                         alt="Encounter Image"
                         class="w-full h-full object-contain rounded-lg shadow-md">
                </div>
            </div>
        </div>
    @else
        <p class="text-red-500">Encounter not found.</p>
    @endif
</div>
