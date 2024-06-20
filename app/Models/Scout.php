<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scout extends Model
{
    protected   $guarded = ['id'];
    protected   $hidden = ['collaborator_password'];

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
