<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'sub_lessons_id', 'type', 'ru', 'tt', 'audio', 'answers', 'options', 'correct_index', 'sort',
    ];

    protected $casts = [
        'answers' => 'array',
        'options' => 'array',
        'correct_index' => 'integer',
        'sort' => 'integer',
    ];
}
