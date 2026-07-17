<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $table = 'calc_components';
    protected $fillable = ['name', 'standar', 'optimal', 'premium', 'order'];
}
