<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingCallEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $receiverId;
    public $conversationId;
    public $caller;
    public $signal;

    public function __construct($receiverId, $conversationId, $caller, $signal)
    {
        $this->receiverId = $receiverId;
        $this->conversationId = $conversationId;
        $this->caller = $caller;
        $this->signal = $signal; // الـ Offer
    }

    public function broadcastOn(): array
    {
        // بث على القناة الخاصة بالمستقبل
        return [
            new PrivateChannel('App.Models.User.' . $this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'incoming-call';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'caller_name' => $this->caller->name,
            'caller_avatar' => $this->caller->avatar ?? null,
            'signal_data' => $this->signal,
        ];
    }
}