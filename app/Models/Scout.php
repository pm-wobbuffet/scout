<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sqids\Sqids;

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
            'instance_data'     =>  'array',
            'point_data'        =>  'array',
            'old_custom_points'     =>  'array',
            'mob_status'        =>  'array',
            'scouts_old'        =>  'array',
            'occupied_points'   =>  'array',
            'finalized_at'      =>  'datetime',
        ];
    }


    /* Relations */

    public function updates(): HasMany
    {
        return $this->hasMany(ScoutUpdate::class);
    }

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
}
