<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TurnChanged implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public $encounterId;
	public $currentTurn;

	/**
	 * Create a new event instance.
	 */
	public function __construct($encounterId, $currentTurn)
	{
		Log::info('TurnChanged event created: encounterId=' . $encounterId . ', currentTurn=' . $currentTurn); // Add this line

		$this->encounterId = $encounterId;
		$this->currentTurn = $currentTurn;
	}
	public function broadcastOn()
	{
		return ['encounter'];
	}

	public function broadcastAs()
	{
		return 'TurnChanged';
	}
}