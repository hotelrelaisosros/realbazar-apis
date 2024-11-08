<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    public function senderMessage()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'chat_id', 'id');
    }
}
