<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NewMessageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {

        return $this->collection->map(function ($row) {

            $messageContent = json_decode($row->content, true);

            if (in_array($row->type, ['image', 'audio', 'video', 'document']) && $row->emisor === 'Customer') {
                $messageContent['link'] = optional($row->attachment)->url;
            }

            return [
                'id' => $row->id,
                'conversation_id' => $row->conversation_id,
                'api_id' => $row->api_id,
                'content' => $messageContent,
                'referral' => isset($row->referral) ? json_decode($row->referral) : '',
                'type' => $row->type,
                'date_of_issue' => $row->date_of_issue,
                'emisor' => $row->emisor,
                'emisor_id' => $row->emisor_id,
                'updated_at' => $row->updated_at,
                'created_at' => $row->created_at,
                'status_m' => $row->status,
                'customer' => optional(optional($row->conversation)->customer)->name,
                'num_phone' => optional(optional($row->conversation)->customer)->num_phone
            ];
        })->all();
    }
}
