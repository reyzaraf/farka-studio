<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'calc_settings';
    protected $fillable = ['key', 'value', 'note'];

    public static function value(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();
        return $row ? $row->value : $default;
    }
}
