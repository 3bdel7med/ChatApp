<?php

namespace App\Http\Controllers;

use App\Events\CallEvent;
use App\Models\Conversation;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function signal(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:offer,answer,ice-candidate,end-call'],
            'data' => ['required'],
        ]);

        // Authorize: check if user belongs to this conversation
        $isAuthorized = $conversation->sender_id === auth()->id()
            || $conversation->receiver_id === auth()->id()
            || $conversation->participants()->where('user_id', auth()->id())->exists();

        abort_unless($isAuthorized, 403);

        broadcast(new CallEvent(
            $validated['type'],
            $validated['data'],
            auth()->user(),
            $conversation->id
        ))->toOthers();

        return response()->json(['status' => 'signal sent']);
    }
}

