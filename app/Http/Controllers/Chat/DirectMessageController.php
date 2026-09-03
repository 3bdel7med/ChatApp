<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DirectMessageController extends Controller
{
public function getOrCreateConversation(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => ['required', 'exists:users,id', 'different:'.auth()->id()],
        ]);
        $authId = auth()->id();
        $receiverId = $validated['receiver_id'];
        $conversation = \App\Models\Conversation::where(function ($query) use ($authId, $receiverId) {
            $query->where('sender_id', $authId)
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($authId, $receiverId) {
            $query->where('sender_id', $receiverId)
                ->where('receiver_id', $authId);
        })->first();
        if (!$conversation) {
            $conversation = \App\Models\Conversation::create([
                'sender_id' => $authId,
                'receiver_id' => $receiverId,
                'type' => 'direct',
            ]);
        }
        return redirect()->route('chat.show', $conversation->id);

    }
}
