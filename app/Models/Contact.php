<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Contact extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'contacts';

    protected $fillable = [
        'name',
        'num_phone',
        'document', 
        // 'document_id',
        'email',
        // 'country_code',
        'status',
        'student'
    ];


    public function assignment()
    {
        return $this->hasMany(Assignment::class);
    }


    /* public function document () {
        return $this->belongsTo('App\Models\Document');
    }*/

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
