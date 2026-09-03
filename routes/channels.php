<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
| هنا بنحدد صلاحيات الوصول لقنوات الـ WebSockets (Echo Listener Authorization)
*/

// 1. القناة الخاصة بالمستخدم لاستقبال "الرنة" والمكالمات القادمة والـ Notifications
// ندعم الشكلين (user.{id} و App.Models.User.{id}) لضمان عمل Echo دون مشاكل
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// 2. قناة المحادثة (خاصة بنقل رسائل الشات وإشارات WebRTC Signal داخل الغرفة)
Broadcast::channel('chat.{conversationId}', function (User $user, int $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    // للمحادثات المباشرة: التاكد من أن المستخدم هو الراسل أو المستقبل
    if ($conversation->type === 'direct') {
        return (int) $user->id === (int) $conversation->sender_id || (int) $user->id === (int) $conversation->receiver_id;
    }

    // لمحادثات المجموعات (Group Chat)
    return $conversation->participants()->where('user_id', $user->id)->exists();
});

// alias للقناة لضمان عدم حدوث خطأ إذا استدعاها الفرونت باسم conversation بدلاً من chat
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    if ($conversation->type === 'direct') {
        return (int) $user->id === (int) $conversation->sender_id || (int) $user->id === (int) $conversation->receiver_id;
    }

    return $conversation->participants()->where('user_id', $user->id)->exists();
});