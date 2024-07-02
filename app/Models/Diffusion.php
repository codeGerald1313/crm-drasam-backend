<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\DetailDiffusion;

class Diffusion extends Model
{
    use HasFactory;

    protected $table = 'diffusions';

    protected $fillable = ['campaign_name','content_type', 'content_reference', 'user_id', 'date', 'status'];

    public function messages()
    {
        return $this->hasMany(Message::class, 'mass_message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(DetailDiffusion::class, 'diffusion_id');
    }

    public function setContentAttributes($type, $reference)
    {
        $this->attributes['content_type'] = $type;
        $this->attributes['content_reference'] = $reference;
    }

}
