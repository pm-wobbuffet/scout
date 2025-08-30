<?php

namespace App\Events\Scout;

use App\Models\Scout;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstanceCountsUpdated implements ShouldDispatchAfterCommit, ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Scout $scout;
    public array $instance_data;
    /**
     * Create a new event instance.
     */
    public function __construct(Scout $scout)
    {
        $this->scout = $scout;
        $this->instance_data = [];
        foreach ($this->scout->instances as $i) {
            $this->instance_data[$i->id] = $i->pivot->instance_count;
        }
    }

    public function broadcastAs(): string
    {
        return 'UpdateInstanceCounts';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("scouts.{$this->scout->slug}"),
            new Channel("scouts.{$this->scout->slug}.{$this->scout->collaborator_password}"),
        ];
    }
}
