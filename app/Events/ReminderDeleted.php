<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reminderId;
    public $conversation;

    public function __construct($reminderId, $conversation)
    {
        $this->reminderId = $reminderId;
        $this->conversation = $conversation;
    }

    public function broadcastOn()
    {
        return new Channel('reminder-deleted');
    }
}
