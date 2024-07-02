<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MassMessageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request): array
    {
        return $this->collection->map(function ($row) {

            $userName = $row->user;

            $countCustomer = $row->messages->filter(function ($message) {
                return $message->emisor === 'Advisor';
            });

            $shippingProcess = $row->messages->reject(function ($message) {
                return $message->status !== 'sent';
            });
           
            return [
                'id' => $row->id,
                'campaign_name' => $row->campaign_name,
                'content_type' => $row->content_type,
                'count_contact' => $countCustomer->count() ?? 0,
                'user' => optional($userName)->name . ' ' . optional($userName)->last_name,
                'date' => $row->date,
                'status' => $shippingProcess->isNotEmpty()
            ];
        })->all();
    }
}
