<?php

namespace App\Events;

use App\Models\Scout;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoutReportModified
{
    use Dispatchable, SerializesModels;

    public Scout $scout;
    public array $details;
    /**
     * Create a new event instance.
     */
    public function __construct(Scout $scout, array $details)
    {
        $this->scout = $scout;
        $this->details = $details;
    }
}
