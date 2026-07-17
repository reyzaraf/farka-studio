<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class SizeTier extends Model
{
    protected $table = 'calc_size_tiers';
    protected $fillable = ['key', 'name', 'description', 'order'];
}
