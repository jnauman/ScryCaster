<div class="p-4">
    <div id="app">
    </div>
    @if ($encounter)
        <h1 class="text-2xl font-bold mb-4">Encounter: {{ $encounter->name }}</h1>
        <p class="text-lg mb-2">Round: {{ $encounter->current_round }}</p>

        <div class="flex">
            <div class="w-1/2 pr-4"> <div id="encounter-{{ $encounter->id }}">
                    <ul class="space-y-2">
                        @foreach ($encounter->characters->sortBy('pivot.order') as $character)
                            <li class="p-3 rounded-lg flex items-center justify-between @if ($character->pivot->order == $encounter->current_turn) bg-[var(--color-accent)] border border-[var(--color-accent-foreground)] text-[var(--color-accent-foreground)] @else bg-[var(--color-accent-content)] @endif" data-order="{{ $character->pivot->order }}">
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

            <div class="w-1/2 pl-4"> <div class="flex justify-center">
                    <img id="encounter-image" src="/images/placeholder.jpg" alt="Encounter Image" class="max-w-full h-auto rounded-lg shadow-md">
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
