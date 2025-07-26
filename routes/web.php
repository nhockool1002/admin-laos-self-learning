<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupabaseUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\BadgeController;

Route::get('/', function () {
    return view('admin');
})->name('panel');

Route::get('/supabase/users', [SupabaseUserController::class, 'index']);
Route::post('/supabase/users', [SupabaseUserController::class, 'store']);
Route::put('/supabase/users/{username}', [SupabaseUserController::class, 'update']);
Route::delete('/supabase/users/{username}', [SupabaseUserController::class, 'destroy']);
Route::patch('/supabase/users/{username}/role', [SupabaseUserController::class, 'updateRole']);

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']); // API login
Route::post('/test-csrf', function() {
    return response()->json(['success' => true, 'message' => 'CSRF working']);
}); // Test CSRF
Route::get('/test-csrf', function() {
    return view('test-csrf');
}); // Test CSRF page
Route::get('/test-session', function() {
    session(['test' => 'working']);
    return response()->json(['session_id' => session()->getId(), 'test' => session('test')]);
}); // Test session
Route::get('/logout', [AuthController::class, 'logout']); // API logout
Route::get('/check-auth', [AuthController::class, 'checkAuth']); // API kiểm tra authentication

Route::get('/admin/users', function () {
    return view('users');
})->name('users');

Route::get('/admin/courses', function () { return view('courses'); });
Route::get('/admin/lessons', function () { return view('lessons'); });
Route::get('/admin/games', function () { return view('games'); });
Route::get('/admin/game-groups', function () { return view('game-groups'); });
Route::get('/admin/badges', function () { return view('badges'); });

Route::get('/supabase/courses', [CourseController::class, 'index']);
Route::post('/supabase/courses', [CourseController::class, 'store']);
Route::get('/supabase/courses/{id}', [CourseController::class, 'show']);
Route::put('/supabase/courses/{id}', [CourseController::class, 'update']);
Route::delete('/supabase/courses/{id}', [CourseController::class, 'destroy']);
Route::get('/supabase/courses/{courseId}/lessons', [CourseController::class, 'listLessons']);
Route::post('/supabase/courses/{courseId}/lessons', [CourseController::class, 'createLesson']);
Route::get('/supabase/lessons/{id}', [CourseController::class, 'showLesson']);
Route::put('/supabase/lessons/{id}', [CourseController::class, 'updateLesson']);
Route::delete('/supabase/lessons/{id}', [CourseController::class, 'deleteLesson']);

Route::get('/supabase/games', [GameController::class, 'index']);
Route::post('/supabase/games', [GameController::class, 'store']);
Route::get('/supabase/games/{id}', [GameController::class, 'show']);
Route::put('/supabase/games/{id}', [GameController::class, 'update']);
Route::delete('/supabase/games/{id}', [GameController::class, 'destroy']);

Route::get('/supabase/game-groups', [GameController::class, 'listGroups']);
Route::post('/supabase/game-groups', [GameController::class, 'createGroup']);
Route::get('/supabase/game-groups/{id}', [GameController::class, 'showGroup']);
Route::put('/supabase/game-groups/{id}', [GameController::class, 'updateGroup']);
Route::delete('/supabase/game-groups/{id}', [GameController::class, 'deleteGroup']);

// Badge management routes
Route::get('/supabase/badges', [BadgeController::class, 'index']);
Route::post('/supabase/badges', [BadgeController::class, 'store']);
Route::get('/supabase/badges/{id}', [BadgeController::class, 'show']);
Route::put('/supabase/badges/{id}', [BadgeController::class, 'update']);
Route::delete('/supabase/badges/{id}', [BadgeController::class, 'destroy']);

// User badge management routes
Route::get('/supabase/user-badges', [BadgeController::class, 'getUserBadges']);
Route::get('/supabase/user-badges/{username}', [BadgeController::class, 'getUserBadgesByUserId']);
Route::post('/supabase/user-badges/award', [BadgeController::class, 'awardBadge']);
Route::post('/supabase/user-badges/revoke', [BadgeController::class, 'revokeBadge']);
Route::get('/supabase/users-with-badges', [BadgeController::class, 'getUsersWithBadges']);

// Public API routes for third-party integration
Route::prefix('api/v1')->group(function () {
    Route::get('/badges', [BadgeController::class, 'apiBadges']);
    Route::get('/badges/{id}', [BadgeController::class, 'apiBadgeDetail']);
    Route::get('/users/{username}/badges', [BadgeController::class, 'apiUserBadges']);
    Route::post('/badges/award', [BadgeController::class, 'apiAwardBadge']);
    Route::post('/badges/revoke', [BadgeController::class, 'apiRevokeBadge']);
});

// Include API routes
require __DIR__.'/api.php';

// Route xem coverage report (chỉ nên dùng cho dev)
Route::get('/coverage/{file?}', function ($file = null) {
    $path = public_path('coverage/' . ($file ?: 'index.html'));
    if (!file_exists($path)) abort(404);
    return response()->file($path);
})->where('file', '.*');
