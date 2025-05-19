<?php

namespace App\Events;

use App\Models\Scout;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use stdClass;

class ScoutAssignMob implements ShouldDispatchAfterCommit, ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private readonly Scout $scout;
    public readonly array $points;
    public readonly int $zone_id;

    /**
     * Create a new event instance.
     */
    public function __construct(Scout $scout, int $zone_id, Collection $points) 
    {
        $this->scout = $scout;
        $this->points = $points->toArray();
        $this->zone_id = $zone_id;
    }


    public function broadcastAs(): string
    {
        return 'ScoutAssignMob';
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
