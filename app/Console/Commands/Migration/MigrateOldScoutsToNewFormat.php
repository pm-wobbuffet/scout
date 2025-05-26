<?php

namespace App\Console\Commands\Migration;

use App\Models\Mob;
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
        if ($this->argument('scout')) {
            $scout = Scout::whereId($this->argument('scout'))->firstOrFail();
            $this->processScout($scout);
            $this->info("Scout ID# {$scout->id} processed.");
        } else {
            if ($this->confirm("Do you really wish to process all scouts? This could take an extremely long time.")) {
                $this->info("On your own head be it");
                $count = DB::table('scouts')->where('version', 1)->count();
                $bar = $this->output->createProgressBar($count);
                $start = 0;
                Scout::where('version', 1)->orderBy('id')->chunk(50, function ($scouts) use ($bar) {
                    foreach ($scouts as $scout) {
                        // Short circuit early if a malformed point_data object exists
                        if (!is_array($scout->point_data)) {
                            $this->info("Scout {$scout->id} had a malformed point_data and was skipped");
                            continue;
                        }
                        $this->processScout($scout);
                        $scout->version = 2;
                        $scout->save();
                        $bar->advance();
                    }
                });
                $bar->finish();
            }
        }
    }

    private function processScout(Scout $scout)
    {
        if ($scout->instance_data) {
            foreach ($scout->instance_data as $zone_id => $instance_count) {
                if ($instance_count > 1) {
                    DB::table('scout_zone_instance_counts')
                        ->upsert(
                            [
                                'scout_id'          => $scout->id,
                                'zone_id'           => $zone_id,
                                'instance_count'    => $instance_count,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ],
                            ['scout_id', 'zone_id', 'instance_count', 'updated_at', 'created_at'],
                            ['instance_count', 'updated_at']
                        );
                }
            }
        }
        if ($scout->mob_status && is_array($scout->mob_status)) {
            foreach ($scout->mob_status as $mob_id => $instances) {
                foreach ($instances as $instance => $is_dead) {
                    if ($is_dead) {
                        DB::table('scout_dead_mobs')
                            ->upsert(
                                [
                                    'scout_id'          => $scout->id,
                                    'mob_id'            => $mob_id,
                                    'instance_number'   => $instance,
                                    'created_at'        => Carbon::now(),
                                    'updated_at'        => Carbon::now(),
                                ],
                                ['scout_id', 'mob_id', 'instance_number'],
                                ['updated_at']
                            );
                    }
                }
            }
        }
        if ($scout->occupied_points) {
            foreach ($scout->occupied_points as $point_id => $instances) {
                foreach ($instances as $instance => $is_occupied) {
                    $p = SpawnPoint::whereId($point_id)->first();
                    if ($p === null) {
                        // For some reason this scout report had an invalid point_id, just continue on to next
                        continue;
                    }
                    DB::table('scout_points')
                        ->upsert(
                            [
                                'scout_id'          => $scout->id,
                                'zone_id'           => $p->zone_id,
                                'point_type'        => 'spawn_point',
                                'point_id'          => $point_id,
                                'instance_number'   => $instance,
                                'mob_id'            => null,
                                'created_at'        => Carbon::now(),
                                'updated_at'        => Carbon::now(),
                            ],
                            [
                                'scout_id',
                                'zone_id',
                                'point_type',
                                'point_id',
                                'instance_number',
                                'mob_id',
                                'created_at',
                                'updated_at'
                            ],
                            ['mob_id', 'updated_at']
                        );
                }
            }
        }
        // Convert custom_points to database stored custom points
        // Need to reassign the ID and keep a mapping
        $custom_points = [];
        if ($scout->custom_points) {
            foreach ($scout->custom_points as $point) {
                if (isset($custom_points[$point['id']])) {
                    continue;
                }
                //$this->info(var_dump($point));
                $s = DB::table('scout_custom_points')
                    ->upsert(
                        [
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
                $cp = ScoutCustomPoint::query()
                    ->where('zone_id', $point['zone_id'])
                    ->where('scout_id', $scout->id)
                    ->where('x', $point['x'])
                    ->where('y', $point['y'])
                    ->firstOrFail();
                // Add to mapping
                $custom_points[$point['id']] = $cp->id;
            }
        }


        // Cycle through submitted points
        // Grab any custom points (ID < 0) and link scout_custom_point entries for them
        foreach ($scout->point_data as $zone_id => $instance_data) {
            foreach ($instance_data as $instance => $filled_points) {
                foreach ($filled_points as $point) {
                    $pid = $point['point_id'];
                    // Make sure the mob is valid. I think when doing API testing some invalid ones got entered
                    $mob = Mob::whereId($point['mob_id'])->first();
                    if ($mob === null) {
                        // Skip processing if no valid mob ID found for this
                        continue;
                    }
                    if ($pid < 1) {
                        // This was a custom point in the old data format. Grab new ID from mapping
                        if (!isset($custom_points[$pid]) || ($custom_point_id = $custom_points[$pid]) === null) {
                            // Some ancient scout reports did not yet use the custom_points field
                            // So we should make a point for it when we come across these orphans
                            try {
                                $custom_point_id = DB::table('scout_custom_points')
                                    ->insertGetId([
                                        'scout_id'      => $scout->id,
                                        'zone_id'       => $zone_id,
                                        'x'             => $point['x'],
                                        'y'             => $point['y'],
                                        'created_at'    => Carbon::now(),
                                        'updated_at'    => Carbon::now(),
                                    ]);
                            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                                // The point already existed
                                $cp = ScoutCustomPoint::query()
                                    ->where('zone_id', $zone_id)
                                    ->where('scout_id', $scout->id)
                                    ->where('x', $point['x'])
                                    ->where('y', $point['y'])
                                    ->firstOrFail();
                                $custom_point_id = $cp->id;
                            }
                            $custom_points[$point['point_id']] = $custom_point_id;
                            //$this->error("Custom point {$pid} was not found for Scout ID:{$scout->id}");
                        }
                        $spawn_point_type = 'custom_spawn_point';
                        $pid = $custom_point_id;
                    } else {
                        // This was a pre-defined point that we have in the database already
                        $spawn_point_type = 'spawn_point';
                        $pid = $point['point_id'];
                    }
                    DB::table('scout_points')
                        ->upsert([
                            'scout_id'          => $scout->id,
                            'zone_id'           => $zone_id,
                            'point_type'        => $spawn_point_type,
                            'point_id'          => $pid,
                            'instance_number'   => intval($instance) ?? 1,
                            'mob_id'            => isset($point['mob_id']) && (intval($point['mob_id']) > 0) ? intval($point['mob_id']) : null,
                            'x'                 => isset($point['x']) ? floatval($point['x']) : null,
                            'y'                 => isset($point['y']) ? floatval($point['y']) : null,
                            'created_at'    => Carbon::now(),
                            'updated_at'    => Carbon::now(),
                        ], [
                            'scout_id',
                            'zone_id',
                            'point_type',
                            'point_id',
                            'instance_number'
                        ], [
                            'updated_at',
                            'x',
                            'y'
                        ]);
                }
            }
        }

        // Were there scout names listed?
        foreach ($scout->scouts_old as $scout_name) {
            $scout->scouts()->create(
                ['scout_name' => $scout_name]
            );
        }
    }
}
