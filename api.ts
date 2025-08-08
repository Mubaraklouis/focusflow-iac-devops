<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\YouTubeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// V1 API Routes
Route::prefix('v1')->group(function () {
    // Categories API endpoints

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/sessions', [App\Http\Controllers\Api\V1\FocusSessionController::class, 'index']);
    Route::get('/sessions/{id}', [App\Http\Controllers\Api\V1\FocusSessionController::class, 'show'])->name('session.show');
    Route::put('/sessions/{id}', [App\Http\Controllers\Api\V1\FocusSessionController::class, 'update'])->name('session.update');


            // Focus Tasks API endpoints
            Route::get('/tasks', [App\Http\Controllers\Api\V1\FocusTaskController::class, 'index']);
            Route::post('/tasks/session/{focusSessionId}', [App\Http\Controllers\Api\V1\FocusTaskController::class, 'store']);
            Route::get('/tasks/{id}', [App\Http\Controllers\Api\V1\FocusTaskController::class, 'show']);
            Route::put('/tasks/{id}', [App\Http\Controllers\Api\V1\FocusTaskController::class, 'update']);
            Route::delete('/tasks/{id}', [App\Http\Controllers\Api\V1\FocusTaskController::class, 'destroy']);
            Route::post('/tasks/{id}/toggle', [App\Http\Controllers\Api\V1\FocusTaskController::class, 'toggle']);

    // YouTube API proxy endpoint (no auth required - acts as proxy only)
    Route::get('/youtube/search', [YouTubeController::class, 'search']);

    // Courses API endpoints
    Route::get('/courses', [App\Http\Controllers\CourseController::class, 'getUserCourses']);

    // Stats API endpoints (public)
    Route::get('/stats/health', [StatsController::class, 'health']);
    Route::get('/stats/public', [StatsController::class, 'publicStats']);
    
    // UUID-based stats endpoints (no auth required - user-specific data by UUID)
    Route::get('/stats/uuid/{user_uuid}', [StatsController::class, 'getStatsByUuid']);
    Route::get('/stats/actual-time/uuid/{user_uuid}', [StatsController::class, 'getActualTimeByUuid']);



    // Focus Sessions API endpoints (requires authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/sessions', [App\Http\Controllers\Api\V1\FocusSessionController::class, 'store']);
        Route::get('/sessions/active', [App\Http\Controllers\Api\V1\FocusSessionController::class, 'active']);
        Route::delete('/sessions/{id}', [App\Http\Controllers\Api\V1\FocusSessionController::class, 'destroy']);
        Route::post('/sessions/{id}/complete', [App\Http\Controllers\Api\V1\FocusSessionController::class, 'complete']);
    });
});
