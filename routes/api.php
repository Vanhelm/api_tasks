<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/tasks', [\App\Http\Controllers\TaskController::class, 'list'])->name('tasks.list');
        Route::post('/tasks', [\App\Http\Controllers\TaskController::class, 'create'])->name('tasks.create');
        Route::get('/tasks/{id}', [\App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
        Route::put('/tasks/{id}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update')
            ->middleware([\App\Http\Middleware\PutRequestMiddleware::class]);
        Route::delete('/tasks/{id}', [\App\Http\Controllers\TaskController::class, 'delete'])->name('tasks.delete');
    });
});
