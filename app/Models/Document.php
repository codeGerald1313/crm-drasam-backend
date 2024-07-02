<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Document extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'documents';

    public function contacts () {
        return $this->hasMany('App\Models\Contact');
    }

    public function getJWTIdentifier () {
        return $this->getKey();
    }

    public function getJWTCustomClaims () {
        return [];
    }
}
