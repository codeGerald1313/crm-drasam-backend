<?php

namespace App\Http\Resources;

use App\Models\Assignment;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ContactCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<int|string, mixed>
     */
    public function toArray($request): array
    {
        return $this->collection->map(function ($row) {

            $lastAssignment = $row->assignment->last();

            return [
                'id' => $row->id,
                'name' => $row->name,
                'num_phone' => $row->num_phone,
                'status' => $row->status,
                'created_at' => $row->created_at,
                'advisorName' => self::getAdvisor($lastAssignment) ?? '',
                'status_reason' => self::getReasons($lastAssignment) ?? '',
                'tag' => self::getTag($lastAssignment) ?? '',
                'interes_en' => self::getInteres($lastAssignment) ?? ''
            ];
        })->all();
    }

    public static function getAdvisor($idAsignacion)
    {
        $idAsign = optional($idAsignacion)->id;
        $assignments = Assignment::with('advisor')->find($idAsign);
        $advirsor = optional(optional($assignments)->advisor)->name . ' ' . optional(optional($assignments)->advisor)->last_name;
        return $advirsor;
    }

    public static function getReasons($idAsignacion)
    {
        $idAsign = optional($idAsignacion)->id;
        $assignments = Assignment::with('reasons')->find($idAsign);
        $reasons = optional(optional($assignments)->reasons)->name;
        return $reasons;
    }

    public static function getTag($idAsignacion)
    {
        $idAsign = optional($idAsignacion)->id;
        $assignments = Assignment::find($idAsign);
        $tag = optional($assignments)->tag_id;
        return $tag;
    }

    public static function getInteres($idAsignacion)
    {
        $idAsign = optional($idAsignacion)->id;
        $assignments = Assignment::find($idAsign);
        $interes = optional($assignments)->interes_en;
        return $interes;
    }
}
