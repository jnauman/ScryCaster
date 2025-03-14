<x-filament-panels::page>
    <div class="bg-torea-bay-500 p-8 text-center text-4xl font-bold text-torea-bay-900 mb-4">
        TAILWIND TEST
    </div>
    <div class="p-4">
        @if ($record)
            <h1 class="text-2xl font-bold mb-4">Run Encounter: {{ $record->name }}</h1>
            <p class="text-lg mb-2">Round: {{ $record->current_round }}</p>
            seance
            <ul class="space-y-2">
                @foreach ($record->characters->sortBy('pivot.order') as $character)
                    <li class="p-3 rounded-lg @if ($character->pivot->order == $record->current_turn) bg-seance-100 border border-seance-400 @else bg-gray-500 @endif">
                        <span class="font-semibold">{{ $character->name }}</span> -
                        <span class="text-sm">Initiative: {{ $character->pivot->initiative_roll }}</span> -
                        <span class="text-sm">Order: {{ $character->pivot->order }}</span>
                    </li>
                @endforeach
            </ul>

            <button wire:click="nextTurn" class="mt-4 bg-seance-100 hover:bg-seance-700 text-blue font-bold py-2 px-4 rounded">
                Next Turn
            </button>
        @else
            <p class="text-red-500">Encounter not found.</p>
        @endif
    </div>
</x-filament-panels::page>