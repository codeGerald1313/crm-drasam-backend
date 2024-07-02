<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WaitTimeCollection extends ResourceCollection
{

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($waitTime) {
                return [
                    'Contacto' => $waitTime->name,
                    'Espera' => $waitTime->formatted_time
                ];
            })
        ];
    }
}
