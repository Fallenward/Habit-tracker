<?php

use App\Http\Controllers\V1\CalendarController;
use App\Http\Controllers\V1\HabitController;
use App\Http\Controllers\V1\LogController;
use App\Http\Controllers\V1\StatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Habits routes (today must be before ApiResource to avoid route conflict)
    Route::get('/habits/today', [HabitController::class, 'today']);
    Route::ApiResource('habits', HabitController::class);

    // Logs routes (Done/Undo)
    Route::put('/logs/{date}', [LogController::class, 'update']);

    // Calendar routes
    Route::get('/calendar', [CalendarController::class, 'index']);
    Route::get('/calendar/{date}', [CalendarController::class, 'show']);

    // Stats routes
    Route::get('/stats', [StatsController::class, 'index']);
});

require __DIR__.'/auth.php';
