<?php

namespace App\Events\Scout;

use App\Models\Scout;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MetaUpdated implements ShouldDispatchAfterCommit, ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private readonly Scout $scout;
    public readonly string $title;
    public readonly array $scouts;
    /**
     * Create a new event instance.
     */
    public function __construct(Scout $scout, string|null $title, array|Collection $scouts)
    {
        $this->scout = $scout;
        $this->title = $title ?? '';
        if ($scouts instanceof Collection) {
            $scouts = $scouts->toArray();
        }
        $this->scouts = $scouts;
    }

    public function broadcastAs(): string
    {
        return 'UpdateMeta';
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
