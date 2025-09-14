<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $data = [
        "message" => "Я перевел слово 'Привет' на татарский как - 'Рәхмәт', но правильный ответ - 'Сәлам'. Почему? Я больше не могу задать вопросы, это тест, в котором я вывожу твой ответ и все. Ты должен объяснить почему мой ответ неправильный, а правильный - 'Сәлам'. Нельзя использовать '\' и такие символы ибо верстка ломается",
        "api_key" => 'chad-166bf1f2add642eb8f43a383f5e781699hcxvn91'
    ];

    return Http::post('https://ask.chadgpt.ru/api/public/gpt-4o-mini', $data)->json();
});
