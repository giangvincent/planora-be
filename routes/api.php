<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CalendarController;
use App\Http\Controllers\Api\V1\GamificationController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\WorldController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Me (current user)
    Route::get('/me', [MeController::class, 'show']);
    Route::patch('/me', [MeController::class, 'update']);
    Route::patch('/me/password', [MeController::class, 'updatePassword']);

    // Tasks
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::post('/tasks/{task}/skip', [TaskController::class, 'skip']);

    // Goals
    Route::apiResource('goals', GoalController::class);
    Route::post('/goals/{goal}/complete', [GoalController::class, 'complete']);
    Route::post('/goals/{goal}/archive', [GoalController::class, 'archive']);

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index']);
    Route::post('/calendar/entries', [CalendarController::class, 'store']);
    Route::patch('/calendar/entries/{entry}', [CalendarController::class, 'update']);
    Route::delete('/calendar/entries/{entry}', [CalendarController::class, 'destroy']);

    // Gamification
    Route::get('/gamification/profile', [GamificationController::class, 'profile']);
    Route::get('/gamification/achievements', [GamificationController::class, 'achievements']);
    Route::get('/gamification/achievements/unlocked', [GamificationController::class, 'unlockedAchievements']);

    // World
    Route::get('/world', [WorldController::class, 'index']);
    Route::patch('/world', [WorldController::class, 'update']);
    Route::get('/world/cosmetics', [WorldController::class, 'cosmetics']);
    Route::post('/world/cosmetics/{cosmetic}/equip', [WorldController::class, 'equipCosmetic']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/subscribe', [NotificationController::class, 'subscribe']);
    Route::delete('/notifications/subscribe', [NotificationController::class, 'unsubscribe']);
});
