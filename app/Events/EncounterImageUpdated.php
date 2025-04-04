<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Add this
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EncounterImageUpdated implements ShouldBroadcast // Implement ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public int $encounterId;
	public string $imageUrl;

	/**
	 * Create a new event instance.
	 */
	public function __construct(int $encounterId, string $imageUrl)
	{
		$this->encounterId = $encounterId;
		$this->imageUrl = $imageUrl;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return array<int, \Illuminate\Broadcasting\Channel>
	 */
	/*public function broadcastOn(): array
	{
		// Use a private channel for security, specific to the encounter
		return [
			new PrivateChannel('encounter.' . $this->encounterId),
		];
	}*/

	public function broadcastOn()
	{
		return ['encounter'];
	}

	public function broadcastAs()
	{
		return 'EncounterImageUpdated';
	}

	/**
	 * The event's broadcast name. Optional, defaults to class name.
	 */
	// public function broadcastAs(): string
	// {
	//     return 'image.updated';
	// }

	/**
	 * Get the data to broadcast.  <-- !! ADD THIS METHOD !!
	 * This ensures the payload is a clean array.
	 *
	 * @return array<string, mixed>
	 */
	public function broadcastWith(): array // <-- !! ADD THIS METHOD !!
	{
		return [
			'encounterId' => $this->encounterId,
			'imageUrl' => $this->imageUrl,
		];
	}
}