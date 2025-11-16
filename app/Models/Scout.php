<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sqids\Sqids;

/**
 * @property int $id
 * @property string|null $slug
 * @property string|null $collaborator_password The password to authorize changes to this scouting report
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $finalized_at
 * @property string|null $title
 * @property int $version
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScoutCustomPoint> $custom_points
 * @property-read int|null $custom_points_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScoutDeadMob> $dead_mobs
 * @property-read int|null $dead_mobs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Zone> $instances
 * @property-read int|null $instances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScoutPoint> $occupied_pts
 * @property-read int|null $occupied_pts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScoutPoint> $points
 * @property-read int|null $points_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScoutScouter> $scouts
 * @property-read int|null $scouts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScoutUpdate> $updates
 * @property-read int|null $updates_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout whereCollaboratorPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout whereFinalizedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scout whereVersion($value)
 * @mixin \Eloquent
 */
class Scout extends Model
{
    protected   $guarded = ['id'];
    protected   $hidden = ['collaborator_password'];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Scout $scout) {
            if (is_null($scout->title)) {
                $scout->title = '';
            }
        });

        static::created(function (Scout $scout) {
            // Create an sqid ID to serve as a slug for the submission
            $sqids = new Sqids(minLength: 10, alphabet: env('SQID_ALPHABET'));
            $scout->slug = $sqids->encode([$scout->id]);
            $scout->collaborator_password = str(bin2hex(random_bytes(4)));
            $scout->save();
        });
    }

    protected function casts(): array
    {
        return [
            //'instance_data'     =>  'array',
            //'point_data'        =>  'array',
            //'old_custom_points'     =>  'array',
            //'mob_status'        =>  'array',
            //'scouts_old'        =>  'array',
            //'occupied_points'   =>  'array',
            'finalized_at'      =>  'datetime',
        ];
    }


    /* Relations */

    public function dead_mobs(): HasMany
    {
        return $this->hasMany(ScoutDeadMob::class);
    }

    public function instances(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'scout_zone_instance_counts')
            ->withPivot(['instance_count'])->withTimestamps();
    }

    public function points(): HasMany
    {
        return $this->hasMany(ScoutPoint::class);
    }

    public function custom_points(): HasMany
    {
        return $this->hasMany(ScoutCustomPoint::class);
    }

    public function occupied_pts(): HasMany
    {
        return $this->hasMany(ScoutPoint::class)
            ->whereNull('mob_id');
    }

    public function scouts(): HasMany
    {
        return $this->hasMany(ScoutScouter::class)->orderBy('id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ScoutVersion::class);
    }

    public function getZoneInstanceCount($zone_id)
    {
        if (!$this->relationLoaded('instances')) {
            $this->load('instances');
        }
        $f = $this->instances->where('id', $zone_id)->first();
        if (!$f) {
            // No override, was 1 at time of creation
            return 1;
        }
        return $f->pivot->instance_count;
    }
}
