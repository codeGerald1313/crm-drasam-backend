<?php

namespace App\Http\Resources;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
        $lastAssignment = $this->assignment->last();
        $lastDateRemenber = $this->dateremenber->where('status',1)->first();

        return [
            'id' => $this->id,
            'contact' => $contactData,
            'contact_id' => $this->contact_id,
            'contact_name' => optional($this->customer)->name,
            'uuid' => $this->uuid,
            'start_date' => $this->start_date,
            'last_activity' => $this->last_activity,
            'updated_at' => $this->updated_at,
            'messages' => $lastMessage,
            'last_assign' => $lastAssignment,
            'lastAssignment' => $lastAssignment->reasons,
            'status_assignments' => $lastAssignment->state,
            'date_remenber' => $lastDateRemenber,
            'status' => $this->status,
            'id_asignacion' => $lastAssignment->id,
            'interes_en' => $this->assignment->last()->interes_en,
            'advisorName' => self::getAsignacion($lastAssignment->id),
            'advisorId' => self::getAdvisor($lastAssignment->id)
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
