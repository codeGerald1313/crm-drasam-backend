<?php

namespace App\Http\Resources;

use App\Models\Assignment;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ConversationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request): array
    {
        return $this->collection->map(function ($row) {

            $contactData = $row->customer;
            $contactData->interes_en = $row->interes_en;
            $lastMessage = $row->messages->last();
            $lastAssignment = $row->assignment->last();
            $lastDateRemenber = $row->dateremenber->last();
            if ($lastDateRemenber) {
                $lastDateRemenber = $row->dateremenber->where('status',1)->first();
            }

            // cantidad de mensajes
            $unreadCustomerMessages = $row->messages->filter(function ($message) {
                return $message->emisor === 'Customer' && $message->status === 'delivered';
            });

            return [
                'id' => $row->id,
                'contact' => $contactData,
                'contact_id' => $row->contact_id,
                'contact_name' => optional($row->customer)->name,
                'uuid' => $row->uuid,
                'start_date' => $row->start_date,
                'last_activity' => $row->last_activity,
                'updated_at' => $row->updated_at,
                'messages' => json_decode($lastMessage),
                'last_assign' => $lastAssignment,
                'lastAssignment' => $lastAssignment->reasons ?? '',
                'status_assignments' => $row->estado_asignacion,
                'date_remenber' => $lastDateRemenber,
                'status' => $row->status,
                'id_asignacion' => $row->id_asignacion,
                'interes_en' => $row->interes_en,
                'advisorName' => self::getAsignacion($row->id_asignacion),
                'advisorId' => self::getAdvisor($row->id_asignacion),
                'count_messages' => $unreadCustomerMessages->count() ?? 0
            ];
        })->all();
    }

    public static function getAsignacion($idAsignacion)
    {
       
        $assignments = Assignment::with('advisor')->find($idAsignacion);
        $advirsor = optional($assignments->advisor)->name . ' ' . optional($assignments->advisor)->last_name;
        return $advirsor;
    }

    public static function getAdvisor($idAsignacion)
    {
       
        $assignments = Assignment::with('advisor')->find($idAsignacion);
        $advirsor = optional($assignments->advisor)->id;
        return $advirsor;
    }
}
