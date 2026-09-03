<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class SendAnswer implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $toUserId;
    public $answer;
    public $conversationId;

    public function __construct($toUserId, $answer, $conversationId)
    {
        $this->toUserId = $toUserId;
        $this->answer = $answer;
        $this->conversationId = $conversationId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->toUserId);
    }

    public function broadcastAs()
    {
        return 'call.answer';
    }
}