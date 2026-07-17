<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    protected $table = 'calc_allocations';
    protected $fillable = ['category', 'label', 'percentage', 'is_base', 'is_default', 'note', 'order'];
    protected $casts = ['percentage' => 'float', 'is_base' => 'boolean', 'is_default' => 'boolean'];
}
