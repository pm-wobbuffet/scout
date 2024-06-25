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
        $zone_number = 0; // Used for assigning priority based on json file order
        foreach ($p as $zone) {
            $zone_number++;
            $mId = intval($zone['map']);
            $z = Zone::updateOrCreate(
                ['id' => $zone['id']],
                [
                    'name'              =>  $zone['name'],
                    'map_id'            =>  $zone['map'],
                    'default_instances' =>  1,
                    'expansion_id'      =>  intval($zone['version']) + 2,
                    'size_factor'       =>  $zone['size_factor'],
                    'max_coord_size'    =>  round(41 / ($zone['size_factor'] / 100), 1, PHP_ROUND_HALF_DOWN),
                    'sort_priority'     =>  $zone_number * 10,
                    'names'             =>  [
                        'en'            =>  $zone['name'],
                        'ja'            =>  $zone['name_ja'],
                        'fr'            =>  $zone['name_fr'],
                        'de'            =>  $zone['name_de'],
                    ]
                ]
            );
            foreach ($zone['mobs'] as $mob) {
                $m = Mob::updateOrCreate(
                    ['id'   =>  $mob['id']],
                    [
                        'name'          =>  $mob['name'],
                        'bNpcBase'      =>  $mob['npcbase'],
                        'rank'          =>  $mob['rank'],
                        'zone_id'       =>  $z->id,
                        'mob_index'     =>  $mob['mob_index'],
                        'names'             =>  [
                            'en'            =>  $mob['name'],
                            'ja'            =>  $mob['name_ja'],
                            'fr'            =>  $mob['name_fr'],
                            'de'            =>  $mob['name_de'],
                        ]
                    ]
                );
            }
            foreach ($zone['aetherytes'] as $tp) {
                $a = Aetheryte::updateOrCreate(
                    [
                        'zone_id'   =>  $z->id,
                        'x'         =>  $tp['x'],
                        'y'         =>  $tp['y'],
                        'icon'      =>  $tp['type'],
                    ],
                    [
                        'name'      =>  $tp['name'],
                        'names'             =>  [
                            'en'            =>  $tp['name'],
                            'ja'            =>  $tp['name_ja'],
                            'fr'            =>  $tp['name_fr'],
                            'de'            =>  $tp['name_de'],
                        ]
                    ]
                );
            }
        }
    }
}
