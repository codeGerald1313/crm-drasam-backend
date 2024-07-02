<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request): array
    {
        return $this->collection->map(function ($row) {

            return [
                'id' => optional($row->customer)->id,
                'name' => optional($row->customer)->name,
                'asignId' => $row->id
            ];
        })->all();
    }
}
