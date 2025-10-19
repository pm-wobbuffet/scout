<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Zone\UpdateInstanceCountRequest;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $zones = Zone::orderBy('id')->get();
        return Inertia::render('admin/Zones', ['zones' => $zones]);
    }

    public function instanceCounts(UpdateInstanceCountRequest $request)
    {
        foreach ($request->validated('instance_counts') as $zone_id => $number_of_instances) {
            Zone::where('id', $zone_id)->update([
                'default_instances' => $number_of_instances,
            ]);
        }
        return to_route('admin.zones')->with('success', 1);
    }

    public function edit(Zone $zone)
    {
        return Inertia::render('admin/Zones/Edit.vue', [
            'zone'  => $zone,
        ]);
    }
}
