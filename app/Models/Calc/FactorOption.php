<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class FactorOption extends Model
{
    protected $table = 'calc_factor_options';
    protected $fillable = ['factor_group_id', 'label', 'multiplier', 'note', 'is_default', 'order'];
    protected $casts = ['multiplier' => 'float', 'is_default' => 'boolean'];

    public function group()
    {
        return $this->belongsTo(FactorGroup::class, 'factor_group_id');
    }
}
