<?php

namespace App\Livewire;

use App\Models\Encounter;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TorchTimerDisplay extends Component
{
    public int $encounterId;
    public ?int $remaining = null;
    public ?int $duration = null;
    public bool $isRunning = false;

    public function mount(Encounter $encounter): void
    {
        $this->encounterId = $encounter->id;
        $this->duration = (int)($encounter->torch_timer_duration ?? 0); // Default to 0 if not set, Alpine will handle null for display
        $this->remaining = (int)($encounter->torch_timer_remaining ?? $this->duration);
        if ($encounter->torch_timer_duration !== null && $encounter->torch_timer_remaining !== null && $this->remaining > $this->duration) {
            $this->remaining = $this->duration;
        }
        $this->isRunning = (bool)($encounter->torch_timer_is_running ?? false);
        Log::info("TorchTimerDisplay mounted for Encounter ID {$this->encounterId}: Duration={$this->duration}, Remaining={$this->remaining}, IsRunning={$this->isRunning}");
    }

    public function getListeners(): array
    {
        return [
            "echo-private:encounter.{$this->encounterId},.TorchTimerUpdated" => 'handleTorchTimerUpdate',
        ];
    }

    public function handleTorchTimerUpdate(array $payload): void
    {
        Log::info("TorchTimerDisplay received TorchTimerUpdated event for Encounter ID {$payload['encounterId']} with payload: " . json_encode($payload));
        if ($this->encounterId === $payload['encounterId']) {
            $this->remaining = $payload['remaining'];
            $this->duration = $payload['duration'];
            $this->isRunning = $payload['isRunning'];
            Log::info("TorchTimerDisplay updated for Encounter ID {$this->encounterId}: Remaining={$this->remaining}, Duration={$this->duration}, IsRunning={$this->isRunning}");

            // Dispatch an event for Alpine to pick up, specific to this component instance
            $this->dispatch("torchTimerUpdatedEcho-{$this->encounterId}", $payload);
            Log::info("TorchTimerDisplay dispatched internal event torchTimerUpdatedEcho-{$this->encounterId} with payload: " . json_encode($payload));
        } else {
            Log::info("TorchTimerDisplay ignored event for Encounter ID {$payload['encounterId']} as it doesn't match component's encounter ID {$this->encounterId}.");
        }
    }

    public function render()
    {
        return view('livewire.torch-timer-display');
    }
}
