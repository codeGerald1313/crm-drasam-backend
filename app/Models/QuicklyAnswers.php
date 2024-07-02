<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class QuicklyAnswers extends Model implements JWTSubject
{
    use HasFactory;
    protected $table = 'quickly_answers';
    protected $fillable = ['id', 'title', 'message', 'user_id', 'type'];

    public function getJWTIdentifier () {
        return $this->getKey();
    }

    public function getJWTCustomClaims () {
        return [];
    }
}
