<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubLesson extends Model
{
    protected $fillable = ['lesson_id', 'name'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
