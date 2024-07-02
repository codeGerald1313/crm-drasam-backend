<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return $this->collection->map(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name
            ];
        })->all();
    }
}