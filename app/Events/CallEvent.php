<?php

namespace App\Events;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $data;
    public $caller;
    public $conversationId;
    public $receiverId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $type, $data, User $caller, int $conversationId)
    {
        $this->type = $type;
        $this->data = $data ?? [];
        $this->caller = $caller;
        $this->conversationId = $conversationId;

        // البحث عن المحادثة مع جلب المشاركين (نتأكد من استخدام العلاقة الصحيحة participants أو users)
        $conversation = Conversation::find($conversationId);

        if ($conversation) {
            // نحدد المستقبل: إذا كانت محادثة مباشرة أو جماعية، نجيب الطرف الثاني اللي مش هو الـ caller
            // نستخدم الاستعلام المباشر من جدول الـ pivot لضمان عدم حدوث خطأ في اسم العلاقة
            $receiver = $conversation->participants()
                ->where('users.id', '!=', $caller->id)
                ->first();

            // لو مش موجودة في participants، نجرب الـ sender / receiver المباشرين لو وجدوا
            if (!$receiver) {
                if ($conversation->type === 'direct') {
                    $receiverId = ($conversation->sender_id === $caller->id)
                        ? $conversation->receiver_id
                        : $conversation->sender_id;
                    $receiver = User::find($receiverId);
                }
            }

            $this->receiverId = $receiver?->id;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.' . $this->conversationId),
        ];

        if ($this->receiverId) {
            $channels[] = new PrivateChannel('user.' . $this->receiverId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'call.event';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'caller' => [
                'id' => $this->caller->id,
                'name' => $this->caller->name,
                'avatar' => $this->caller->avatar_url ?? null,
            ],
            'conversation_id' => $this->conversationId,
        ];
    }
}
