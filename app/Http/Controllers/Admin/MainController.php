<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scout;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MainController extends Controller
{
    //
    public function dashboard(Request $request)
    {
        $scouts = Scout::count();
        $multi_zone = Zone::where('default_instances', '>', 1)->count();
        $last_day = Scout::where('created_at', '>=', Carbon::now()->subDays(1))->count();
        return Inertia::render('Dashboard', [
            'total_scouts'  => $scouts,
            'multi_zones'   => $multi_zone,
            'last_day'      => $last_day,
            'last_twenty'   => Scout::orderBy('id', 'DESC')->limit(20)->get()->makeVisible(['collaborator_password']),
        ]);
    }
}
