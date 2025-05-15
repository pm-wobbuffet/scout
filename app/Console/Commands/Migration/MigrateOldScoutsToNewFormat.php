<?php

namespace App\Console\Commands\Migration;

use App\Models\Scout;
use App\Models\ScoutCustomPoint;
use App\Models\SpawnPoint;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateOldScoutsToNewFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maps:migrate-scouts {scout?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old V1 scouts to their new V2 version';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        if($this->argument('scout')) {
            $scout = Scout::whereId($this->argument('scout'))->firstOrFail();
            if($scout->instance_data) 
            {
                foreach($scout->instance_data as $zone_id => $instance_count) 
                {
                    if($instance_count > 1) {
                        DB::table('scout_zone_instance_counts')
                        ->upsert([
                            'scout_id'          => $scout->id,
                            'zone_id'           => $zone_id,
                            'instance_count'    => $instance_count,
                            'created_at'        => Carbon::now(),
                            'updated_at'        => Carbon::now(),
                        ],
                        ['zone_id', 'instance_count', 'updated_at', 'created_at'],
                        ['instance_count', 'updated_at']
                        );
                    }
                }
            }
            if($scout->occupied_points) {
                foreach($scout->occupied_points as $point_id => $instances) {
                    foreach($instances as $instance => $is_occupied) {
                        $p = SpawnPoint::whereId($point_id)->firstOrFail();
                        DB::table('scout_points')
                        ->upsert([
                            'scout_id'          => $scout->id,
                            'zone_id'           => $p->zone_id,
                            'point_type'        => 'spawn_point',
                            'point_id'          => $point_id,
                            'instance_number'   => $instance,
                            'mob_id'            => null,
                            'created_at'        => Carbon::now(),
                            'updated_at'        => Carbon::now(),
                        ],
                        ['scout_id', 'zone_id', 'point_type', 'point_id', 
                        'instance_number','mob_id','created_at', 'updated_at'],
                        ['mob_id', 'updated_at']);
                    }
                }
            }
            // Convert custom_points to database stored custom points
            // Need to reassign the ID and keep a mapping
            if($scout->custom_points) {
                foreach($scout->custom_points as $point) {
                    //$this->info(var_dump($point));
                    $s = DB::table('scout_custom_points')
                    ->upsert([
                        'scout_id'      => $scout->id,
                        'zone_id'       => $point['zone_id'],
                        'x'             => $point['x'],
                        'y'             => $point['y'],
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now(),
                    ],
                    ['scout_id', 'zone_id', 'x', 'y'],
                    ['updated_at']
                    );
                }
            }
            $this->info('Scout retrieved');
        }
    }
}
