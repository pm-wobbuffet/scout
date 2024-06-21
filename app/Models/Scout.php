<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sqids\Sqids;

class Scout extends Model
{
    protected   $guarded = ['id'];
    protected   $hidden = ['collaborator_password'];

    protected static function booted(): void
    {
        static::created(function(Scout $scout) {
            // Create an sqid ID to serve as a slug for the submission
            $sqids = new Sqids(minLength: 10, alphabet: env('SQID_ALPHABET'));
            $scout->slug = $sqids->encode([$scout->id]);
            $scout->save();
        });
    }

    protected function casts(): array
    {
        return [
            'instance_data' =>  'array',
            'point_data'    =>  'array',
        ];
    }


    /* Relations */

    public function updates(): HasMany
    {
        return $this->hasMany(ScoutUpdate::class);
    }
}
