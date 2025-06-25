<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnChanged implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public $encounterId;
	public $currentTurn;
	public $currentRound;

	/**
	 * Create a new event instance.
	 */
	public function __construct($encounterId, $currentTurn, $currentRound)
	{

		$this->encounterId  = $encounterId;
		$this->currentTurn  = $currentTurn;
		$this->currentRound = $currentRound;
	}

	public function broadcastOn()
	{
		return [
			new Channel('encounter.' . $this->encounterId),
		];
	}

	public function broadcastAs()
	{
		return 'TurnChanged';
	}

	public function broadcastWith(): array
	{
		return [
			'encounterId' => $this->encounterId,
			'currentTurn' => $this->currentTurn,
			'currentRound' => $this->currentRound,
		];
	}
}