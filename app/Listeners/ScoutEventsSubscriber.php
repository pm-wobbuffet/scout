<?php

namespace App\Listeners;

use App\Events\ScoutAssignMob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;

class ScoutEventsSubscriber
{

    public function handleScoutAssignMobEvent(ScoutAssignMob $event): void
    {

    }

    public function subscribe(Dispatcher $events):void 
    {
        $events->listen(
            ScoutAssignMob::class,
            [self::class, 'handleScoutAssignMobEvent']
        );
    }
}
