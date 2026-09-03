<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GroupController extends Controller
{
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
       public function getUsersForGroup(Request $request)
    {
        $users = User::where('id', '!=', auth()->id())
            ->limit(50)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

}
