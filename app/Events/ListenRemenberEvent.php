<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListenRemenberEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $remenber;
    public $advisor;
    public $contact;


    public function __construct($remenber, $advisor, $contact)
    {
        $this->remenber = $remenber;

        $this->advisor = $advisor;
        $this->contact = $contact;
    }

    public function broadcastOn()
    {
        return new Channel('remenber-events');
    }
}
