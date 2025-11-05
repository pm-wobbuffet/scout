<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpawnPoint extends Model
{
    use SoftDeletes;

    public $table = 'spawn_points';

    protected $hidden = ['updated_at', 'deleted_at', 'created_at'];

    public function valid_mobs(): BelongsToMany
    {
        return $this->belongsToMany(Mob::class, 'mobs_spawn_points')
            ->withTimestamps();
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    // Always return 'spawn_point" as the point_type on these models
    // makes inserting into the ScoutSpawnPoint table easier, since
    // custom spawn points will have a similar entry
    protected function pointType(): Attribute
    {
        return Attribute::make(
            get: fn() => "spawn_point",
        );
    }
}
