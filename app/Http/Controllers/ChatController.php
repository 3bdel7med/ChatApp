<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\NewChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('Chat/Index', [
            'conversations' => $this->getAuthUserConversations(),
        ]);
    }

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

    public function createGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id', 'different:'.auth()->id()],
        ]);

        $authId = auth()->id();

        // Include the authenticated user as a participant
        $allUserIds = array_merge($validated['user_ids'], [$authId]);
        $allUserIds = array_unique($allUserIds);

        $conversation = Conversation::create([
            'sender_id' => $authId,
            'type' => 'group',
            'name' => $validated['name'],
        ]);

        // Attach all participants
        $conversation->participants()->attach($allUserIds);

        return redirect()->route('chat.show', $conversation->id);
    }

    public function showConversation($conversationId)
    {
        $conversation = \App\Models\Conversation::findOrFail($conversationId);

        // Authorize: check if user is sender, receiver, or a participant
        $isAuthorized = $conversation->sender_id === auth()->id()
            || $conversation->receiver_id === auth()->id()
            || $conversation->participants()->where('user_id', auth()->id())->exists();

        abort_unless($isAuthorized, 403);

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->get();

return Inertia::render('Chat/Show', [
            'conversation' => $conversation->load(['sender', 'receiver', 'participants']),
            'messages'     => $messages,
            'conversations' => $this->getAuthUserConversations(),
        ]);
    }

    public function getUsersForGroup(Request $request)
    {
        $users = User::where('id', '!=', auth()->id())
            ->limit(50)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    private function getAuthUserConversations()
    {
        $authId = auth()->id();

        // Get IDs of conversations where user is a participant (direct or group)
        $directIds = Conversation::where('type', 'direct')
            ->where(function ($query) use ($authId) {
                $query->where('sender_id', $authId)
                    ->orWhere('receiver_id', $authId);
            })->pluck('id');

        $groupIds = Conversation::where('type', 'group')
            ->whereHas('participants', fn($q) => $q->where('user_id', $authId))
            ->pluck('id');

        $allIds = $directIds->merge($groupIds)->unique()->values();

        // Fetch all conversations with eager loading
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

    public function storeMessage(Request $request, Conversation $conversation)
    {
        // 1. Fixed validation: Body is required ONLY if no file is present
        $validated = $request->validate([
            'body' => 'required_without:file|nullable|string|max:5000',
            'file' => 'required_without:body|nullable|file|max:10240',
        ]);

        $filePath = null;
        $fileName = null;
        $fileType = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $fileName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();

            // Determine if image or general file
            $fileType = str_starts_with($mimeType, 'image/') ? 'image' : 'file';

            // Store file safely
            $path = $file->store('chat_files', 'public');

            // Import or use global helper to avoid class errors
            $filePath = Storage::url($path);
        }

        // For groups, receiver_id is null since there are multiple recipients
        if ($conversation->type === 'group') {
            $receiverId = null;
        } else {
            $receiverId = $conversation->getOtherUser(auth()->id())->id;
        }

        // 2. Used safe null-coalescing for the body field
        $message = $conversation->messages()->create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $receiverId,
            'body'        => $validated['body'] ?? null,
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_type'   => $fileType,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);
        // Resolve notification recipients based on conversation type
        if ($conversation->type === 'group') {
            // Group: all participants except the sender
            $recipients = $conversation->participants()
                ->where('users.id', '!=', auth()->id())
                ->get();
        } else {
            // Direct: the other participant
            $recipients = collect([$conversation->getOtherUser(auth()->id())])->filter();
        }

        broadcast(new MessageSent($message))->toOthers();
        Notification::send($recipients, new NewChatMessage($message));


        return back()->with('message', $message);
    }
}
