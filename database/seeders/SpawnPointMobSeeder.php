<?php

namespace Database\Seeders;

use App\Models\SpawnPoint;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Handles the importing of zones from SPAWN_POINT_MOBS.csv. You can generate this file by exporting from your
 * favorite DB engine the following query:
 * SELECT z.id,z.name,
    (SELECT name FROM mobs m WHERE m.zone_id = z.id AND mob_index = 1) as mob1_name,
    (SELECT name FROM mobs m2 WHERE m2.zone_id = z.id AND mob_index = 2) as mob2_name,
    sp.id as spawn_point_id, sp.x, sp.y,
    valid_mobs_table.valid_mobs
    FROM zones z
    LEFT JOIN spawn_points sp ON(sp.zone_id = z.id)
    LEFT JOIN (
    SELECT sp.id, GROUP_CONCAT(mob_index ORDER BY mob_index SEPARATOR '|' ) as valid_mobs FROM spawn_points sp
    LEFT JOIN mobs_spawn_points msp ON(msp.spawn_point_id = sp.id)
    LEFT JOIN mobs ON (msp.mob_id = mobs.id)
    GROUP BY sp.id
    ) AS valid_mobs_table ON(valid_mobs_table.id = sp.id)
 * You can then fill in the valid_mobs by using a pipe delimiter. i.e 1|2 means mobs 1 and 2 are both valid for a point, "1" would mean only mob 1, etc
 * You can add new spawn points at the end just by inserting new lines with the appropriate format.
 */
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
                    /*
                    SpawnPoint::where('x', $x)
                    ->where('y', $y)
                    ->where('zone_id', $zId)
                    ->first()
                    ->valid_mobs()->sync($mobs);
                    */
                    $sp = SpawnPoint::firstOrCreate([
                        'x' => $x,
                        'y' => $y,
                        'zone_id'   => $zId,
                    ],[
                        'is_active' => 1,
                    ]
                    );
                    $sp->valid_mobs()->sync($mobs);
                }
            }
        }

        // Soft delete any spawn points with no valid mobs
        SpawnPoint::query()
        ->whereDoesntHave('valid_mobs')
        ->delete();
    }
}
