<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessGeminiSimulation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimulationController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string',
            'conversation_id' => 'required|exists:conversations,id',
            'receiver_id' => 'nullable|integer',
        ]);

        // Dispatch background job with authenticated user ID
        ProcessGeminiSimulation::dispatch(
            conversationId: (int) $validated['conversation_id'],
            topic: $validated['topic'],
            senderId: Auth::id(),
            receiverId: isset($validated['receiver_id']) ? (int) $validated['receiver_id'] : null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Simulation job queued successfully.',
        ], 202);
    }
}
