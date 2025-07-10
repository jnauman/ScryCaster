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
        $this->duration = (int)($this->encounter->torch_timer_duration ?? 60); // Default to 60 minutes
        $this->remaining = (int)($this->encounter->torch_timer_remaining ?? $this->duration);
        // Ensure remaining does not exceed duration if both are set from DB
        if ($this->encounter->torch_timer_duration !== null && $this->encounter->torch_timer_remaining !== null && $this->remaining > $this->duration) {
            $this->remaining = $this->duration;
        }
        $this->isRunning = (bool)($this->encounter->torch_timer_is_running ?? false);
        Log::info("TorchTimerControls mounted for Encounter ID {$this->encounter->id}: Duration={$this->duration}, Remaining={$this->remaining}, IsRunning={$this->isRunning}");
    }

    public function updatedDuration($value): void
    {
        $this->validateOnly('duration');
        $newDuration = is_numeric($value) ? (int)$value : 0;
        $this->duration = $newDuration; // Update Livewire property
        $this->encounter->torch_timer_duration = $newDuration;

        if ($this->remaining === null || $this->remaining > $newDuration || $newDuration == 0) {
            $this->remaining = $newDuration > 0 ? $newDuration : 0;
            $this->isRunning = false;
            $this->encounter->torch_timer_remaining = $this->remaining;
            $this->encounter->torch_timer_is_running = $this->isRunning;
        }
        // Ensure Livewire properties are also updated if changed indirectly
        $this->duration = (int)$this->encounter->torch_timer_duration;
        $this->remaining = (int)$this->encounter->torch_timer_remaining;
        $this->isRunning = (bool)$this->encounter->torch_timer_is_running;

        $this->encounter->save();
        Log::info("updatedDuration saved Encounter ID {$this->encounter->id}: Duration={$this->encounter->torch_timer_duration}, Remaining={$this->encounter->torch_timer_remaining}, IsRunning={$this->encounter->torch_timer_is_running}");
        $this->broadcastUpdate();
    }

    public function updatedRemaining($value): void
    {
        // This is typically called if wire:model.lazy="remaining" is used directly,
        // but manual adjustment methods (addTime, subtractTime) are preferred for better control.
        $this->validateOnly('remaining');
        $this->remaining = is_numeric($value) ? (int)$value : 0;
        $this->encounter->torch_timer_remaining = $this->remaining;
        $this->encounter->save();
        Log::info("updatedRemaining saved Encounter ID {$this->encounter->id}: Remaining={$this->encounter->torch_timer_remaining}");
        $this->broadcastUpdate();
    }

    public function startPauseTimer(): void
    {
        $this->isRunning = !$this->isRunning;
        $this->encounter->torch_timer_is_running = $this->isRunning;

        if ($this->isRunning && ((int)$this->remaining === 0 || $this->remaining === null) && (int)$this->duration > 0) {
            $this->remaining = (int)$this->duration;
            $this->encounter->torch_timer_remaining = $this->remaining;
        }

        // Ensure Livewire properties are correctly typed before save and broadcast
        $this->duration = (int)$this->encounter->torch_timer_duration; // Could have been null
        $this->remaining = (int)$this->remaining; // Ensure it's an int
        $this->isRunning = (bool)$this->encounter->torch_timer_is_running;


        $this->encounter->save();
        Log::info("startPauseTimer saved Encounter ID {$this->encounter->id}: Duration={$this->encounter->torch_timer_duration}, Remaining={$this->encounter->torch_timer_remaining}, IsRunning={$this->encounter->torch_timer_is_running}");
        $this->broadcastUpdate();
    }

    public function resetTimer(): void
    {
        $this->isRunning = false;
        $this->duration = (int)($this->encounter->torch_timer_duration ?? 60); // Ensure duration is int
        $this->remaining = $this->duration;

        $this->encounter->torch_timer_is_running = $this->isRunning;
        $this->encounter->torch_timer_remaining = $this->remaining;
        $this->encounter->torch_timer_duration = $this->duration; // Persist if it was null

        $this->encounter->save();
        Log::info("resetTimer saved Encounter ID {$this->encounter->id}: Duration={$this->encounter->torch_timer_duration}, Remaining={$this->encounter->torch_timer_remaining}, IsRunning={$this->encounter->torch_timer_is_running}");
        $this->broadcastUpdate();
    }

    public function addTime(int $minutes): void
    {
        $this->remaining = max(0, (int)$this->remaining + $minutes);
        $this->encounter->torch_timer_remaining = $this->remaining;

        // Ensure other props are correctly typed for broadcast
        $this->duration = (int)($this->encounter->torch_timer_duration ?? 0);
        $this->isRunning = (bool)$this->encounter->torch_timer_is_running;

        $this->encounter->save();
        Log::info("addTime saved Encounter ID {$this->encounter->id}: Remaining={$this->encounter->torch_timer_remaining}");
        $this->broadcastUpdate();
    }

    public function subtractTime(int $minutes): void
    {
        $this->remaining = max(0, (int)$this->remaining - $minutes);
        $this->encounter->torch_timer_remaining = $this->remaining;

        // Ensure other props are correctly typed for broadcast
        $this->duration = (int)($this->encounter->torch_timer_duration ?? 0);
        $this->isRunning = (bool)$this->encounter->torch_timer_is_running;

        $this->encounter->save();
        Log::info("subtractTime saved Encounter ID {$this->encounter->id}: Remaining={$this->encounter->torch_timer_remaining}");
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
