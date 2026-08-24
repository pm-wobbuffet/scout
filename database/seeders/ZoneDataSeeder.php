<?php

namespace Database\Seeders;

use App\Models\Aetheryte;
use App\Models\Mob;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ZoneDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Allow mass assignment while seeding
        Model::unguard(true);

        $data = File::json(resource_path('json/zones.json'));
        if (!$data) {
            Log::error('Zone json data corrupted - re-export from the Hunt Extractor');
        }
        $zone_number = 0;
        foreach ($data as $zone) {
            $zone_number++;
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
                    'allow_custom_points' => intval($zone['version']) == 5 ? true : false,
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
                        'bNpcBase'      =>  $mob['bNpcBase'],
                        'rank'          =>  $mob['rank'],
                        'zone_id'       =>  $z->id,
                        'mob_index'     =>  $mob['index'],
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
                        'icon'      =>  $tp['icon'],
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
