<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupabaseUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;

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
