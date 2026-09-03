<?php

namespace App\Http\Controllers;

use App\Events\CallSignalSent;
use App\Events\IncomingCallEvent;
use App\Models\Conversation;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function signal(Request $request, $conversation = null)
    {
        // 1. تحديد رقم المحادثة سواء من الـ Route Parameter أو الـ Request Body
        $conversationId = $conversation instanceof Conversation
            ? $conversation->id
            : ($conversation ?? $request->input('conversation_id'));

        if (!$conversationId) {
            return response()->json(['error' => 'Conversation ID is required'], 422);
        }

        // 2. التحقق من البيانات
        $validated = $request->validate([
            'type' => 'required|string|in:offer,answer,candidate,reject,end',
            'signal' => 'nullable',
            'receiver_id' => 'nullable|integer',
            'conversation_id' => 'nullable|integer',
        ]);

        $sender = auth()->user();

        // 3. بث الإشارة دائماً على قناة المحادثة ليعالجها الـ WebRTC Composable (سواء كانت offer أو answer أو candidate...)
        broadcast(new CallSignalSent(
            (int) $conversationId,
            $sender,
            $validated['type'],
            $validated['signal'] ?? null
        ))->toOthers();

          if ($validated['type'] === 'offer' && !empty($validated['receiver_id'])) {
            broadcast(new IncomingCallEvent(
                (int) $validated['receiver_id'],
                (int) $conversationId,
                $sender,
                $validated['signal'] ?? null
            ))->toOthers();
        }

        return response()->json(['status' => 'Signal broadcasted successfully']);
    }
}
