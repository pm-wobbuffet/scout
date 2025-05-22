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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PointOccupancyChanged implements ShouldDispatchAfterCommit, ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private readonly Scout $scout;
    public readonly array $points;
    public readonly int $zone_id;
    public readonly int $instance_number;

    /**
     * Create a new event instance.
     */
    public function __construct(Scout $scout, Collection $points, int $zone_id, int $instance_number)
    {
        $this->scout = $scout;
        $this->points = $points->values()->toArray();
        $this->zone_id = $zone_id;
        $this->instance_number = $instance_number;
    }

    public function broadcastAs(): string
    {
        return 'UpdatePointOccupancy';
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
