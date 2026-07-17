<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class BuildingType extends Model
{
    protected $table = 'calc_building_types';
    protected $fillable = ['key', 'name', 'price_per_m2', 'order'];
    protected $casts = ['price_per_m2' => 'integer'];
}
