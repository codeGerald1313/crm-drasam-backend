<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactWaitTime extends Model
{
    use HasFactory;

    protected $table = 'contact_wait_times';

    protected $fillable = [
        'contact_id',
        'advisor_id',
        'wait_time',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }


    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }
}
