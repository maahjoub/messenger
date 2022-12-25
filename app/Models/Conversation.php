<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'conversations';

    public function participant() 
    {
        return $this->belongsToMany(User::class, 'participants')->withPivot([
            'joined_at', 'role'
        ]);
    }
    
    public function theMessage() 
    {
        return $this->hasMany(message::class, 'conversation_id', 'id')->latest();
    }
    
    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function lastMessage() 
    {
        return $this->belongsTo(message::class, 'last_message_id', 'messages.id')->withDefault(__('No Message Yet !!!'));
    }

}
