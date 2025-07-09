<?php

namespace App\Livewire;

use App\Models\Encounter;
use Livewire\Component;
use App\Events\TorchTimerUpdated;
use Illuminate\Support\Facades\Log;

class TorchTimerControls extends Component
{
    public Encounter $encounter;
    public $duration; // Corresponds to torch_timer_duration
    public $remaining; // Corresponds to torch_timer_remaining
    public bool $isRunning = false; // To track if the timer is active

    protected $rules = [
        'duration' => 'nullable|integer|min:0',
        'remaining' => 'nullable|integer|min:0',
    ];

    public function mount(Encounter $encounter): void
    {
        $this->encounter = $encounter;
        $this->duration = $this->encounter->torch_timer_duration ?? 60; // Default to 60 minutes
        $this->remaining = $this->encounter->torch_timer_remaining ?? $this->duration;
        $this->isRunning = $this->encounter->torch_timer_is_running ?? false;
        Log::info("TorchTimerControls mounted for Encounter ID {$this->encounter->id}: Duration={$this->duration}, Remaining={$this->remaining}, IsRunning={$this->isRunning}");
    }

    public function updatedDuration($value): void
    {
        $this->validateOnly('duration');
        $this->encounter->torch_timer_duration = $value;
        // If duration changes and remaining is greater, or if timer is not set, reset remaining and stop timer.
        if ($this->remaining === null || $this->remaining > $value || $value == 0) {
            $this->remaining = $value > 0 ? $value : 0; // Set remaining to new duration or 0
            $this->isRunning = false; // Stop the timer
            $this->encounter->torch_timer_remaining = $this->remaining;
            $this->encounter->torch_timer_is_running = $this->isRunning;
        }
        $this->encounter->save();
        $this->broadcastUpdate();
    }

    public function updatedRemaining($value): void
    {
        $this->validateOnly('remaining');
        $this->encounter->torch_timer_remaining = $value;
        $this->encounter->save();
        $this->broadcastUpdate();
    }

    public function startPauseTimer(): void
    {
        $this->isRunning = !$this->isRunning;
        $this->encounter->torch_timer_is_running = $this->isRunning;

        if ($this->isRunning && ($this->remaining === 0 || $this->remaining === null) && $this->duration > 0) {
            $this->remaining = $this->duration; // Auto-restart if timer was at 0 or not set
            $this->encounter->torch_timer_remaining = $this->remaining;
        }
        $this->encounter->save();
        $this->broadcastUpdate();
    }

    public function resetTimer(): void
    {
        $this->isRunning = false;
        $this->remaining = $this->duration;
        $this->encounter->torch_timer_is_running = $this->isRunning;
        $this->encounter->torch_timer_remaining = $this->remaining;
        $this->encounter->save();
        $this->broadcastUpdate();
    }

    public function addTime(int $minutes): void
    {
        $this->remaining = max(0, (int)$this->remaining + $minutes);
        $this->encounter->torch_timer_remaining = $this->remaining;
        $this->encounter->save();
        $this->broadcastUpdate();
    }

    public function subtractTime(int $minutes): void
    {
        $this->remaining = max(0, (int)$this->remaining - $minutes);
        $this->encounter->torch_timer_remaining = $this->remaining;
        $this->encounter->save();
        $this->broadcastUpdate();
    }

    private function broadcastUpdate(): void
    {
        $payload = [
            'encounterId' => $this->encounter->id,
            'remaining' => (int)$this->remaining,
            'duration' => (int)$this->duration,
            'isRunning' => (bool)$this->isRunning,
        ];

        Log::info("TorchTimerControls broadcasting update for Encounter ID {$this->encounter->id}: " . json_encode($payload));
        try {
            event(new TorchTimerUpdated(
                $payload['encounterId'],
                $payload['remaining'],
                $payload['duration'],
                $payload['isRunning']
            ));
            Log::info("TorchTimerControls event broadcasted successfully for Encounter ID {$this->encounter->id}.");

            // Dispatch event for AlpineJS component on this client
            $this->dispatch('torchTimerExternalUpdate', $payload);
            Log::info("TorchTimerControls dispatched torchTimerExternalUpdate for self: " . json_encode($payload));

        } catch (\Exception $e) {
            Log::error("TorchTimerControls failed to broadcast event for Encounter ID {$this->encounter->id}: " . $e->getMessage());
        }
    }

    public function syncState(): void
    {
        $this->encounter->refresh();
        $this->duration = $this->encounter->torch_timer_duration ?? 60;
        $this->remaining = $this->encounter->torch_timer_remaining ?? $this->duration;
        $this->isRunning = $this->encounter->torch_timer_is_running ?? false;
        Log::info("TorchTimerControls state synced for Encounter ID {$this->encounter->id}: Duration={$this->duration}, Remaining={$this->remaining}, IsRunning={$this->isRunning}");

        $payload = [
            'encounterId' => $this->encounter->id,
            'remaining' => (int)$this->remaining,
            'duration' => (int)$this->duration,
            'isRunning' => (bool)$this->isRunning,
        ];
        $this->dispatch('torchTimerExternalUpdate', $payload);
        Log::info("TorchTimerControls dispatched torchTimerExternalUpdate after sync: " . json_encode($payload));
    }

    public function render()
    {
        return view('livewire.torch-timer-controls');
    }
}
