<?php

namespace App\Http\Resources;

use App\Models\Assignment;
use Illuminate\Http\Request;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class NewConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contactData = $this->customer;
        $contactData->interes_en = $this->assignment->last()->interes_en;
        $lastMessage = $this->messages->last();

        if($lastMessage){
            $messageContent = new MessageResource($lastMessage);
        }

        $lastAssignment = $this->assignment->last();
        $lastDateRemenber = $this->dateremenber->where('status',1)->first();

        $estadoAsignacion = "2";
    
        if ($lastAssignment) {
            if ($lastAssignment->advisor_id === null) {
                $estadoAsignacion = "1";
            } elseif ($lastAssignment->state === null || $lastAssignment->advisor_id === null) {
                $estadoAsignacion = "2";
            } elseif ($lastAssignment->state !== null && $lastAssignment->advisor_id !== null) {
                $estadoAsignacion = "3";
            }
        }

        $unreadCustomerMessages = $this->messages->filter(function ($message) {
            return $message->emisor === 'Customer' && $message->status === 'delivered';
        });

        return [
            'id' => $this->id,
            'contact' => $contactData,
            'contact_id' => $this->contact_id,
            'contact_name' => optional($this->customer)->name,
            'uuid' => $this->uuid,
            'start_date' => $this->start_date,
            'last_activity' => $this->last_activity,
            'updated_at' => $this->updated_at,
            'messages' => $messageContent ?? '',
            'last_assign' => $lastAssignment,
            'lastAssignment' => $lastAssignment->reasons,
            'status_assignments' => $estadoAsignacion,
            'date_remenber' => $lastDateRemenber,
            'status' => $this->status,
            'id_asignacion' => $lastAssignment->id,
            'interes_en' => $this->assignment->last()->interes_en,
            'advisorName' => self::getAsignacion($lastAssignment->id),
            'advisorId' => self::getAdvisor($lastAssignment->id),
            'count_messages' => $unreadCustomerMessages->count() ?? 0
        ];
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
