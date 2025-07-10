<div class="p-4">
    <div id="app">
    </div>
    @if ($encounter)

        <h1 class="text-3xl font-extrabold mt-2 mb-2 text-center text-[var(--color-accent)]">Encounter: {{ $encounter->name }}</h1>
        <p class="text-xl mb-1 text-center text-[var(--color-zinc-600)] dark:text-[var(--color-zinc-400)]">Round: {{ $encounter->current_round }}</p>

        {{-- Torch Timer Display --}}
        <div class="my-3 max-w-xs mx-auto">
            @livewire('torch-timer-display', ['encounter' => $encounter])
        </div>

        <div class="flex flex-col lg:flex-row w-full items-start lg:h-[calc(100vh-200px)] gap-6">
            {{-- Combatants List --}}
            <div class="w-full lg:w-1/3 flex-shrink-0 lg:pr-4 overflow-y-auto h-96 lg:h-full bg-[var(--color-zinc-100)] dark:bg-[var(--color-zinc-800)] p-4 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold mb-3 text-[var(--color-zinc-800)] dark:text-[var(--color-zinc-200)]">Turn Order</h2>
                <div id="encounter-{{ $encounter->id }}-combatants">
                    <ul class="space-y-2">
                        @forelse ($combatants as $groupOrIndividualIndex => $groupOrIndividual)
                            @if ($groupOrIndividual['type'] === 'group')
                                <li class="rounded-lg overflow-hidden shadow-sm"
                                    style="border-left: 4px solid {{ $groupOrIndividual['group_css_classes'] ?: 'transparent' }}; background-color: rgba(0,0,0,0.05);"
                                    wire:key="group-{{ $groupOrIndividual['name'] }}-{{ $groupOrIndividualIndex }}">
                                    <h3 class="text-sm font-semibold mb-1 px-2 pt-1 text-[var(--color-zinc-700)] dark:text-[var(--color-zinc-300)]">
                                        Group: {{ $groupOrIndividual['name'] }}
                                    </h3>
                                    <ul class="space-y-1 px-1 pb-1"> {{-- Inner list for combatants within the group --}}
                                        @foreach ($groupOrIndividual['combatants'] as $combatantIndex => $combatant)
                                            {{-- Individual Combatant LI (Player View) --}}
                                            <li class="p-2 rounded-md flex items-center gap-3 transition-all duration-150 ease-in-out
                                                {{ $combatant['css_classes'] }}
                                                @if (isset($encounter->current_turn) && $combatant['order'] == $encounter->current_turn)
                                                    border-2 border-[var(--color-turn-indicator)] transform scale-102 shadow-lg
                                                @else
                                                    border-2 border-transparent
                                                @endif
                                            " data-order="{{ $combatant['order'] }}" wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}-group-{{ $groupOrIndividual['name'] }}">
                                                <div class="flex-shrink-0">
                                                    <img src="{{ $combatant['image'] }}" alt="{{ $combatant['name'] }}" class="w-10 h-10 object-cover rounded-full border border-[var(--color-zinc-300)] dark:border-[var(--color-zinc-600)]">
                                                </div>
                                                <div class="flex-grow">
                                                    <span class="font-semibold text-lg text-[var(--color-zinc-800)] dark:text-[var(--color-zinc-100)] block">
                                                        {{ $combatant['name'] }}
                                                        @if(!empty($combatant['title'])) - {{ $combatant['title'] }}@endif
                                                    </span>
                                                    <span class="text-xs text-[var(--color-zinc-500)] dark:text-[var(--color-zinc-400)]">
                                                        ({{ $combatant['type'] === 'player' ? 'Player' : 'Monster' }})
                                                        {{-- Group name already indicated by the group wrapper --}}
                                                    </span>
                                                    @if ($combatant['type'] === 'player')
                                                        <div class="text-xs mt-0.5 text-[var(--color-zinc-600)] dark:text-[var(--color-zinc-300)]">
                                                            Ancestry: <span class="font-medium">{{ $combatant['ancestry'] ?? 'N/A' }}</span> / Class: <span class="font-medium">{{ $combatant['class'] ?? 'N/A' }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @else {{-- Item is an Individual Player or Monster --}}
                                @php
                                    $combatant = $groupOrIndividual['combatants'][0]; // Get the single combatant
                                @endphp
                                {{-- Individual Combatant LI (Player View) --}}
                                <li class="p-2 rounded-lg flex items-center gap-3 transition-all duration-150 ease-in-out
                                    {{ $combatant['css_classes'] }}
                                    @if (isset($encounter->current_turn) && $combatant['order'] == $encounter->current_turn)
                                        border-2 border-[var(--color-turn-indicator)] transform scale-105 shadow-xl
                                    @else
                                        border-2 border-transparent
                                    @endif
                                " data-order="{{ $combatant['order'] }}" wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}-individual">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $combatant['image'] }}" alt="{{ $combatant['name'] }}" class="w-12 h-12 object-cover rounded-full border-2 border-[var(--color-zinc-300)] dark:border-[var(--color-zinc-600)]">
                                    </div>
                                    <div class="flex-grow">
                                        <span class="font-bold text-xl text-[var(--color-zinc-800)] dark:text-[var(--color-zinc-100)] block">
                                            {{ $combatant['name'] }}
                                            @if(!empty($combatant['title'])) - {{ $combatant['title'] }}@endif
                                        </span>
                                        <span class="text-xs text-[var(--color-zinc-500)] dark:text-[var(--color-zinc-400)]">
                                            ({{ $combatant['type'] === 'player' ? 'Player' : 'Monster' }})
                                            {{-- No group name needed for ungrouped individuals --}}
                                        </span>
                                        @if ($combatant['type'] === 'player')
                                            <div class="text-sm mt-1 text-[var(--color-zinc-600)] dark:text-[var(--color-zinc-300)]">
                                                Ancestry: <span class="font-semibold">{{ $combatant['ancestry'] ?? 'N/A' }}</span><br>
                                                Class: <span class="font-semibold">{{ $combatant['class'] ?? 'N/A' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endif
                        @empty
                            <li class="p-4 text-[var(--color-zinc-500)] dark:text-[var(--color-zinc-400)] text-center">No combatants in this encounter yet. Time to add some!</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Encounter Image Area --}}
            <div class="flex-grow w-full lg:w-2/3 flex flex-col self-stretch h-full bg-[var(--color-zinc-100)] dark:bg-[var(--color-zinc-800)] rounded-lg shadow-md p-4">
                <div class="flex justify-center items-center flex-grow h-full overflow-hidden">
                    <img id="encounter-image"
                         src="{{ $imageUrl }}"
                         alt="Encounter Image"
                         class="max-w-full max-h-full object-contain rounded-lg"> {{-- Removed shadow-md from here as parent has it --}}
                </div>
            </div>
        </div>
    @else
        <p class="text-red-500 text-center text-xl mt-8">Encounter not found. Please check the URL.</p>
    @endif
</div>

@push('scripts')
<script>
    // Scrolling logic removed as current turn combatant is now always at the top
    // for the GM view (RunEncounter.php).
    // This specific view (encounter-dashboard.blade.php) is likely for player view
    // and might have its own scrolling needs if the list is long, but the original
    // request was about the GM view (scrycaster.app/admin/encounters/3/run)
    // and re-ordering combatants there.
    // If this dashboard also needs specific scrolling, it would be a separate consideration.
</script>
@endpush
