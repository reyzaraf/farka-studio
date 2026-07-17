<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class FactorGroup extends Model
{
    protected $table = 'calc_factor_groups';
    protected $fillable = ['key', 'name', 'order'];

    public function options()
    {
        return $this->hasMany(FactorOption::class, 'factor_group_id')->orderBy('order');
    }
}
