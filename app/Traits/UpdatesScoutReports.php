<?php

namespace App\Traits;

use App\Events\Scout\MetaUpdated;
use App\Models\Scout;

trait UpdatesScoutReports
{
    public function authorizeUpdate(Scout $scout, string $password)
    {
        if (!$password || ($scout->collaborator_password !== $password)) {
            abort(403, 'Unauthorized action.');
        }

        if ($scout && $scout->finalized_at !== null) {
            abort(403, 'Unauthorized action');
        }
    }

    public function addScouterToScoutReport(Scout $scout, string $reporter)
    {
        if (!$reporter || $reporter === '') return;
        // If they already exist in the scout list, can return early
        if ($scout->scouts()->where('scout_name', '=', $reporter)->count() > 0) return;
        $scout->scouts()->create(['scout_name' => $reporter]);
        broadcast(new MetaUpdated(
            $scout,
            $scout->title,
            $scout->scouts,
        ));
    }
}
