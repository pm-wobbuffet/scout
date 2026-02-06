<?php

namespace App\Listeners;

use App\Events\ScoutReportModified;
use App\Http\Resources\ScoutVersionCompactResource;
use App\Models\ScoutVersion;
use Carbon\Carbon;
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
        $lv = $event->scout->versions()->orderBy('id', 'DESC')->first();
        // Throttle creation of report version rows to prevent excessive hits on that table
        if ($lv !== null && $lv->created_at >= Carbon::now()->subSeconds(config('app.scout.version_history_lockout', 10))) {
            return;
        }

        $scout = $event->scout->load(['points', 'scouts', 'dead_mobs', 'custom_points', 'instances']);
        $scout->versions()->create([
            'scout_details' => $scout->toResource(ScoutVersionCompactResource::class),
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
