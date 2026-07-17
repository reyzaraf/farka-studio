<?php

namespace App\Models\Calc;

use Illuminate\Database\Eloquent\Model;

class RoomArea extends Model
{
    protected $table = 'calc_room_areas';
    protected $fillable = ['room_id', 'size_tier_id', 'panjang', 'lebar', 'area'];
    protected $casts = ['panjang' => 'float', 'lebar' => 'float', 'area' => 'float'];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function sizeTier()
    {
        return $this->belongsTo(SizeTier::class, 'size_tier_id');
    }
}
