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
                        @foreach ($participants as $participant)
                            @php
                                $isCurrentTurn = $participant->order == $encounter->current_turn;
                                $cssClasses = '';
                                if ($participant->participant_type === 'player') {
                                    $cssClasses = $participant->getListItemCssClasses($encounter->current_turn);
                                } else { // monster_instance
                                    // Basic styling for monster instances, can be expanded
                                    $cssClasses = $isCurrentTurn ? 'bg-red-400 text-white' : 'bg-red-100';
                                }
                            @endphp
                            <li class="p-3 rounded-lg flex items-center justify-between {{ $cssClasses }}"
                                data-order="{{ $participant->order }}">
                                <div class="flex-grow">
                                    <span class="font-semibold">
                                        @if ($participant->participant_type === 'player')
                                            {{ $participant->name }}
                                        @else
                                            {{ $participant->monster->name }} (Instance)
                                        @endif
                                    </span>
                                    <span class="text-xs">
                                        (HP: {{ $participant->current_health }} /
                                        @if ($participant->participant_type === 'player')
                                            {{ $participant->max_health }}
                                        @else
                                            {{ $participant->monster->max_health }}
                                        @endif
                                        )
                                    </span>
                                </div>
                                <div class="text-sm">
                                    <span>Init: {{ $participant->initiative_roll ?? 'N/A' }}</span>
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
