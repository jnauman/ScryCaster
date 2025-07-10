<div x-data="torchTimerDisplay_{{ $encounterId }}({{ (int)($remaining ?? 0) }}, {{ $isRunning ? 'true' : 'false' }}, {{ (int)($duration ?? 0) }})"
     x-init="initTimer()"
     wire:ignore.self
     class="p-3 bg-gray-800 bg-opacity-70 rounded-lg shadow-md text-center">

    <template x-if="durationSeconds === null || remainingSeconds === null">
        <p class="text-sm text-gray-400">Torch status not available.</p>
    </template>

    <template x-if="durationSeconds !== null && remainingSeconds !== null">
        <div>
            <h4 class="text-md font-semibold mb-1 text-yellow-400">Torch Light</h4>
            <p class="text-3xl font-bold"
               :class="{
                   'text-red-500': isRunning && remainingSeconds !== null && durationSeconds !== null && remainingSeconds <= (durationSeconds * 0.1) && remainingSeconds > 0,
                   'text-red-700': isRunning && remainingSeconds === 0, // More distinct for burnt out while 'running'
                   'text-green-400': isRunning && remainingSeconds !== null && durationSeconds !== null && remainingSeconds > (durationSeconds * 0.1),
                   'text-gray-400': !isRunning
               }"
               x-text="timeFormatted()">
            </p>
            <p class="text-xs text-gray-500">
                <span x-show="isRunning">
                    Burning
                    <span x-show="remainingSeconds <= (durationSeconds * 0.25) && remainingSeconds > (durationSeconds * 0.1)">(Getting Low)</span>
                    <span x-show="remainingSeconds <= (durationSeconds * 0.1) && remainingSeconds > 0">(Flickering Weakly!)</span>
                    <span x-show="remainingSeconds === 0">(Burnt Out!)</span>
                </span>
                <span x-show="!isRunning">
                    <span x-show="remainingSeconds === 0 && durationSeconds > 0">(Burnt Out)</span>
                    <span x-show="remainingSeconds > 0 && remainingSeconds < durationSeconds">(Paused)</span>
                    <span x-show="remainingSeconds >= durationSeconds && durationSeconds > 0">(Not Lit)</span>
                     <span x-show="durationSeconds === 0 && remainingSeconds === 0">(Not Set)</span>
                </span>
            </p>

            <template x-if="durationSeconds > 0">
                <div class="w-full bg-gray-600 rounded-full h-2.5 mt-2">
                    <div class="h-2.5 rounded-full"
                         :class="{
                             'bg-red-700': remainingSeconds === 0, /* Burnt out */
                             'bg-red-500': isRunning && percentage() <= 10 && remainingSeconds > 0,
                             'bg-yellow-500': isRunning && percentage() > 10 && percentage() <= 25,
                             'bg-green-400': isRunning && percentage() > 25,
                             'bg-gray-400': !isRunning && remainingSeconds > 0 /* Paused or Not Lit */
                         }"
                         :style="`width: ${percentage()}%`">
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>

<script>
    function torchTimerDisplay_{{ $encounterId }}(initialRemaining, initialIsRunning, initialDuration) {
        return {
            // Note: Your initial values from PHP are already integers, no need for multiplication here
            // if you pass them as seconds. But your current PHP passes minutes, so we'll stick to that.
            // For clarity, I've renamed the parameters to show they are minutes.
            remainingSeconds: initialRemaining !== null ? Math.max(0, initialRemaining * 60) : null,
            isRunning: initialIsRunning,
            durationSeconds: initialDuration !== null ? Math.max(0, initialDuration * 60) : null,
            interval: null,

            initTimer() {
                if (this.isRunning && this.remainingSeconds !== null && this.remainingSeconds > 0) {
                    this.startClientTimer();
                }

                // Listen for Livewire event
                Livewire.on('torchTimerUpdatedEcho-{{ $encounterId }}', (event) => {
                    // *** THIS IS THE FIX ***
                    // Access the payload from event.detail[0]

                    const payload = event[0];

                    console.log('Display {{ $encounterId }} received torchTimerUpdatedEcho:', payload);

                    // Now use the payload object
                    this.durationSeconds = payload.duration !== null ? Math.max(0, payload.duration * 60) : null;
                    this.remainingSeconds = payload.remaining !== null ? Math.max(0, payload.remaining * 60) : null;
                    this.isRunning = payload.isRunning;

                    if (this.isRunning && this.remainingSeconds !== null && this.remainingSeconds > 0) {
                        this.startClientTimer();
                    } else {
                        this.stopClientTimer();
                        if (this.remainingSeconds === 0) {
                            this.isRunning = false;
                        }
                    }
                });
            },

            startClientTimer() {
                if (this.interval) clearInterval(this.interval);
                if (!this.isRunning || this.remainingSeconds === null || this.remainingSeconds <= 0) {
                    this.stopClientTimer();
                    return;
                }

                this.interval = setInterval(() => {
                    if (this.remainingSeconds !== null && this.remainingSeconds > 0) {
                        this.remainingSeconds--;
                    } else {
                        this.isRunning = false;
                        this.stopClientTimer();
                    }
                }, 1000);
            },

            stopClientTimer() {
                if (this.interval) {
                    clearInterval(this.interval);
                    this.interval = null;
                }
            },

            formatSecondsToHms(totalSeconds) {
                if (isNaN(totalSeconds) || totalSeconds === null || totalSeconds < 0) {
                    return '00:00:00';
                }
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            },

            timeFormatted() {
                return this.formatSecondsToHms(this.remainingSeconds);
            },

            percentage() {
                if (this.durationSeconds === null || this.remainingSeconds === null || this.durationSeconds === 0) return 0;
                return Math.max(0, Math.min(100, (this.remainingSeconds / this.durationSeconds) * 100));
            }
        }
    }
</script>
