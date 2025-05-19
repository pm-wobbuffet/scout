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
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoutUpdateMobStatus implements ShouldDispatchAfterCommit, ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private readonly Scout $scout;
    public readonly int $mob_id;
    public readonly int $instance_number;
    public readonly bool $is_dead;

    /**
     * Create a new event instance.
     */
    public function __construct(Scout $scout, int $mob_id, int $instance_number, bool $is_dead)
    {
        $this->scout = $scout;
        $this->mob_id = $mob_id;
        $this->instance_number = $instance_number;
        $this->is_dead = $is_dead;
    }

    public function broadcastAs(): string
    {
        return 'UpdateMobStatus';
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
