<?php

namespace App\Listeners;

use App\Events\ScoutReportModified;
use App\Models\ScoutVersion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;

class ScoutEventsSubscriber implements ShouldQueue
{
    /**
     * Create a versioned history entry for a scouting report when details or points have been updated
     * @param \App\Events\ScoutReportModified $event
     * @return void
     */
    public function handleScoutReportModifiedEvent(ScoutReportModified $event): void
    {
        $scout = $event->scout->load(['points', 'scouts', 'dead_mobs', 'custom_points', 'instances']);
        $scout->versions()->create([
            'scout_details' => $scout->toArray(),
            'update_details' => $event->details,
            'user'  => $event->user,
        ]);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            ScoutReportModified::class,
            [self::class, 'handleScoutReportModifiedEvent']
        );
    }
}
