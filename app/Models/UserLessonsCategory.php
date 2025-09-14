<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLessonsCategory extends Model
{
    protected $fillable = [
        'users_id', 'sub_lessons_id', 'score'
    ];

    public function subLesson(): BelongsTo
    {
        return $this->belongsTo(SubLesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
