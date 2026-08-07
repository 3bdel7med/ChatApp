<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'last_message_at',
        'type',
        'name',
        'avatar',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_user');
    }

    public function getOtherUser(int $userId)
    {
        if ($this->type === 'group') {
            return $this->participants()->where('user_id', '!=', $userId)->get();
        }
        return $this->sender_id === $userId ? $this->receiver : $this->sender;
    }
}
