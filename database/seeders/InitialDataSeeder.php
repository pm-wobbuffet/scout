<?php

namespace Database\Seeders;

use App\Models\Aetheryte;
use App\Models\Expansion;
use App\Models\Mob;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Allow mass assignment while seeding
        Model::unguard(true);
        // Create expansions

        $arr = Expansion::firstOrCreate(['id' => 2], [
            'name' => 'A Realm Reborn',
            'abbreviation' => 'ARR',
        ]);
        $hw = Expansion::firstOrCreate(['id' => 3], [
            'name' => 'Heavensward',
            'abbreviation' => 'HW',
        ]);
        $sb = Expansion::firstOrCreate(['id' => 4], [
            'name' => 'Stormblood',
            'abbreviation' => 'SB',
        ]);
        $shb = Expansion::firstOrCreate(['id' => 5], [
            'name' => 'Shadowbringers',
            'abbreviation' => 'SHB',
        ]);
        $ew = Expansion::firstOrCreate(['id' => 6], [
            'name' => 'Endwalker',
            'abbreviation' => 'EW',
        ]);
        $dt = Expansion::firstOrCreate(['id' => 7], [
            'name' => 'Dawntrail',
            'abbreviation' => 'DT',
        ]);

        $p = File::json(resource_path('json/zones.json'));
        foreach($p as $zone) {
            $mId = intval($zone['map']);
            $z = Zone::updateOrCreate(
                ['id' => $zone['id']],
                [
                    'name'              =>  $zone['name'],
                    'map_id'            =>  $zone['map'],
                    'default_instances' =>  1,
                    'expansion_id'      =>  intval($zone['version']) + 2,
                    'size_factor'       =>  $zone['size_factor'],
                    'max_coord_size'    =>  round( 41 / ($zone['size_factor'] / 100), 1, PHP_ROUND_HALF_DOWN),
                ]
            );
            foreach($zone['mobs'] as $mob) {
                $m = Mob::firstOrCreate(
                    ['id'   =>  $mob['id']],
                    [
                        'name'          =>  $mob['name'],
                        'bNpcBase'      =>  $mob['npcbase'],
                        'rank'          =>  $mob['rank'],
                        'zone_id'       =>  $z->id,
                    ]
                );
            }
            foreach ($zone['aetherytes'] as $tp) {
                $a = Aetheryte::firstOrCreate(
                    [
                        'zone_id'   =>  $z->id,
                        'x'         =>  $tp['x'],
                        'y'         =>  $tp['y'],
                        'icon'      =>  $tp['type'],
                    ],
                    [
                        'name'      =>  $tp['name'],
                    ]
                );
            }
        }
    }
}
