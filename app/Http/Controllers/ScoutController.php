<?php

namespace App\Http\Controllers;

use App\Events\Scout\Finalized;
use App\Events\Scout\InstanceCountsUpdated;
use App\Http\Requests\Scout\StoreScoutRequest;
use App\Http\Requests\Scout\UpdateInstanceCountRequest;
use App\Http\Requests\Scout\UpdateMetaRequest;
use App\Http\Requests\Scout\VersionReversionRequest;
use App\Http\Resources\ScoutResource;
use App\Http\Resources\ScoutVersionResource;
use App\Models\Expansion;
use App\Models\Scout;
use App\Models\Zone;
use App\Traits\BroadcastsScoutingEvents;
use App\Traits\UpdatesScoutReports;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ScoutController extends Controller
{
    use UpdatesScoutReports, BroadcastsScoutingEvents;

    /**
     * Store a scouting report to the database
     * Sends the user to the newly created report on success
     * @param \App\Http\Requests\Scout\StoreScoutRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreScoutRequest $request)
    {
        $scout = Scout::create($request->validated());

        // Handle relations
        $this->handleCustomPoints($scout, $request->input('custom_points', []));
        $scout->points()->createMany($request->validated('points', []));
        $scout->instances()->sync($request->validated('instance_data', []));
        $scout->dead_mobs()->createMany($request->validated('dead_mobs', []));
        $scout->scouts()->createMany($request->validated('scouts', []));

        // Fire update
        $this->sendReportModifiedEvent($scout, ['name' => 'Initial Report Submission']);
        return redirect()->route('scout.view', [$scout->slug, $scout->collaborator_password])
            ->with(['newly_created' => true]);
    }

    public function view(Request $request, Scout $scout, string $password = ''): \Inertia\Response|JsonResponse
    {
        $scout->load(['dead_mobs', 'instances', 'points', 'scouts', 'custom_points', 'custom_points.zone', 'custom_points.zone.mobs']);
        if ($password && $password === $scout->collaborator_password) {
            $scout->makeVisible(['collaborator_password']);
        }

        $expansions = $this->getExpansionsData();
        $exp_totals = $this->calculateExpTotals($expansions, $scout);
        if ($request->wantsJson() || $request->has('json')) {
            return response()->json($this->generateJson($scout, $password === $scout->collaborator_password));
        }
        // Set OpenGraph settings for discord display
        $this->setOpenGraphDetails($scout, $exp_totals);

        return Inertia::render('scout/View', [
            'expac' => $expansions,
            'scout' => new ScoutResource($scout),
            'defaultId' => intval(config('app.scout.default_expansion_id', 7)),
            'ajaxRefreshInterval' => intval(config('app.scout.ajax_refresh_interval', 10000)),
            'versions' => Inertia::defer(function () use ($scout) {
                //
                return ScoutVersionResource::collection($scout->versions()->orderBy('version', 'desc')
                    ->paginate(10, ['*'], 'historypage'));
            }, 'versions')
        ]);
    }

    /**
     * Update the number of instances assigned to each zone in the scouting report
     *
     * @param UpdateInstanceCountRequest $request
     * @param Scout $scout
     * @param string $password
     * @return JsonResponse
     */
    public function updateInstances(UpdateInstanceCountRequest $request, Scout $scout, string $password): JsonResponse
    {
        $this->authorizeUpdate($scout, $password);
        $scout->instances()->sync($request->validated('instance_data'));
        broadcast(new InstanceCountsUpdated($scout))->toOthers();
        $this->sendReportModifiedEvent($scout, ['name' => "Instance Counts Updated"]);
        return response()->json($scout->instances);
    }

    /**
     * Updates the metadata for a scout request, including title and scouter list
     * @param \App\Http\Requests\Scout\UpdateMetaRequest $request
     * @param \App\Models\Scout $scout
     * @param string $password
     * @return JsonResponse
     */
    public function updateMeta(UpdateMetaRequest $request, Scout $scout, string $password): JsonResponse
    {
        $this->authorizeUpdate($scout, $password);

        $scout->title = $request->validated('title', '');
        $scout->scouts()->delete();
        foreach ($request->validated('scouts') as $scouter) {
            $scout->scouts()->updateOrCreate([
                'scout_name' => $scouter['scout_name'],
            ]);
        }
        $scout->save();
        $this->metaUpdated($scout);
        $this->sendReportModifiedEvent($scout, ['name' => 'Report Details Updated']);
        return response()->json(['success' => true]);
    }

    /**
     * Finalize a scouting report, preventing any further updates (other than Meta information)
     *
     * @param Scout $scout
     * @param string $password
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finalize(Scout $scout, string $password = ''): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeUpdate($scout, $password);

        $scout->update([
            'finalized_at' => Carbon::now(),
        ]);
        broadcast(new Finalized($scout))->toOthers();
        return to_route('scout.view', [$scout]);
    }

    /**
     * Duplicate a scouting report to allow new edits or changes to a finalized report
     *
     * @param Request $request
     * @param Scout $scout
     * @return RedirectResponse
     */
    public function clone(Request $request, Scout $scout): RedirectResponse
    {
        $sc = $scout->replicate(['collaborator_password', 'slug', 'finalized_at']);
        $sc->collaborator_password = str(bin2hex(random_bytes(4)));
        $sc->save();
        // Clone the individual relations
        $custom_point_map = [];
        foreach ($scout->custom_points as $p) {
            $i = $sc->custom_points()->create($p->toArray());
            $custom_point_map[$p->id] = $i->id;
        }

        foreach ($scout->points as $p) {
            // Map the new custom spawn point to the point_id for any applicable rows
            if ($p->point_type == 'custom_spawn_point') {
                if (array_key_exists($p->point_id, $custom_point_map)) {
                    $p->point_id = $custom_point_map[$p->point_id];
                }
            }
            $sc->points()->create($p->toArray());
        }
        foreach ($scout->instances as $i) {
            $sc->instances()->attach($i, ['instance_count' => $i->pivot->instance_count]);
        }
        foreach ($scout->scouts as $s) {
            $sc->scouts()->create($s->toArray());
        }
        foreach ($scout->dead_mobs as $d) {
            $sc->dead_mobs()->create($d->toArray());
        }
        return redirect()->route('scout.view', [$sc->slug, $sc->collaborator_password])
            ->with(['newly_created' => true]);
    }

    /**
     * Revert a scouting report to a previous numbered version.
     * This creates a new version based on the existing version, rather than truly doing a rewind, i.e. a
     * user at version 10 can revert to version "6", but this will just copy the contents of version 6 into
     * a new version 11
     *
     * @param VersionReversionRequest $request
     * @param Scout $scout
     * @param string $password
     * @return void
     */
    public function revert(VersionReversionRequest $request, Scout $scout, string $password = '')
    {
        $this->authorizeUpdate($scout, $password);
        $target_version = $scout->versions()->where('version', $request->validated('version_number'))
            ->firstOrFail();

        DB::transaction(function () use ($scout, $target_version) {
            // Reset all values to their previous versions
            $scout->scouts()->delete();
            $scout->scouts()->createMany($target_version->scout_details['scouts'] ?? []);

            $scout->custom_points()->delete();
            $scout->custom_points()->createMany($target_version->scout_details['custom_points'] ?? []);

            $scout->points()->delete();
            $scout->points()->createMany($target_version->scout_details['points'] ?? []);

            $scout->instances()->sync($target_version->scout_details['instances'] ?? []);

            $scout->dead_mobs()->delete();
            $scout->dead_mobs()->createMany($target_version->scout_details['dead_mobs'] ?? []);

            $scout->title = $target_version->scout_details['title'] ?? '';
            $scout->save();
        });

        // @todo: add websocket event to let users know the reversion happened
        $this->VersionReverted($scout);
        $this->sendReportModifiedEvent($scout, [
            'name' => "Reverted to Previous Version ({$request->validated('version_number')})",
        ]);

        return to_route('scout.view', [$scout, $password]);
    }

    /**
     * Get the total number of mobs for each expansion in the scouting report
     * Only counts expansions with found mobs
     * Used mainly for the OpenGraph display in Discord embeds
     * @param Collection<int, Expansion> $expansions
     * @param Scout $scout
     * @return string[]
     */
    private function calculateExpTotals(Collection $expansions, Scout $scout): array
    {
        $scout->load(['instances', 'points', 'points.zone' => function ($q) {
            $q->select(['id', 'expansion_id', 'name']);
        }]);
        // Cycle through expansions and zones total up found mobs and expected totals
        $ret = $expansions->reduce(function (?array $carry, Expansion $item) use ($scout) {
            $total_mobs = 0;
            $seen_mobs = $scout->points->where('zone.expansion_id', $item->id)->count();
            if ($seen_mobs > 0) {
                $total_mobs = $item->zones->reduce(function (int $carry, Zone $zone) use ($scout) {
                    $carry += ($scout->getZoneInstanceCount($zone->id) * $zone->mobs->count());
                    return $carry;
                }, 0);
                $carry[] = "{$item->abbreviation}: {$seen_mobs}/{$total_mobs}";
            }
            return $carry;
        }, []);
        return $ret;
    }

    /**
     * Add OpenGraph Meta details to the Meta package
     * Calls functions in the parent Controller class
     *
     * @param  array  $exp_totals  - Array of expansion mob totals ["ARR: 10/17", "HW: 12/12"]
     */
    private function setOpenGraphDetails(Scout $scout, array $exp_totals): void
    {
        if ($scout->title) {
            $this->setOGTitle($scout->title . ' ' . implode(', ', $exp_totals));
        } else {
            $this->setOGTitle(implode(', ', $exp_totals));
        }

        if ($scout->scouts && count($scout->scouts) > 0) {
            $this->setOGDescription('Scouted by: ' . implode(', ', $scout->scouts->pluck('scout_name')->toArray() ?? []));
        }
    }
}
