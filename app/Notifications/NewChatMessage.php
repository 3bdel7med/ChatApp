<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewChatMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Broadcast the notification over the user's private channel.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender' => $this->message->sender->only('id', 'name'),
            'body' => $this->message->body,
            'preview' => $this->previewText(),
            'created_at' => $this->message->created_at->diffForHumans(),
        ]);
    }

    /**
     * Get the array representation of the notification (stored in DB).
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'preview' => $this->previewText(),
        ];
    }

    /**
     * Build a short preview of the message (handles file-only messages).
     */
    private function previewText(): string
    {
        if (! empty($this->message->body)) {
            return Str::limit($this->message->body, 60);
        }

        if ($this->message->file_type === 'image') {
            return '[صورة]';
        }

        if (! empty($this->message->file_name)) {
            return '[ملف] ' . Str::limit($this->message->file_name, 40);
        }

        return 'رسالة جديدة';
    }
}

