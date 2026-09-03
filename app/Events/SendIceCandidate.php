<?php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class SendIceCandidate implements ShouldBroadcastNow
{
    use SerializesModels;

    public $toUserId;
    public $candidate;
    public $conversationId;

    public function __construct($toUserId, $candidate, $conversationId)
    {
        $this->toUserId = $toUserId;
        $this->candidate = $candidate;
        $this->conversationId = $conversationId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->toUserId);
    }

    public function broadcastAs()
    {
        return 'call.ice-candidate';
    }
}