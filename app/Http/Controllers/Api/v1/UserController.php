<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLessonsCategory;
use App\Services\User\UserServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Vlsv\TelegramInitDataValidator\Validator\InitData;

class UserController extends Controller
{
    protected UserServices $userServices;
    public function __construct(UserServices $userServices) {
        $this->userServices = $userServices;
    }

    public function auth(Request $request): JsonResponse
    {
        if (!$request->get('init_data')) {
            return response()->json(['failed'], 400);
        }

        $check = InitData::isValid(
            $request->get('init_data'),
            env('TELEGRAM_BOT'),
            true
        );

        if ($check['isValid']) {
            $user = $check['data']['parsed']['user'];

            return response()->json(
                $this->userServices->authentificate(
                    userId: $user['id'] ?? 0,
                    fullName: ($user['first_name'] . ' ' . $user['last_name']),
                    avatar: $user['photo_url'] ?? '',
                    username: $user['username'] ?? '',
                )
            );
        }

        return response()->json(['valid' => false], 400);
    }

    public function register(Request $request): JsonResponse
    {
        User::query()->where('id', auth()->id())->update([
            ... $request->all(),
            'status' => 1
        ]);

        return response()->json([
            'message' => ''
        ]);
    }

    public function tops(): JsonResponse
    {
        return response()->json(
            User::query()->orderByDesc('xp')->take(5)->get()
        );
    }

    public function answer(Request $request): JsonResponse
    {
//        $correct = $request->get('correct');
//        $inCorrect = $request->get('user');
//
//        $data = [
//            "message" => "Ты — строгий лингвист-проверяющий перевода с русского на татарский. Объясни за 1–2 предложения по-русски, почему ответ пользователя неверен и почему правильный вариант верен. Дано: правильный ответ: $correct; ответ пользователя: $inCorrect. Пиши простым текстом без кавычек, скобок, бэктиков, эмодзи и символов \ / < > { } [ ]. Не раскрывай дополнительные синонимы, не добавляй примеры, не меняй регистр слов. Фокусируйся на конкретной причине ошибки: орфография (ә ө ү ң), падеж/аффиксы, число/лицо, вежливая форма, лексическое несоответствие, стиль/регистр. Если ответы совпадают, коротко подтвердить верность и указать нюанс нормы.",
//            "api_key" => 'chad-166bf1f2add642eb8f43a383f5e781699hcxvn91'
//        ];
//
//        $request = Http::post('https://ask.chadgpt.ru/api/public/gpt-4o-mini', $data)->json();

        return response()->json([
//            'message' => $request['response']
            'message' => 123123
        ]);
    }

    public function pastTasks(Request $request): JsonResponse
    {
        UserLessonsCategory::query()->create([
            'users_id' => auth()->id(),
            'sub_lessons_id' => $request->get('childId') ?? 0,
        ]);

        return response()->json([

        ]);
    }
}
