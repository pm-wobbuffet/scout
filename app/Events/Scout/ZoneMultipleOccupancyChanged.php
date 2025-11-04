<?php

namespace App\Events\Scout;

use App\Models\Scout;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Collection;

class ZoneMultipleOccupancyChanged implements ShouldDispatchAfterCommit, ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private readonly Scout $scout;
    public readonly array $points;
    public readonly array $custom_points;
    public readonly array $zonelist;

    /**
     * Create a new event instance.
     */
    public function __construct(Scout $scout, mixed $points, array $custom_points, $zonelist)
    {
        $this->scout = $scout;
        $this->points = $points->toArray();
        $this->custom_points = $custom_points;
        $this->zonelist = $zonelist;
    }

    public function broadcastAs(): string
    {
        return 'UpdateZonesOccupancy';
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
