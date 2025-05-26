<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoutScouter extends Model
{
    //
    protected $table = 'scout_scouters';
    protected $guarded = ['id'];

    public $timestamps = false;
}
