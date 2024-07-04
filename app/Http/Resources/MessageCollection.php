<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MessageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Almacena los contenidos únicos para evitar duplicados
        $uniqueContents = [];

        return $this->collection->map(function ($row) use (&$uniqueContents) {
            $messageContent = json_decode($row->content, true);

            // Verificar si el contenido ya ha sido procesado
            $contentHash = md5(json_encode($messageContent));
            if (in_array($contentHash, $uniqueContents)) {
                // Si el contenido ya existe, omitir este mensaje
                return null;
            }

            // Agregar el contenido al conjunto de contenidos únicos
            $uniqueContents[] = $contentHash;

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
            ];
        })->filter()->all(); // Filtrar los valores nulos
    }
}
