<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'conversation_id',
        'api_id',
        'context',
        'content',
        'referral',
        'type',
        'date_of_issue',
        'status',
        'emisor',
        'emisor_id',
        'mass_message_id'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function attachment()
    {
        return $this->hasOne(Attachment::class, 'message_id', 'api_id');
    }
    
    public function diffusion()
    {
        return $this->belongsTo(Diffusion::class, 'mass_message_id');
    }

}
