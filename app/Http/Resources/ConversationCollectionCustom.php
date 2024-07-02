<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ConversationCollectionCustom extends ResourceCollection
{
    public function toArray($request): array
    {
        return $this->collection->map(function ($conversation) {

            return [
                'advisor_name' => $conversation->advisor_name,
                'contact_name' => $conversation->contact_name,
                'last_activity' => $conversation->last_activity,
                'messages' => isset($conversation->messages) ? json_decode($conversation->messages) : 'Sin mensajes',
            ];
        })->all();
    }

    
}
