
<x-filament-panels::page>
    <div id="app">
    </div>

    <div class="p-4">
        @if ($record)
            <h1 class="text-2xl font-bold mb-4">Run Encounter: {{ $record->name }}</h1>
            <p class="text-lg mb-2">Round: {{ $record->current_round }}</p>
            <div id="encounter-{{ $record->id }}">
                <ul class="space-y-2">
                    @foreach ($record->characters->sortBy('pivot.order') as $character)
                        <li class="p-3 rounded-lg @if ($character->pivot->order == $record->current_turn) bg-[var(--color-accent)]  border border-[var(--coloraccent-foreground @else  bg-[var(--color-accent-content)] @endif">
                            <span class="font-semibold">{{ $character->name }}</span> -
                            <span class="text-sm">Initiative: {{ $character->pivot->initiative_roll }}</span> -
                            <span class="text-sm">Order: {{ $character->pivot->order }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <button wire:click="nextTurn" class="mt-4 bg-gray-100 hover:bg-gray-700 text-blue font-bold py-2 px-4 rounded">
                Next Turn
            </button>
        @else
            <p class="text-red-500">Encounter not found.</p>
        @endif
    </div>
   {{-- @vite(['resources/css/app.css', 'resources/js/app.js'])--}}
@vite('resources/css/app.css')
<script>
	window.encounterId = {{ $record->id }};
	window.initialCurrentTurn = {{ $record->current_turn }};
</script>
</x-filament-panels::page>

