<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CalendarController;
use App\Http\Controllers\Api\V1\GamificationController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\RolePhaseController;
use App\Http\Controllers\Api\V1\PhaseStepController;
use App\Http\Controllers\Api\V1\LearningTaskController;
use App\Http\Controllers\Api\V1\AutoCheckController;
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

    // Roles / Roadmaps
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);
    Route::patch('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    Route::post('/roles/import-from-ai', [RoleController::class, 'importFromAi']);

    // Phases
    Route::post('/roles/{role}/phases', [RolePhaseController::class, 'store']);
    Route::patch('/phases/{phase}', [RolePhaseController::class, 'update']);
    Route::delete('/phases/{phase}', [RolePhaseController::class, 'destroy']);

    // Steps
    Route::post('/phases/{phase}/steps', [PhaseStepController::class, 'store']);
    Route::patch('/steps/{step}', [PhaseStepController::class, 'update']);
    Route::delete('/steps/{step}', [PhaseStepController::class, 'destroy']);

    // Learning tasks
    Route::post('/steps/{step}/tasks', [LearningTaskController::class, 'store']);
    Route::patch('/learning-tasks/{learningTask}', [LearningTaskController::class, 'update']);
    Route::delete('/learning-tasks/{learningTask}', [LearningTaskController::class, 'destroy']);
    Route::post('/learning-tasks/{learningTask}/complete', [LearningTaskController::class, 'complete']);
    Route::post('/learning-tasks/{learningTask}/sync-task', [LearningTaskController::class, 'syncTask']);

    // Auto-checks
    Route::get('/learning-tasks/{learningTask}/auto-check', [AutoCheckController::class, 'show']);
    Route::post('/learning-tasks/{learningTask}/auto-check/run', [AutoCheckController::class, 'run']);
});
