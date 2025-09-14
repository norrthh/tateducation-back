<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserServices
{
    public function authentificate(
        int    $userId,
        string $fullName,
        string $avatar,
        string $username,
    ): array
    {
        $userFind = User::query()->where('telegram_id', $userId)->first();

        $user = [
            'name' => $fullName,
            'telegram_id' => $userId,
//            'username' => $username,
            'telegram_photo_url' => $avatar,
        ];

        if (!$userFind) {
            User::query()->create($user);
        } else {
            User::query()->where('telegram_id', $userId)->update($user);
        }

        $user = User::query()->where('telegram_id', $userId)->first();

        return [
            'token' => $user->createToken('authToken')->plainTextToken,
            'user' => $user,
            'data' => $this->getMainData($user->id)
        ];
    }

    protected function getMainData($userId): array
    {
        $lessons = DB::table('lessons')
            ->where('is_active', true)
            ->orderBy('sort', 'asc')
            ->select(['id', 'name', 'image', 'sort'])
            ->get();

        $subLessonsByLesson = DB::table('sub_lessons')
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->orderBy('id', 'asc') // sort поля нет — используем id
            ->select(['id', 'lesson_id', 'name'])
            ->get()
            ->groupBy('lesson_id');

        // 3) Одним махом тянем ULC по всем сабам пользователя
        $allSubIds = $subLessonsByLesson->flatten(1)->pluck('id')->unique()->values();
        $ulc = DB::table('user_lessons_categories')
            ->where('users_id', $userId)
            ->whereIn('sub_lessons_id', $allSubIds)
            ->select(['id', 'users_id', 'sub_lessons_id', 'score', 'created_at', 'updated_at'])
            ->get()
            ->keyBy('sub_lessons_id');

        // 4) Выбираем "текущий" lesson по твоему правилу
        $chosen = null;

        // 4.1) Первый, где НЕ на все сабы есть записи в ULC (coverage < total)
        foreach ($lessons as $lesson) {
            $subs = $subLessonsByLesson->get($lesson->id, collect());
            if ($subs->isEmpty()) continue;

            $covered = $subs->filter(fn($s) => $ulc->has($s->id))->count();
            if ($covered < $subs->count()) { // <- твоя проверка "не на все подкатегория есть id"
                $chosen = $lesson;
                break;
            }
        }

        // 4.2) Если все сабы у всех уроков хотя бы "созданы" в ULC — ищем первый, где не пройдено по порогу
        if (!$chosen) {
            foreach ($lessons as $lesson) {
                $subs = $subLessonsByLesson->get($lesson->id, collect());
                if ($subs->isEmpty()) continue;

                $hasNotPassed = $subs->contains(function ($s) use ($ulc) {
                    $score = (int)($ulc->get($s->id)->score ?? 0);
                    return $score < 1;
                });

                if ($hasNotPassed) {
                    $chosen = $lesson;
                    break;
                }
            }
        }

        // 4.3) Если всё пройдено — просто первый по sort
        if (!$chosen) {
            $chosen = $lessons->first();
        }

        if (!$chosen) {
            return [
                'data' => [
                    'lesson' => null,
                    'sub_lessons' => [],
                    'unpassed_list' => [],
                    'next_unpassed' => null,
                    'progress' => [
                        'overall' => [
                            'percent' => 0,
                            'passed' => false
                        ],
                        'by_category' => [
                            'grammar' => [
                                'label' => 'Грамматика',
                                'percent' => 0,
                                'passed' => false
                            ],
                            'writing' => ['label' => 'Письмо', 'percent' => 0, 'passed' => false],
                        ],
                    ],
                ],
            ];
        }

        // 5) Формируем ответ для выбранной главы
        $lessonArr = [
            'id' => (int)$chosen->id,
            'name' => $chosen->name,
            'image' => $chosen->image,
        ];

        $subs = $subLessonsByLesson->get($chosen->id, collect());

        // простая категоризация по названию (как раньше)
        $categorize = function (string $name): string {
            $n = mb_strtolower($name);
            if (str_contains($n, 'граммат')) return 'grammar';
            if (str_contains($n, 'ауди') || str_contains($n, 'слуш')) return 'listening';
            if (str_contains($n, 'пись') || str_contains($n, 'пиш')) return 'writing';
            return 'grammar';
        };

        $subLessons = [];
        $unpassed = [];
        $cats = [
            'grammar' => ['label' => 'Грамматика', 'sum' => 0, 'cnt' => 0, 'allPassed' => true],
            'listening' => ['label' => 'Аудирование', 'sum' => 0, 'cnt' => 0, 'allPassed' => true],
            'writing' => ['label' => 'Письмо', 'sum' => 0, 'cnt' => 0, 'allPassed' => true],
        ];

        foreach ($subs as $s) {
            $ul = $ulc->get($s->id);
            $score = (int)($ul->score ?? 0);
            $percent = max(0, min(100, $score));
            $passed = $percent >= 1;
            $catKey = $categorize($s->name);

            $cats[$catKey]['sum'] += $percent;
            $cats[$catKey]['cnt'] += 1;
            if (!$passed) $cats[$catKey]['allPassed'] = false;

            $item = [
                'id' => (int)$s->id,
                'name' => $s->name,
                'percent' => $percent,
                'passed' => $passed,
                'category' => $catKey,
                'user_lessons_category' => [
                    'exists' => (bool)$ul,
                    'score' => $ul->score ?? null,
                    'created_at' => $ul->created_at ?? null,
                    'updated_at' => $ul->updated_at ?? null,
                ],
            ];
            $subLessons[] = $item;

            if (!$passed) {
                $unpassed[] = ['id' => $item['id'], 'name' => $item['name']];
            }
        }

        $nextUnpassed = $unpassed[0] ?? null;

        // агрегаты для карточек
        $byCategory = [];
        $overallSum = 0;
        $overallCnt = 0;
        $overallAllPassed = true;
        foreach ($cats as $key => $c) {
            $p = $c['cnt'] ? (int)round($c['sum'] / $c['cnt']) : 0;
            $byCategory[$key] = ['label' => $c['label'], 'percent' => $p, 'passed' => $c['cnt'] > 0 ? $c['allPassed'] : false];
            $overallSum += $c['sum'];
            $overallCnt += $c['cnt'];
            if (!$c['allPassed']) $overallAllPassed = false;
        }
        $overallPercent = $overallCnt ? (int)round($overallSum / $overallCnt) : 0;

        return [
            'lesson' => $lessonArr,
            'sub_lessons' => $subLessons,
            'unpassed_list' => $unpassed,
            'next_unpassed' => $nextUnpassed,
            'progress' => [
                'overall' => ['percent' => $overallPercent, 'passed' => $overallAllPassed],
                'by_category' => $byCategory,
            ],
        ];
    }
}
