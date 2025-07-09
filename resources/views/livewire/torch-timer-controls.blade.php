<div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">Torch Timer</h3>

    <div class="mb-4">
        <label for="torch_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Set Torch Duration (minutes)</label>
        <input id="torch_duration" type="number" wire:model.lazy="duration" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
        @error('duration') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div class="text-center mb-4" x-data="torchTimerControls_{{ $encounter->id }}({{ $remaining ?? 0 }}, {{ $isRunning ? 'true' : 'false' }}, {{ $duration ?? 0 }})" x-init="initTimer()">
        <p class="text-5xl font-bold text-gray-900 dark:text-gray-100" x-text="timeFormatted()">
            {{ gmdate("H:i:s", ($remaining ?? 0) * 60) }}
        </p>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Remaining Time (out of <span x-text="durationFormated()">{{ gmdate("H:i:s", ($duration ?? 0) * 60) }}</span>)
        </p>
    </div>

    <div class="grid grid-cols-2 gap-2 mb-4">
        <button wire:click="startPauseTimer"
                class="px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white
                       {{ $isRunning ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }}
                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ $isRunning ? 'Pause' : 'Start' }} Timer
        </button>
        <button wire:click="resetTimer"
                class="px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white bg-red-500 hover:bg-red-600
                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            Reset Timer
        </button>
    </div>

    <div class="grid grid-cols-2 gap-2">
        <div class="flex rounded-md shadow-sm">
            <button wire:click="subtractTime(15)"
                    class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-l-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                -15m
            </button>
            <button wire:click="addTime(15)"
                    class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 border-l border-gray-300 dark:border-gray-500">
                +15m
            </button>
        </div>
        <div class="flex rounded-md shadow-sm">
            <button wire:click="subtractTime(5)"
                    class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-l-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                -5m
            </button>
            <button wire:click="addTime(5)"
                    class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 border-l border-gray-300 dark:border-gray-500">
                +5m
            </button>
        </div>
    </div>
    {{-- Placeholder for server-side timer updates --}}
    {{-- <div wire:poll.1000ms="decrementTimeOnServer" style="display:none;"></div> --}}

    @push('scripts')
    <script>
        function torchTimerControls_{{ $encounter->id }}(initialRemainingMinutes, initialIsRunning, initialDurationMinutes) {
            return {
                remainingSeconds: Math.max(0, initialRemainingMinutes * 60),
                isRunning: initialIsRunning,
                durationSeconds: Math.max(0, initialDurationMinutes * 60),
                interval: null,

                initTimer() {
                    if (this.isRunning && this.remainingSeconds > 0) {
                        this.startClientTimer();
                    }

                    this.$wire.on('torchTimerExternalUpdate', (eventData) => {
                        console.log('Controls received torchTimerExternalUpdate:', eventData);
                        this.durationSeconds = Math.max(0, eventData.duration * 60);
                        this.remainingSeconds = Math.max(0, eventData.remaining * 60);
                        this.isRunning = eventData.isRunning;
                        if (this.isRunning && this.remainingSeconds > 0) {
                            this.startClientTimer();
                        } else {
                            this.stopClientTimer();
                        }
                    });

                    // Sync with server state when tab becomes visible, in case of drift
                    // or if backend updates happened while tab was inactive.
                    document.addEventListener("visibilitychange", () => {
                        if (!document.hidden) {
                            this.$wire.call('syncState');
                        }
                    });
                },

                startClientTimer() {
                    if (this.interval) clearInterval(this.interval);
                    if (!this.isRunning || this.remainingSeconds <= 0) return;

                    this.interval = setInterval(() => {
                        if (this.remainingSeconds > 0) {
                            this.remainingSeconds--;
                        } else {
                            this.isRunning = false; // Visually stop
                            this.stopClientTimer();
                            // Optionally, notify Livewire component that timer reached zero client-side
                            // this.$wire.call('clientTimerReachedZero');
                        }
                    }, 1000);
                },

                stopClientTimer() {
                    if (this.interval) {
                        clearInterval(this.interval);
                        this.interval = null;
                    }
                },

                timeFormatted() {
                    if (this.remainingSeconds === null) return '00:00:00';
                    const hours = Math.floor(this.remainingSeconds / 3600);
                    const minutes = Math.floor((this.remainingSeconds % 3600) / 60);
                    const seconds = this.remainingSeconds % 60;
                    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },
                durationFormated() {
                    if (this.durationSeconds === null) return '00:00:00';
                    const hours = Math.floor(this.durationSeconds / 3600);
                    const minutes = Math.floor((this.durationSeconds % 3600) / 60);
                    const seconds = this.durationSeconds % 60;
                    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },

                // Watch for changes from Livewire component's state (e.g., after a button click)
                // This is implicitly handled by Livewire updating the bound initial values when the component re-renders.
                // However, direct calls from Livewire might be more robust.
                // We will use a Livewire event for this.
            }
        }
    </script>
    @endpush
</div>
