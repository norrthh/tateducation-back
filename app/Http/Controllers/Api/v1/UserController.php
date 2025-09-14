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

    private function clean(string $s): string
    {
        // Убираем символы, ломающие вёрстку, и лишние пробелы/переводы строк
        $s = preg_replace('/[\\\\\\/<>{}\\[\\]`"“”‘’]/u', '', $s ?? '');
        $s = preg_replace('/\\s+/u', ' ', $s);
        return trim($s);
    }

    private function callLLM(array $payload)
    {
        $payload['api_key'] = 'chad-166bf1f2add642eb8f43a383f5e781699hcxvn91';

        $resp = Http::timeout(20)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('https://ask.chadgpt.ru/api/public/gpt-4o-mini', $payload);

        if (!$resp->ok()) {
            return ['error' => 'LLM request failed', 'status' => $resp->status(), 'body' => $resp->body()];
        }

        return $resp->json(); // ожидается, что вернётся { "text": "..."} или подобное
    }

    public function answer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user'     => 'required|string|max:120',
            'correct'  => 'required|string|max:120',
            'question' => 'nullable|string|max:200',
        ]);

        if (trim($data['user']) === trim($data['correct'])) {
            return response()->json([
                'text' => 'Ваш вариант верен. Совпадает с нормой написания и формой.'
            ]);
        }

        $system = "Ты строгий лингвист по татарскому языку.\n"
            . "Объясни по-русски за 1–2 предложения, почему ответ пользователя неверен и почему верен правильный.\n"
            . "Пиши простым текстом без кавычек, скобок, бэктиков и символов \\ / < > { } [ ].\n"
            . "Фокусируйся на причине: орфография (ә ө ү җ ң һ), аффикс/падеж, число/лицо, вежливая форма, лексика/стиль.\n"
            . "Если ответы совпадают — кратко подтвердить верность.";

        $message = "correct: {$data['correct']}\n"
            . "user: {$data['user']}\n"
            . "source_ru: " . ($data['question'] ?? '');

        $payload = [
            'message'     => $message,
            'history'     => [ ['role' => 'system', 'content' => $system] ],
            'temperature' => 0.2,
            'max_tokens'  => 80,
        ];

        $res  = $this->callLLM($payload);
        $text = $res['response'];

        return response()->json(['message' => $this->clean($text)]);
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
