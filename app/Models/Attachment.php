<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $table = 'attachments';

    protected $fillable = [
        'attachment_id',
        'message_id',
        'url',
        'mime_type',
        'sha256',
        'file_size',
        'messaging_product'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id', 'api_id');
    }
}
