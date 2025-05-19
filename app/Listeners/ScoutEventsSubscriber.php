<?php

namespace App\Listeners;

use App\Events\ScoutAssignMobEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;

class ScoutEventsSubscriber
{

    public function handleScoutAssignMobEvent(ScoutAssignMobEvent $event): void
    {

    }

    public function subscribe(Dispatcher $events):void 
    {
        $events->listen(
            ScoutAssignMobEvent::class,
            [self::class, 'handleScoutAssignMobEvent']
        );
    }
}
