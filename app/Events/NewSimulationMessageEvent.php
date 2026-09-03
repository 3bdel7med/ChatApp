<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // لضمان إرسال البث فوراً دون انتظار Queue
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSimulationMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $speaker,
        public string $message
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // بث الرسالة عبر القناة التي يستمع إليها الـ Frontend
        return [
            new Channel('simulation-channel'),
        ];
    }

    /**
     * اسم الحدث الذي يستمع إليه Vue.js (Laravel Echo)
     */
    public function broadcastAs(): string
    {
        return 'new-simulation-message';
    }
}
