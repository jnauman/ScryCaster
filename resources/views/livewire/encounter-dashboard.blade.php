<div class="p-4">
    <div id="app">
    </div>
    @if ($encounter)
        <h1 class="text-2xl font-bold mb-4">Encounter: {{ $encounter->name }}</h1>
        <p class="text-lg mb-2">Round: {{ $encounter->current_round }}</p>

        <div class="flex w-full items-start h-[calc(100vh-200px)]">
            <div class="w-[500px] flex-shrink-0 pr-4 overflow-y-auto">
                <div id="encounter-{{ $encounter->id }}">
                    <ul class="space-y-2">
                        @foreach ($encounter->characters->sortBy('pivot.order') as $character)
                            <li class="p-3 rounded-lg flex items-center justify-between
                                @if ($character->type == 'monster' && $character->pivot->order != $encounter->current_turn)
                                    monster-not-turn
                                @elseif ($character->type == 'monster' && $character->pivot->order == $encounter->current_turn)
                                    monster-current-turn
                                @elseif ($character->type == 'player' && $character->pivot->order != $encounter->current_turn)
                                    player-not-turn
                                @else
                                    player-current-turn
                                @endif
                            " data-order="{{ $character->pivot->order }}">
                                <div class="flex-grow">
                                    <span class="font-semibold">{{ $character->name }}</span>
                                </div>
                                <div class="text-sm">
                                    <span>Init: {{ $character->pivot->initiative_roll }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="flex-grow pl-4 flex flex-col self-stretch">
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
<script>
    window.encounterId = {{ $encounter->id }};
    window.initialCurrentTurn = {{ $encounter->current_turn }};
</script>
