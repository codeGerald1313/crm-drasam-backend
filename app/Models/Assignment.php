<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Assignment extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'assignments';

    protected $fillable = [
        'advisor_id',
        'contact_id',
        'conversation_id',
        'state',
        'time',
        'interes_en',
        'reason_id',
        'tag_id'
    ];

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function customer()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function reasons()
    {
        return $this->belongsTo(ClousureReason::class, 'reason_id');
    }

    public function getJWTIdentifier () {
        return $this->getKey();
    }

    public function getJWTCustomClaims () {
        return [];
    }

    public static function countAllAssignments() {
        return self::whereNotNull('advisor_id')->count();
    }
    
}
