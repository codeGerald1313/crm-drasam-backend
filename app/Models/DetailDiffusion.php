<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contact;
use App\Models\Diffusion;


class DetailDiffusion extends Model
{
    use HasFactory;

    protected $fillable = ['diffusion_id', 'contact_id'];


    public function contact()
    {
        return $this->belongsTo(Contact::class, 'user_id');
    }

    public function diffusion()
    {
        return $this->belongsTo(Diffusion::class, 'diffusion_id');
    }
}
