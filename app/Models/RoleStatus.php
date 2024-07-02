<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class RoleStatus extends Model implements JWTSubject
{
    use HasFactory;
    protected $table = 'role_status';
    protected $fillable = [
        'status',
        'id_role',
    ];

    public function getJWTIdentifier () {
        return $this->getKey();
    }

    public function getJWTCustomClaims () {
        return [];
    }
}
