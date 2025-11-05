<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPoint extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Always return 'custom_spawn_point" as the point_type on these models
    // makes inserting into the ScoutPoints table easier, since
    // spawn points will have a similar entry
    protected function pointType(): Attribute
    {
        return Attribute::make(
            get: fn() => "custom_spawn_point",
        );
    }
}
