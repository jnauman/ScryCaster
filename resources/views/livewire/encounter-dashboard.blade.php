<div class="p-4">
    <div id="app">
    </div>
    @if ($encounter)

        <h1 class="text-3xl font-extrabold mt-2 mb-2 text-center text-[var(--color-accent)]">Encounter: {{ $encounter->name }}</h1>
        <p class="text-xl mb-3 text-center text-[var(--color-zinc-600)] dark:text-[var(--color-zinc-400)]">Round: {{ $encounter->current_round }}</p>

        <div class="flex flex-col lg:flex-row w-full items-start lg:h-[calc(100vh-200px)] gap-6">
            {{-- Combatants List --}}
            <div class="w-full lg:w-1/3 flex-shrink-0 lg:pr-4 overflow-y-auto h-96 lg:h-full bg-[var(--color-zinc-100)] dark:bg-[var(--color-zinc-800)] p-4 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold mb-3 text-[var(--color-zinc-800)] dark:text-[var(--color-zinc-200)]">Turn Order</h2>
                <div id="encounter-{{ $encounter->id }}-combatants">
                    <ul class="space-y-2">
                        @forelse ($combatants as $combatant)
                            <li class="p-2 rounded-lg flex items-center gap-3 transition-all duration-150 ease-in-out
                                {{ $combatant['css_classes'] }}
                                @if (isset($encounter->current_turn) && $combatant['order'] == $encounter->current_turn)
                                    border-2 border-[var(--color-turn-indicator)] transform scale-105 shadow-xl
                                @endif
                            " data-order="{{ $combatant['order'] }}" wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}">

                                {{-- Combatant Image --}}
                                <div class="flex-shrink-0">
                                    <img src="{{ $combatant['image'] }}"
                                         alt="{{ $combatant['name'] }}"
                                         class="w-12 h-12 object-cover rounded-full border-2 border-[var(--color-zinc-300)] dark:border-[var(--color-zinc-600)]">
                                </div>

                                <div class="flex-grow">
                                    <span class="font-bold text-xl text-[var(--color-zinc-800)] dark:text-[var(--color-zinc-100)] block">{{ $combatant['name'] }}
                                        @if(!empty($combatant['title']))
                                            - {{ $combatant['title'] }}
                                        @endif
                                    </span>
                                    <span class="text-xs text-[var(--color-zinc-500)] dark:text-[var(--color-zinc-400)]">({{ $combatant['type'] === 'player' ? 'Player' : ($combatant['type'] === 'monster_instance' ? 'Monster' : 'Unknown') }})</span>
                                </div>

                                {{-- Toggle Icon for Monster Instances --}}
                                @if ($combatant['type'] === 'monster_instance')
                                    <div class="ml-auto pr-2">
                                        <button wire:click="toggleMonsterDetail({{ $combatant['id'] }})" class="text-[var(--color-accent)] hover:text-[var(--color-accent-hover)] transition-colors">
                                            @if ($expandedMonsterInstances[$combatant['id']] ?? false)
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            @endif
                                        </button>
                                    </div>
                                @endif
                            </li>

                            {{-- Collapsible Monster Details --}}
                            @if ($combatant['type'] === 'monster_instance' && ($expandedMonsterInstances[$combatant['id']] ?? false))
                            <li class="bg-[var(--color-zinc-50)] dark:bg-[var(--color-zinc-750)] p-3 rounded-b-lg mb-2 shadow-inner" wire:key="monster-detail-{{ $combatant['id'] }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-[var(--color-zinc-700)] dark:text-[var(--color-zinc-300)]">
                                    <div><strong>AC:</strong> {{ $combatant['ac'] ?? 'N/A' }} {{ $combatant['armor_type'] ? '(' . $combatant['armor_type'] . ')' : '' }}</div>
                                    <div><strong>HP:</strong> {{ $combatant['current_health'] ?? 'N/A' }} / {{ $combatant['max_health'] ?? 'N/A' }}</div>
                                    <div><strong>Level:</strong> {{ $combatant['level'] ?? 'N/A' }}</div>
                                    <div><strong>Alignment:</strong> {{ $combatant['alignment'] ?? 'N/A' }}</div>
                                    <div class="md:col-span-2"><strong>Movement:</strong> {{ $combatant['movement'] ?? 'N/A' }}</div>

                                    @if(!empty($combatant['traits']))
                                        <div class="md:col-span-2">
                                            <strong>Traits:</strong>
                                            @if(is_array($combatant['traits']))
                                                {{ implode(', ', $combatant['traits']) }}
                                            @else
                                                {{ $combatant['traits'] }}
                                            @endif
                                        </div>
                                    @endif

                                    <div class="md:col-span-2">
                                        <strong>Stats:</strong>
                                        Str: {{ $combatant['strength'] ?? 'N/A' }} |
                                        Dex: {{ $combatant['dexterity'] ?? 'N/A' }} |
                                        Con: {{ $combatant['constitution'] ?? 'N/A' }} |
                                        Int: {{ $combatant['intelligence'] ?? 'N/A' }} |
                                        Wis: {{ $combatant['wisdom'] ?? 'N/A' }} |
                                        Cha: {{ $combatant['charisma'] ?? 'N/A' }}
                                    </div>

                                    @if(!empty($combatant['description']))
                                        <div class="md:col-span-2 mt-1">
                                            <p class="text-xs italic">{{ $combatant['description'] }}</p>
                                        </div>
                                    @endif

                                    @if (!empty($combatant['attacks']))
                                        <div class="md:col-span-2 mt-1">
                                            <strong>Attacks:</strong>
                                            {{-- Assuming attacks might be a JSON string or array --}}
                                            @if (is_array($combatant['attacks']))
                                                <ul class="list-disc list-inside ml-2">
                                                    @foreach ($combatant['attacks'] as $attackName => $attackDetails)
                                                        <li>
                                                            @if(is_string($attackName) && !is_array($attackDetails))
                                                                {{ $attackName }}: {{ $attackDetails }}
                                                            @elseif(is_string($attackName))
                                                                {{ $attackName }}
                                                                @if(is_array($attackDetails))
                                                                    ({{ isset($attackDetails['to_hit']) ? 'To Hit: '.$attackDetails['to_hit'].', ' : '' }}{{ isset($attackDetails['damage']) ? 'Dmg: '.$attackDetails['damage'] : '' }})
                                                                @endif
                                                            @else
                                                                {{ $attackDetails }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p>{{ $combatant['attacks'] }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </li>
                            @endif


                            {{-- Player Details (original location) --}}
                            @if ($combatant['type'] === 'player')
                            <li class="bg-transparent p-0 -mt-2"> {{-- Adjust list item for player details to keep them visually associated --}}
                                <div class="pl-[calc(0.75rem+48px+0.75rem)] pb-2 text-sm text-[var(--color-zinc-600)] dark:text-[var(--color-zinc-300)]">
                                    Ancestry: <span class="font-semibold">{{ $combatant['ancestry'] ?? 'N/A' }}</span><br>
                                    Class: <span class="font-semibold">{{ $combatant['class'] ?? 'N/A' }}</span>
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
