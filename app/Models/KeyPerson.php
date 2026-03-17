<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyPerson extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'role', 'image_url', 'description', 'order'];
}
