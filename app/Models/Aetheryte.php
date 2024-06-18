<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aetheryte extends Model
{
    protected   $appends = ['x_norm', 'y_norm'];
    
    public function xNorm(): Attribute
    {
        return Attribute::make(
            get: function() {
                return round( ($this->x / 42) * 100, 1) . '%';
            }
        );
    }

    public function yNorm(): Attribute
    {
        return Attribute::make(
            get: function() {
                return round( ($this->y / 42) * 100, 1) . '%';
            }
        );
    }
}
