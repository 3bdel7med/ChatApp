<?php

namespace App\Http\Controllers\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Notifications\NewChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation)
{
    $validated = $request->validate([
        'body' => 'required_without:file|nullable|string|max:5000',
        'file' => 'required_without:body|nullable|file|mimes:jpeg,png,jpg,pdf,mp3,wav,webm,ogg|max:20480',
    ]);

    $filePath = null;
    $fileName = null;
    $fileType = null;

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();

        // Categorize file type
        if (str_starts_with($mimeType, 'image/')) {
            $fileType = 'image';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            $fileType = 'audio';
        } else {
            $fileType = 'file';
        }

        $path = $file->store('chat_files', 'public');
        $filePath = Storage::url($path);
    }

    $message = $conversation->messages()->create([
        'sender_id' => auth()->id(),
        'receiver_id' => $conversation->type === 'group' ? null : $conversation->getOtherUser(auth()->id())->id,
        'body' => $validated['body'] ?? null,
        'file_path' => $filePath,
        'file_name' => $fileName,
        'file_type' => $fileType,
    ]);

        $conversation->update(['last_message_at' => now()]);

        $recipients = $conversation->type === 'group'
            ? $conversation->participants()->where('users.id', '!=', auth()->id())->get()
            : collect([$conversation->getOtherUser(auth()->id())])->filter();

        broadcast(new MessageSent($message))->toOthers();
        Notification::send($recipients, new NewChatMessage($message));

        return back()->with('message', $message);
    }
}
