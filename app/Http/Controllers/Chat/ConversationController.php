<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConversationController extends Controller
{
    public function index()
    {
        return Inertia::render('Chat/Index', [
            'conversations' => $this->getAuthUserConversations(),
        ]);
    }

    public function show($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        $isAuthorized = $conversation->sender_id === auth()->id()
            || $conversation->receiver_id === auth()->id()
            || $conversation->participants()->where('user_id', auth()->id())->exists();

        abort_unless($isAuthorized, 403);

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->get();

        return Inertia::render('Chat/Show1', [
            'conversation' => $conversation->load(['sender', 'receiver', 'participants']),
            'messages'     => $messages,
            'conversations' => $this->getAuthUserConversations(),
        ]);
    }

    public function storeDirect(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => ['required', 'exists:users,id', 'different:'.auth()->id()],
        ]);

        $authId = auth()->id();
        $receiverId = $validated['receiver_id'];

        $conversation = Conversation::where(function ($query) use ($authId, $receiverId) {
            $query->where('sender_id', $authId)->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($authId, $receiverId) {
            $query->where('sender_id', $receiverId)->where('receiver_id', $authId);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'sender_id' => $authId,
                'receiver_id' => $receiverId,
                'type' => 'direct',
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(
                $this->formatConversation($conversation->load(['sender', 'receiver', 'messages'])),
                201
            );
        }

        return redirect()->route('chat.show', $conversation->id);
    }

    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id', 'different:'.auth()->id()],
        ]);

        $authId = auth()->id();
        $allUserIds = array_unique(array_merge($validated['user_ids'], [$authId]));

        $conversation = Conversation::create([
            'sender_id' => $authId,
            'type' => 'group',
            'name' => $validated['name'],
        ]);

        $conversation->participants()->attach($allUserIds);

        if ($request->wantsJson()) {
            return response()->json(
                $this->formatConversation($conversation->load(['participants', 'messages'])),
                201
            );
        }

        return redirect()->route('chat.show', $conversation->id);
    }

    private function getAuthUserConversations()
    {
        $authId = auth()->id();

        $directIds = Conversation::where('type', 'direct')
            ->where(function ($query) use ($authId) {
                $query->where('sender_id', $authId)->orWhere('receiver_id', $authId);
            })->pluck('id');

        $groupIds = Conversation::where('type', 'group')
            ->whereHas('participants', fn($q) => $q->where('user_id', $authId))
            ->pluck('id');

        $allIds = $directIds->merge($groupIds)->unique()->values();

        $conversations = Conversation::whereIn('id', $allIds)
            ->with(['sender', 'receiver', 'participants', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->orderByDesc('updated_at')
            ->get();

        return $conversations->map(fn($conv) => $this->formatConversation($conv));
    }

    private function formatConversation($conversation)
    {
        $authId = auth()->id();

        if ($conversation->type === 'group') {
            return [
                'id' => $conversation->id,
                'type' => 'group',
                'name' => $conversation->name,
                'other_user' => null,
                'participants' => $conversation->participants,
                'last_message' => $conversation->messages->first(),
                'updated_at' => $conversation->updated_at,
            ];
        }

        $otherUser = $conversation->sender_id === $authId ? $conversation->receiver : $conversation->sender;

        return [
            'id' => $conversation->id,
            'type' => 'direct',
            'other_user' => $otherUser,
            'participants' => null,
            'last_message' => $conversation->messages->first(),
            'updated_at' => $conversation->updated_at,
        ];
    }
}
