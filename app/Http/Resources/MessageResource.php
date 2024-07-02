<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $messageContent = json_decode($this->content, true);

        if (in_array($this->type, ['image', 'audio', 'video', 'document']) && $this->emisor === 'Customer') {
            $messageContent['link'] = optional($this->attachment)->url;
        }

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'api_id' => $this->api_id,
            'content' => $messageContent,
            'referral' => isset($this->referral) ? json_decode($this->referral) : '',
            'type' => $this->type,
            'date_of_issue' => $this->date_of_issue,
            'emisor' => $this->emisor,
            'emisor_id' => $this->emisor_id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'status_m' => $this->status
        ];
        
    }
}
