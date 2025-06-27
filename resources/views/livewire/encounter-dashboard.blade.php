<div class="p-4">
    <div id="app">
    </div>
    @if ($encounter)

        <h1 class="text-3xl font-extrabold mt-2 mb-2 text-center text-blue-400">Encounter: {{ $encounter->name }}</h1>
        <p class="text-xl mb-3 text-center text-gray-300">Round: {{ $encounter->current_round }}</p>

        <div class="flex flex-col lg:flex-row w-full items-start lg:h-[calc(100vh-200px)] gap-6">
            {{-- Combatants List --}}
            <div class="w-full lg:w-1/3 flex-shrink-0 lg:pr-4 overflow-y-auto h-96 lg:h-full bg-gray-800 p-4 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-3 text-white">Turn Order</h2>
                <div id="encounter-{{ $encounter->id }}-combatants">
                    <ul class="space-y-2">
                        @forelse ($combatants as $combatant)
                            <li class="p-2 rounded-lg flex items-center gap-3 transition-all duration-150 ease-in-out
                                {{ $combatant['css_classes'] }}
                                @if (isset($encounter->current_turn) && $combatant['order'] == $encounter->current_turn)
                                    border-2 border-yellow-400 transform scale-105 shadow-xl
                                @endif
                            " data-order="{{ $combatant['order'] }}" wire:key="combatant-{{ $combatant['type'] }}-{{ $combatant['id'] }}">

                                {{-- Combatant Image --}}
                                <div class="flex-shrink-0">
                                    {{-- Corrected logo path and same size constraints --}}
                                    <img src="{{ $combatant['image'] }}"
                                         alt="{{ $combatant['name'] }}"
                                         class="w-12 h-12 object-cover rounded-full border-2 border-gray-600">
                                </div>

                                <div class="flex-grow">
                                    <span class="font-bold text-xl text-white block">{{ $combatant['name'] }}
                                        @if(!empty($combatant['title']))
                                            - {{ $combatant['title'] }}
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-400">({{ $combatant['type'] === 'player' ? 'Player' : 'Monster' }})</span>

                                    @if ($combatant['type'] === 'player')
                                        <div class="text-sm mt-1 text-gray-300">
                                            Ancestry: <span class="font-semibold">{{ $combatant['ancestry'] ?? 'N/A' }}</span><br>
                                            Class: <span class="font-semibold">{{ $combatant['class'] ?? 'N/A' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="p-4 text-gray-400 text-center">No combatants in this encounter yet. Time to add some!</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Encounter Image Area --}}
            <div class="flex-grow w-full lg:w-2/3 flex flex-col self-stretch h-full bg-gray-800 rounded-lg shadow-lg p-4">
                <div class="flex justify-center items-center flex-grow h-full overflow-hidden">
                    <img id="encounter-image"
                         src="{{ $imageUrl }}"
                         alt="Encounter Image"
                         class="max-w-full max-h-full object-contain rounded-lg shadow-md">
                </div>
            </div>
        </div>
    @else
        <p class="text-red-500 text-center text-xl mt-8">Encounter not found. Please check the URL.</p>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        const combatantList = document.getElementById('encounter-{{ $encounter->id }}-combatants');

        if (combatantList) {
            const observer = new MutationObserver(mutations => {
                for (let mutation of mutations) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const targetElement = mutation.target;
                        // Check if the element is the current turn by looking for a specific class
                        // Using 'border-yellow-400' as it's uniquely applied to the current turn.
                        if (targetElement.classList.contains('border-yellow-400')) {
                            targetElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            break; // Found the target, no need to check other mutations
                        }
                    } else if (mutation.type === 'childList') {
                        // If children are added/removed, check for the current turn item among the new/existing children
                        const currentTurnElement = combatantList.querySelector('.border-yellow-400');
                        if (currentTurnElement) {
                            currentTurnElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    }
                }
            });

            observer.observe(combatantList, {
                childList: true, // Watch for direct children being added or removed
                subtree: true,   // Watch for changes in all descendants of combatantList
                attributes: true, // Watch for attribute changes
                attributeFilter: ['class'] // Specifically watch for changes to the 'class' attribute
            });

            // Initial scroll to current turn on page load / component load
            const initialCurrentTurnElement = combatantList.querySelector('.border-yellow-400');
            if (initialCurrentTurnElement) {
                // A slight delay can sometimes help ensure the layout is fully stable
                setTimeout(() => {
                    initialCurrentTurnElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            }
        }
    });
</script>
@endpush
