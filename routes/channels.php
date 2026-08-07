<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;



Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('chat.{conversationId}', function (User $user, int $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    // Direct conversation: check sender/receiver
    if ($conversation->type === 'direct') {
        return $user->id === $conversation->sender_id || $user->id === $conversation->receiver_id;
    }

    // Group conversation: check if user is a participant
    return $conversation->participants()->where('user_id', $user->id)->exists();
});
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    return Conversation::find($conversationId)
        ?->participants()
        ->where('users.id', $user->id)
        ->exists();
});
