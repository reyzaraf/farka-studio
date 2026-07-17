<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Zonasi extends Model
{
    protected $table = 'calc_zonasi';
    protected $fillable = ['code', 'name', 'kdb', 'klb', 'ktb', 'rth', 'order'];
    protected $casts = ['kdb' => 'float', 'klb' => 'float', 'ktb' => 'float', 'rth' => 'float'];
}
