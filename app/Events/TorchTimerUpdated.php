<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TorchTimerUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $encounterId;
    public int $remaining;
    public int $duration;
    public bool $isRunning;

    /**
     * Create a new event instance.
     */
    public function __construct(int $encounterId, int $remaining, int $duration, bool $isRunning)
    {
        $this->encounterId = $encounterId;
        $this->remaining = $remaining;
        $this->duration = $duration;
        $this->isRunning = $isRunning;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('encounter.' . $this->encounterId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'TorchTimerUpdated';
    }

	public function broadcastWith(): array
	{
		return [
			'encounterId' => $this->encounterId,
			'remaining' => $this->remaining,
			'duration' => $this->duration,
			'isRunning' => $this->isRunning,
		];
	}
}
