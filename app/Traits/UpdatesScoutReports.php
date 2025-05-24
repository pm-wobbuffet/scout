<?php

namespace App\Traits;

use App\Models\Scout;

trait UpdatesScoutReports
{
    public function authorizeUpdate(Scout $scout, string $password) 
    {
        if(!$password || ($scout->collaborator_password !== $password)) {
            abort(403, 'Unauthorized action.');
        }

        if($scout && $scout->finalized_at !== null) {
            abort(403, 'Unauthorized action');
        }
    }
}
