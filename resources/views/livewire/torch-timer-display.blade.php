<div class="p-3 bg-gray-800 bg-opacity-70 rounded-lg shadow-md text-center">
    @if ($duration === null || $remaining === null)
        <p class="text-sm text-gray-400">Torch status not available.</p>
    @else
        <h4 class="text-md font-semibold mb-1 text-yellow-400">Torch Light</h4>
        <p class="text-3xl font-bold
            @if ($isRunning && $remaining !== null && $remaining <= ($duration * 0.1)) text-red-500 @elseif ($isRunning) text-green-400 @else text-gray-400 @endif">
            {{ gmdate("H:i:s", $remaining * 60) }}
        </p>
        <p class="text-xs text-gray-500">
            @if ($isRunning)
                Burning
                @if ($remaining !== null && $remaining <= ($duration * 0.25) && $remaining > ($duration * 0.1))
                    (Getting Low)
                @elseif ($remaining !== null && $remaining <= ($duration * 0.1) && $remaining > 0)
                    (Flickering Weakly!)
                @elseif ($remaining === 0)
                    (Burnt Out!)
                @endif
            @else
                @if ($remaining === 0 && $duration > 0)
                    (Burnt Out)
                @elseif ($remaining > 0 && $remaining < $duration)
                    (Paused)
                @else
                    (Not Lit)
                @endif
            @endif
        </p>
        {{-- Optional: Progress Bar --}}
        @if ($duration > 0)
        <div class="w-full bg-gray-600 rounded-full h-2.5 mt-2">
            @php
                $percentage = $duration > 0 ? ($remaining / $duration) * 100 : 0;
                $barColor = 'bg-gray-400'; // Default for not running or paused
                if ($isRunning) {
                    if ($percentage <= 10) $barColor = 'bg-red-500';
                    elseif ($percentage <= 25) $barColor = 'bg-yellow-500';
                    else $barColor = 'bg-green-400';
                } elseif ($remaining === 0) {
                    $barColor = 'bg-red-700'; // Burnt out
                }
            @endphp
            <div class="{{ $barColor }} h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
        </div>
        @endif
    @endif
</div>
