<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'document' => $this->document,
            'document_id' => $this->document_id,
            'birthdate' => $this->birthdate,
            'email' => $this->email,
            'country_code' => $this->country_code,
            'num_phone' => $this->num_phone,
            'status' => $this->status
        ];
    }
}
