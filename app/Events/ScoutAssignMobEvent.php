<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use stdClass;

class ScoutAssignMobEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly stdClass $data;

    /**
     * Create a new event instance.
     */
    public function __construct(stdClass $data) 
    {
        $this->data = $data;
        Log::info("Hopefully broadcasting on {$this->data->slug}.{$this->data->collaborator_password}");
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("scouts.{$this->data->slug}"),
            new Channel("scouts.{$this->data->slug}.{$this->data->collaborator_password}"),
        ];
    }
}
