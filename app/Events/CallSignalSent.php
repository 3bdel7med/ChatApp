<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignalSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $sender;
    public $type;      // 'offer', 'answer', 'candidate', 'reject', 'end'
    public $signal;    // بيانات الـ SDP أو الـ Candidate

    public function __construct($conversationId, $sender, $type, $signal = null)
    {
        $this->conversationId = $conversationId;
        $this->sender = $sender; // استقبال كائن الـ User أو الـ ID
        $this->type = $type;
        $this->signal = $signal;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.event';
    }

    public function broadcastWith(): array
    {
        // استخراج الـ ID سواء تم تمرير Model أو رقم مباشر
        $senderId = is_object($this->sender) ? $this->sender->id : $this->sender;
        $senderName = is_object($this->sender) ? ($this->sender->name ?? 'User') : 'User';

        return [
            'type' => $this->type,
            'signal' => $this->signal,
            'caller' => [
                'id' => (int) $senderId,
                'name' => $senderName,
            ],
        ];
    }
}
