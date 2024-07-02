<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'email' => $row->email
            ];
        })->all();
    }
}
