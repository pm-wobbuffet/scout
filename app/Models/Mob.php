<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mob extends Model
{
    protected function casts(): array
    {
        return [
            'names' =>  'array',
        ];
    }
}
