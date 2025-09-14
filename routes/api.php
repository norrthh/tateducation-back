<?php

use App\Http\Controllers\Api\v1\Task\TaskController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('users')->group(function () {
        Route::post('/auth', [UserController::class, 'auth']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/tops', [UserController::class, 'tops']);
            Route::post('/register', [UserController::class, 'register']);
            Route::post('/answer', [UserController::class, 'answer']);

            Route::prefix('tasks')->group(function () {
                Route::get('/', [TaskController::class, 'index']);
                Route::get('/{id}/{chillId}', [TaskController::class, 'getTasks']);
            });

            Route::prefix('lessons')->group(function () {
                Route::get('/', [TaskController::class, 'index']);
            });

            Route::post('past', [UserController::class, 'pastTasks']);
        });
    });
});
