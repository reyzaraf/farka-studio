<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'calc_rooms';
    protected $fillable = ['category', 'name', 'order'];

    public function areas()
    {
        return $this->hasMany(RoomArea::class, 'room_id');
    }
}
