<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Conexion extends Model implements JWTSubject
{
    use HasFactory;
    
    protected $table = 'conexion';
    protected $fillable = ['company_name', 'token', 'phone_id', 'phone', 'welcome', 'status', 'status_bot', 'user_id'];
    
    public function getJWTIdentifier () {
        return $this->getKey();
    }
    
    public function getJWTCustomClaims () {
        return [];
    }
}
