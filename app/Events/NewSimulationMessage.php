<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Use ShouldBroadcastNow for immediate websockets
use Illuminate\Queue\SerializesModels;

class NewSimulationMessage implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
        public string $speaker = 'AI Assistant'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->conversation_id),
        ];
    }

    /**
     * Exact name expected by Echo in Vue (.simulation.message)
     */
    public function broadcastAs(): string
    {
        return 'simulation.message';
    }

    /**
     * Structure payload expected by Vue listener
     */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'speaker' => $this->speaker,
        ];
    }
}
