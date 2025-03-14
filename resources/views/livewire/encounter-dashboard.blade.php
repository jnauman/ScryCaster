<div class="p-4">
        @if ($encounter)
                <h1 class="text-2xl font-bold mb-4">Encounter: {{ $encounter->name }}</h1>
                <p class="text-lg mb-2">Round: {{ $encounter->current_round }}</p>

                <ul class="space-y-2">
                        @foreach ($encounter->characters->sortBy('pivot.order') as $character)
                                <li class="p-3 rounded-lg @if ($character->pivot->order == $encounter->current_turn) bg-blue-100 border border-blue-400 @else bg-gray-100 @endif">
                                        <span class="font-semibold">{{ $character->name }}</span> -
                                        <span class="text-sm">Initiative: {{ $character->pivot->initiative_roll }}</span> -
                                        <span class="text-sm">Order: {{ $character->pivot->order }}</span>
                                </li>
                        @endforeach
                </ul>
        @else
                <p class="text-red-500">Encounter not found.</p>
        @endif
</div>