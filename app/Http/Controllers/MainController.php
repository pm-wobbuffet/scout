<?php

namespace App\Http\Controllers;

use App\Events\Scout\MetaUpdated;
use App\Http\Requests\Scout\HandleImportedPointsRequest;
use App\Http\Requests\Scout\StoreScoutRequest;
use App\Http\Requests\Scout\UpdateMetaRequest;
use App\Http\Requests\Scout\UpdateOccupiedPointRequest;
use App\Http\Resources\ExpansionResource;
use App\Http\Resources\ScoutCustomPointResource;
use App\Http\Resources\ScoutResource;
use App\Http\Resources\ScoutVersionResource;
use App\Models\Expansion;
use App\Models\Scout;
use App\Models\ScoutVersion;
use App\Models\Zone;
use App\Traits\BroadcastsScoutingEvents;
use App\Traits\UpdatesScoutReports;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use function count;

class MainController extends Controller
{
    use UpdatesScoutReports, BroadcastsScoutingEvents;

    /**
     * Show a blank map for the user to start their scouting journey
     */
    public function index(): \Inertia\Response
    {
        $expansions = $this->getExpansionsData();

        return Inertia::render('Index', [
            'expac' => ExpansionResource::collection($expansions),
            // 'expac'     => $expansions,
            'defaultId' => intval(env('DEFAULT_EXPANSION_ID', 7)),
        ]);
    }

    public function getUpdates(Request $request, Scout $scout, string $password = ''): ScoutResource
    {
        $this->authorizeUpdate($scout, $password);
        $scout->load(['updates', 'dead_mobs', 'instances', 'points']);
        $scout->loadMax('updates', 'id');

        return new ScoutResource($scout);
    }

    public function updateOccupiedPoint(UpdateOccupiedPointRequest $request, Scout $scout, string $password = '')
    {
        $this->authorizeUpdate($scout, $password);
        // If the request has a mob_id (which should be null, but explicitly set)
        // we're marking as occupied, else unoccupy a spot
        if ($request->has('mob_id')) {
            $scout->occupied_pts()->updateOrCreate([
                'point_type' => $request->validated('point_type'),
                'point_id' => $request->validated('point_id'),
                'instance_number' => $request->validated('instance_number', 1),
                'zone_id' => $request->validated('zone_id'),
            ], [
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'mob_id' => null,
            ]);
        } else {
            $scout->occupied_pts()
                ->where('point_type', $request->validated('point_type'))
                ->where('point_id', $request->validated('point_id'))
                ->where('instance_number', $request->validated('instance_number', 1))
                ->where('zone_id', $request->validated('zone_id'))
                ->delete();
        }
        $points = $scout->points
            ->where('zone_id', $request->validated('zone_id'))
            ->where('instance_number', $request->validated('instance_number', 1));
        $this->PointOccupacyUpdateEvent($scout, $points, $request->validated('zone_id'), $request->validated('instance_number', 1));

        return response()->json($points);
    }

    public function handleImportedPoints(HandleImportedPointsRequest $request, Scout $scout, string $password = '')
    {
        $this->authorizeUpdate($scout, $password);
        // Remove any existing points that are associated with an entry in the zonelist submitted
        // since we're assuming the user provides a complete list of points in the zone now (occupied or A rank taken combined)
        $p = $scout->points()->whereIn(
            DB::raw("CONCAT(zone_id,'-',instance_number)"),
            $request->validated('zonelist')
        )->delete();

        // TODO: Handle meta update if a reporter was passed for this point

        $created_points = $scout->points()->createMany($request->validated('point_data'));

        $this->ScoutMultipleOccupanyUpdates($scout, $request->validated('zonelist'));
        $this->sendReportModifiedEvent($scout, ['name' => "Multiple Points Imported ({$created_points->count()})"]);

        return response()->json([
            'zonelist'          => $request->validated('zonelist'),
            'zone_points'       => $created_points,
            'custom_points'     => $scout->custom_points,
        ]);
    }

    /* Private methods */

    /**
     * Return an array of data about a scout for use in JSON responses, if requested in a non-API way
     *
     * @param  bool  $is_collaborator  - also return the collab password if true
     */
    private function generateJson(Scout $scout, bool $is_collaborator = false): array
    {
        $r = [
            'scout_id' => $scout->id,
            'instance_counts' => $scout->instance_data,
            'mob_list' => $scout->point_data,
        ];
        if ($is_collaborator) {
            $r['collaborator_password'] = $scout->collaborator_password;
        }

        return $r;
    }
}
