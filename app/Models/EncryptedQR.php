<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class EncryptedQR extends Model implements JWTSubject
{
    use HasFactory;
    
    protected $table = 'encrypted_qrs';

    protected $fillable = ['hashed_content'];

    public function getJWTIdentifier () {
        return $this->getKey();
    }

    public function getJWTCustomClaims () {
        return [];
    }
}
