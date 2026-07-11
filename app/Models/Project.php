<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['slug', 'title', 'category_id', 'status', 'architect', 'floor_area', 'site_area', 'stories', 'location', 'order', 'is_published'];
    protected $casts = ['is_published' => 'boolean'];
    public function contents()
    {
        return $this->hasMany(ProjectContent::class)->orderBy('order');
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
