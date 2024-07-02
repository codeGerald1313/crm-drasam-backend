<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    protected $fillable = [
        'uuid',
        'contact_id', 
        'start_date',
        'last_activity',
        'status',
        'status_bot',
        'channel_id'
    ];
    

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function assignment()
    {
        return $this->hasMany(Assignment::class);
    }

    public function dateremenber()
    {
        return $this->hasMany(DateRemenber::class, 'conversation_id');
    }

}
