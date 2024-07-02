<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateRemenber extends Model
{
    use HasFactory;

    protected $table = 'date_remenber';

    protected $fillable = ['date_to_remenber', 'time_to_remenber', 'conversation_id', 'status'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

}