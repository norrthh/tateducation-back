<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\SubLesson;
use App\Models\Task;
use App\Models\User;
use App\Models\UserLessonsCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoContentSeedr extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lessons = [
            [
                'slug' => 'greetings',
                'name' => 'Приветствия',
                'description' => 'Базовые фразы вежливости и приветствия.',
                'image' => '/images/lessons/greetings.png',
                'subs' => [
                    ['slug' => 'greetings-translation', 'name' => 'Переводы приветствий', 'description' => 'Перевод RU → TT', 'sort' => 10],
                    ['slug' => 'greetings-choice', 'name' => 'Выбор значений', 'description' => 'Выбор правильного варианта', 'sort' => 20],
                    ['slug' => 'greetings-grammar', 'name' => 'Грамматика приветствий', 'description' => 'Падежи и формы', 'sort' => 30],
                ],
            ],
            [
                'slug' => 'family',
                'name' => 'Семья',
                'description' => 'Слова и конструкции на тему семьи.',
                'image' => '/images/lessons/family.png',
                'sort' => 20,
                'subs' => [
                    ['slug' => 'family-vocabulary', 'name' => 'Словарный запас', 'description' => 'Родственные связи и обращения', 'sort' => 10],
                    ['slug' => 'family-grammar', 'name' => 'Грамматика', 'description' => 'Притяжательные формы', 'sort' => 20],
                    ['slug' => 'family-practice', 'name' => 'Практика', 'description' => 'Диалоги и мини-тексты', 'sort' => 30],
                ],
            ],
            [
                'slug' => 'food',
                'name' => 'Еда',
                'description' => 'Продукты, блюда, заказы в кафе.',
                'image' => '/images/lessons/food.png',
                'sort' => 30,
                'subs' => [
                    ['slug' => 'food-vocabulary', 'name' => 'Словарный запас', 'description' => 'Названия продуктов и блюд', 'sort' => 10],
                    ['slug' => 'food-dialogues', 'name' => 'Диалоги', 'description' => 'В кафе и магазине', 'sort' => 20],
                    ['slug' => 'food-culture', 'name' => 'Культура', 'description' => 'Национальные блюда', 'sort' => 30],
                ],
            ],
        ];

        foreach ($lessons as $L) {
            $lesson = Lesson::updateOrCreate(
                ['name' => $L['name']],
                [
                    'name' => $L['name'],
                    'description' => $L['description'],
                    'image' => $L['image'],
                    'is_active' => true,
                ]
            );

            foreach ($L['subs'] as $S) {
                SubLesson::updateOrCreate(
                    [
                        'lesson_id' => $lesson->id,
                    ],
                    [
                        'name' => $S['name'],
                    ]
                );
            }
        }

        // ---------- 2) (Опционально) Привязать TASKS к подурокам ----------
        if (Schema::hasTable('tasks')) {
            // базовые данные задач (если их ещё нет)
            $seedTasks = [
                // INPUT
                ['type' => 'input', 'ru' => 'Привет', 'answers' => ['Сәлам', 'Сәлам!'], 'sort' => 10],
                ['type' => 'input', 'ru' => 'Здравствуйте', 'answers' => ['Исәнмесез', 'Исәнмесез!'], 'sort' => 20],
                ['type' => 'input', 'ru' => 'Спасибо', 'answers' => ['Рәхмәт', 'Рәхмәт!'], 'sort' => 30],
                ['type' => 'input', 'ru' => 'Доброе утро', 'answers' => ['Хәерле иртә', 'Хәерле иртә!'], 'sort' => 40],
                ['type' => 'input', 'ru' => 'Добрый день', 'answers' => ['Хәерле көн', 'Хәерле көн!'], 'sort' => 50],
                ['type' => 'input', 'ru' => 'Добрый вечер', 'answers' => ['Хәерле кич', 'Хәерле кич!'], 'sort' => 60],
                ['type' => 'input', 'ru' => 'Доброй ночи', 'answers' => ['Хәерле төн', 'Хәерле төн!'], 'sort' => 70],
                ['type' => 'input', 'ru' => 'Да / Нет', 'answers' => ['Әйе / Юк', 'Әйе/Юк', 'Әйе - Юк'], 'sort' => 80],

                // CHOICE
                ['type' => 'choice', 'ru' => '«Хәерле иртә!»', 'options' => ['Доброе утро', 'Спасибо', 'До свидания', 'Пожалуйста'], 'correct_index' => 0, 'sort' => 90],
                ['type' => 'choice', 'ru' => 'Выберите корректную форму с местным падежом (место): «… (в школе)»', 'tt' => 'мәктәп__', 'options' => ['мәктәпдә', 'мәктәпта', 'мәктәптә', 'мәктәпда'], 'correct_index' => 2, 'sort' => 110],
                ['type' => 'choice', 'ru' => 'Укажите правильную форму направления: «Еду в Казань»', 'tt' => 'Казан__ барам', 'options' => ['Казанга', 'Казангә', 'Казанка', 'Казанкә'], 'correct_index' => 0, 'sort' => 120],
            ];

            $task = SubLesson::query()->first()->id;

            foreach ($seedTasks as $row) {
                Task::query()->create([
                    'sub_lessons_id' => $task,
                    ... $row
                ]);
            }
        }
    }
}
