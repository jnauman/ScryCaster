<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel; // Changed from public Channel
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
// Log removed as it's not used in the updated version
// PresenceChannel removed as it's not used

class TurnChanged implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public int $encounterId; // Type hinted
	public int $currentTurn;  // Type hinted
	public int $currentRound; // Type hinted

	/**
	 * Create a new event instance.
	 *
	 * @param int $encounterId
	 * @param int $currentTurn
	 * @param int $currentRound
	 */
	public function __construct(int $encounterId, int $currentTurn, int $currentRound)
	{
		$this->encounterId = $encounterId;
		$this->currentTurn = $currentTurn;
		$this->currentRound = $currentRound;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * Broadcasts on a private channel specific to the encounter.
	 *
	 * @return array<int, \Illuminate\Broadcasting\Channel>
	 */
	public function broadcastOn(): array
	{
		// Broadcast on a private channel for the specific encounter
		return [
			new PrivateChannel('encounter.' . $this->encounterId),
		];
	}

	/**
	 * The name of the event as it should be broadcast.
	 *
	 * @return string
	 */
	public function broadcastAs(): string
	{
		return 'TurnChanged'; // Client will listen for ".TurnChanged"
	}

	/**
	 * Get the data to broadcast with the event.
	 *
	 * @return array<string, mixed>
	 */
	public function broadcastWith(): array
	{
		return [
			'encounterId' => $this->encounterId,
			'currentTurn' => $this->currentTurn,
			'currentRound' => $this->currentRound,
		];
	}
}