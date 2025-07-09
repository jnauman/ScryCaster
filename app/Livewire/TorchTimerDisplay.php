<?php

namespace App\Livewire;

use App\Models\Encounter;
use Livewire\Component;

class TorchTimerDisplay extends Component
{
    public int $encounterId;
    public ?int $remaining = null;
    public ?int $duration = null;
    public bool $isRunning = false;

    public function mount(Encounter $encounter): void
    {
        $this->encounterId = $encounter->id;
        $this->remaining = $encounter->torch_timer_remaining;
        $this->duration = $encounter->torch_timer_duration;
        $this->isRunning = $encounter->torch_timer_is_running ?? false;
    }

    public function getListeners(): array
    {
        return [
            "echo-private:encounter.{$this->encounterId},.TorchTimerUpdated" => 'handleTorchTimerUpdate',
        ];
    }

    public function handleTorchTimerUpdate(array $payload): void
    {
        if ($this->encounterId === $payload['encounterId']) {
            $this->remaining = $payload['remaining'];
            $this->duration = $payload['duration'];
            $this->isRunning = $payload['isRunning'];
        }
    }

    public function render()
    {
        return view('livewire.torch-timer-display');
    }
}
