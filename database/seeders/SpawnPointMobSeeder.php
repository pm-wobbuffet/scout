<?php

namespace Database\Seeders;

use App\Models\SpawnPoint;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpawnPointMobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a map of zones and mob ids
        $zonemap = [];
        $zones = Zone::query()
        ->with(['mobs'])
        ->get();
        foreach($zones as $zone) {
            $zonemap[$zone->id] = [];
            foreach($zone->mobs as $mob) {
                $zonemap[$zone->id][$mob->mob_index] = $mob->id;
            }
        }

        if( ($handle = fopen(resource_path('csv/SPAWN_POINT_MOBS.csv'), 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Column 0 = zone_id
                // Column 5 = x, Column 6 = y, 
                // Column 7 = valid mobs in 1|2 or 1 or 2 format
                $zId = $data[0];
                if($zId == 'id') continue;
                $x = $data[5];
                $y = $data[6];
                if($data[7] != '') {
                    $mobs = [];
                    $pieces = explode('|', $data[7]);
                    foreach($pieces as $p) {
                        $mobs[] = $zonemap[$zId][$p];
                    }
                    SpawnPoint::where('x', $x)
                    ->where('y', $y)
                    ->where('zone_id', $zId)
                    ->first()
                    ->valid_mobs()->sync($mobs);
                }
            }
        }

        // Soft delete any spawn points with no valid mobs
        SpawnPoint::query()
        ->whereDoesntHave('valid_mobs')
        ->delete();
    }
}
