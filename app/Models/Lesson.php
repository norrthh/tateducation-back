<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = ['name', 'description', 'image', 'is_active'];

    public function subLessons(): HasMany
    {
        return $this->hasMany(SubLesson::class);
    }
}
