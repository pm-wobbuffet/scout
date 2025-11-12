<?php

namespace App\Traits;

use App\Models\Scout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Request;

trait VerifiesScoutUpdateRequests
{
    /**
     * Verify that a given request is authorized to perform an update
     * Used in the authorize() method of a few Form Requests
     * @param \App\Models\Scout $scout
     * @param FormRequest $request
     * @return bool
     */
    public function verifyPermissions(Scout $scout, FormRequest $request): bool
    {
        if (!$scout || $scout === null) {
            Log::debug("No Scouting report was found for this request, {req}", ['req' => $this]);
            return false;
        }

        // Check to make sure they supplied the correct collaborator_password
        // to prevent unauthorized users from supplying updates
        if ($scout->collaborator_password !== $request->input('collaborator_password')) {
            Log::debug("An invalid collaborator password was submitted for this request, {req}", ['req' => $this]);
            return false;
        }

        // Make sure we don't make any changes to an existing map that's finalized
        if ($scout->finalized_at !== null) {
            Log::debug("An API request was made to update a finalized scouting report, {req}", ['req' => $this]);
            return false;
        }

        return true;
    }
}
