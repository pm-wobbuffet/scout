<?php

namespace Database\Seeders;

use App\Models\SpawnPoint;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SpawnPointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Allow mass assignment while seeding
        Model::unguard(true);
        $p = File::json(resource_path('json/spawn_points_hunthelper.json'));

        foreach ($p as $zone) {
            $db_zone = Zone::where('id', $zone['MapID'])->firstOrFail();
            foreach ($zone['Positions'] as $point) {
                SpawnPoint::firstOrCreate([
                    'zone_id'   => $db_zone->id,
                    'x'         => $point['X'],
                    'y'         => $point['Y'],
                ]);
            }
        }
    }
}
