<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcast when an encounter's image is updated.
 *
 * This event implements ShouldBroadcast, allowing it to be sent over WebSockets (e.g., Laravel Echo)
 * to update clients in real-time.
 */
class EncounterImageUpdated implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	/** @var int The ID of the encounter whose image was updated. */
	public int $encounterId;

	/** @var string The new URL of the image for the encounter. */
	public string $imageUrl;

	/**
	 * Create a new event instance.
	 *
	 * @param int $encounterId The ID of the encounter.
	 * @param string $imageUrl The new image URL.
	 */
	public function __construct(int $encounterId, string $imageUrl)
	{
		$this->encounterId = $encounterId;
		$this->imageUrl = $imageUrl;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * This event broadcasts on a private channel specific to the encounter,
	 * ensuring only authorized users for that encounter receive updates.
	 * The channel name will be, e.g., 'private-encounter.123'.
	 *
	 * @return array<int, \Illuminate\Broadcasting\Channel>
	 */
	public function broadcastOn()
	{
		return [
			new Channel('encounter.' . $this->encounterId),
		];

	}

	/**
	 * The name of the event as it should be broadcast.
	 *
	 * This is the event name client-side listeners (e.g., Echo) will subscribe to.
	 * If not specified, Laravel defaults to the class name.
	 * It's good practice to define it explicitly.
	 *
	 * @return string
	 */
	public function broadcastAs(): string
	{
		return 'EncounterImageUpdated'; // Client will listen for ".EncounterImageUpdated"
	}

	/**
	 * Get the data to broadcast with the event.
	 *
	 * This method controls the payload sent with the broadcast event.
	 * It ensures a consistent and clean array structure for the event data.
	 *
	 * @return array<string, mixed>
	 */
	public function broadcastWith(): array
	{
		return [
			'encounterId' => $this->encounterId,
			'imageUrl' => $this->imageUrl,
		];
	}
}