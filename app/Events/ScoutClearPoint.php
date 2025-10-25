<?php

namespace App\Events;

use App\Http\Resources\ScoutCustomPointResource;
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

class ScoutClearPoint implements ShouldDispatchAfterCommit, ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private readonly Scout $scout;
    public readonly int $id;
    public readonly string $point_type;
    public readonly int $instance_number;

    public readonly array $custom_points;

    /**
     * Create a new event instance.
     */
    public function __construct(Scout $scout, int $point_id, string $point_type, int $instance_number)
    {
        $scout->load(['custom_points']);
        $this->scout = $scout;
        $this->id = $point_id;
        $this->point_type = $point_type;
        $this->instance_number = $instance_number;
        $this->custom_points = collect(ScoutCustomPointResource::collection($scout->custom_points))->toArray();
    }

    public function broadcastAs(): string
    {
        return 'ScoutClearPoint';
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
