<?php

namespace App\Http\Controllers;

use App\Events\Scout\Finalized;
use App\Events\Scout\InstanceCountsUpdated;
use App\Http\Requests\Scout\UpdateInstanceCountRequest;
use App\Models\Scout;
use App\Traits\UpdatesScoutReports;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class ScoutController extends Controller
{
    use UpdatesScoutReports;

    public function updateInstances(UpdateInstanceCountRequest $request, Scout $scout, string $password)
    {
        $this->authorizeUpdate($scout, $password);
        // $counts = [];
        // foreach ($request->validated('instance_data') as $zone_id => $instance_count) {
        //     $instance_count = ($instance_count < 1) ? 1 : $instance_count;
        //     $counts[] = ['zone_id' => $zone_id, 'instance_count' => $instance_count];
        // }
        //$scout->instances()->sync($counts);
        $scout->instances()->sync($request->validated('instance_data'));
        broadcast(new InstanceCountsUpdated($scout))->toOthers();
        return response()->json($scout->instances);
    }

    public function finalize(Scout $scout, string $password = ''): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeUpdate($scout, $password);

        $scout->update([
            'finalized_at' => Carbon::now(),
        ]);
        broadcast(new Finalized($scout))->toOthers();
        return to_route('scout.view', [$scout]);
    }

    public function clone(Request $request, Scout $scout)
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
        if ($sc) {
            return redirect()->route('scout.view', [$sc->slug, $sc->collaborator_password])
                ->with(['newly_created' => true]);
        }
    }
}
