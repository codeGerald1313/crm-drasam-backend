<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationReopening extends Model
{
    use HasFactory;

    protected $table = 'conversation_reopenings';

    protected $fillable = [
        'conversation_id',
        'reopened_at',
        'reason'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }
}
