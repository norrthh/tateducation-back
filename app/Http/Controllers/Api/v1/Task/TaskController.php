<?php

namespace App\Http\Controllers\Api\v1\Task;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Task;
use App\Models\UserLessonsCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    private const PASS_SCORE = 100;

    public function index(): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $lessons = Lesson::query()
            ->select(['id', 'name', 'description', 'image'])
            ->with([
                'subLessons:id,lesson_id,name',
            ])
            ->get();

        $doneSubIds = UserLessonsCategory::query()
            ->where('users_id', $userId)
            ->pluck('sub_lessons_id')
            ->all();

        $payload = $lessons->map(callback: function ($lesson) use ($doneSubIds) {
            $items = $lesson->subLessons->map(function ($sub) use ($doneSubIds) {
                if (Task::query()->where('sub_lessons_id', $sub->id)->exists()) {
                    $done = in_array($sub->id, $doneSubIds, true);

                    return [
                        'id' => $sub->id,
                        'title' => $sub->name,
                        'status' => $done ? 'done' : 'in-progress',
                        'icon' => $sub->name === 'Грамматика'
                            ? '/images/gramm.svg'
                            : '/images/pismo.svg',
                    ];
                }

                return null;
            })
                ->filter()
                ->values();

            // Прогресс по уроку (в процентах)
            $total = $lesson->subLessons->count();
            $doneCnt = $lesson->subLessons->whereIn('id', $doneSubIds)->count();
            $progress = $total > 0 ? (int)round($doneCnt / $total) : 0;

            return [
                'id' => $lesson->id,
                'chapter' => $lesson->name,
                'subtitle' => $lesson->description,
                'progress' => $progress,
                'cover' => '/images/bookapple.svg',
                'variant' => $progress === 100 ? 'filled' : 'outline',
                'items' => $items,
            ];
        })->values();

        return response()->json($payload);
    }

    public function getTasks($id, $chillId): JsonResponse
    {
        $tasks = Task::query()->where('sub_lessons_id', $chillId)->get();

        $filterTask = [];
        foreach ($tasks->toArray() as $task) {
            $filterTask[] = [
                ... $task,
                'correctIndex' => $task['correct_index'],
            ];
        }

        return response()->json($filterTask);
    }
}
