<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SupabaseUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\LessonGameController;

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
Route::get('/admin/lesson-games', function () { return view('lesson-games'); });
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

// Lesson game management routes
Route::get('/supabase/lesson-games', [LessonGameController::class, 'index']);
Route::post('/supabase/lesson-games', [LessonGameController::class, 'store']);
Route::get('/supabase/lesson-games/{id}', [LessonGameController::class, 'show']);
Route::put('/supabase/lesson-games/{id}', [LessonGameController::class, 'update']);
Route::delete('/supabase/lesson-games/{id}', [LessonGameController::class, 'destroy']);

Route::get('/supabase/lesson-game-groups', [LessonGameController::class, 'listGroups']);
Route::post('/supabase/lesson-game-groups', [LessonGameController::class, 'createGroup']);
Route::get('/supabase/lesson-game-groups/{id}', [LessonGameController::class, 'showGroup']);
Route::put('/supabase/lesson-game-groups/{id}', [LessonGameController::class, 'updateGroup']);
Route::delete('/supabase/lesson-game-groups/{id}', [LessonGameController::class, 'deleteGroup']);

// Debug route for game groups (remove after testing)
Route::get('/debug/game-groups', function() {
    $supabase = new App\Services\SupabaseService();
    
    // Get raw data to check schema
    $rawResponse = Http::withHeaders([
        'apikey' => config('services.supabase.anon_key'),
        'Authorization' => 'Bearer ' . config('services.supabase.anon_key'),
    ])->get(config('services.supabase.url') . '/rest/v1/game_groups');
    
    $rawData = $rawResponse->json();
    
    // Check if group_game_type column exists
    $hasGroupGameType = !empty($rawData) && isset($rawData[0]['group_game_type']);
    
    // Test individual filters
    $testTypeA = Http::withHeaders([
        'apikey' => config('services.supabase.anon_key'),
        'Authorization' => 'Bearer ' . config('services.supabase.anon_key'),
    ])->get(config('services.supabase.url') . '/rest/v1/game_groups?group_game_type=eq.A')->json();
    
    $testTypeB = Http::withHeaders([
        'apikey' => config('services.supabase.anon_key'),
        'Authorization' => 'Bearer ' . config('services.supabase.anon_key'),
    ])->get(config('services.supabase.url') . '/rest/v1/game_groups?group_game_type=eq.B')->json();
    
    return response()->json([
        'schema_check' => [
            'has_group_game_type_column' => $hasGroupGameType,
            'total_groups' => count($rawData),
            'sample_group' => $rawData[0] ?? null,
        ],
        'service_calls' => [
            'type_a_groups' => $supabase->getGameGroups(),
            'type_b_groups' => $supabase->getLessonGameGroups(),
        ],
        'direct_api_tests' => [
            'all_groups_raw' => $rawData,
            'type_a_filter_test' => $testTypeA,
            'type_b_filter_test' => $testTypeB,
        ]
    ]);
});

// Test endpoint to debug lesson game group creation
Route::post('/test/create-lesson-group', function(Request $request) {
    try {
        $data = [
            'name' => 'Test Lesson Group ' . time(),
            'description' => 'Test description',
            'group_game_type' => 'B'
        ];
        
        Log::info('Testing lesson group creation', ['data' => $data]);
        
        // Test direct Supabase call
        $response = Http::withHeaders([
            'apikey' => config('services.supabase.anon_key'),
            'Authorization' => 'Bearer ' . config('services.supabase.anon_key'),
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post(config('services.supabase.url') . '/rest/v1/game_groups', $data);
        
        Log::info('Direct Supabase response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'success' => $response->successful()
        ]);
        
        return response()->json([
            'status' => $response->successful() ? 'SUCCESS' : 'ERROR',
            'supabase_status' => $response->status(),
            'data_sent' => $data,
            'response_body' => $response->json(),
            'error' => $response->successful() ? null : $response->body()
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'EXCEPTION',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Simple test route to verify database schema
Route::get('/test/database-schema', function() {
    try {
        // Test if group_game_type column exists by trying to query it
        $result = Http::withHeaders([
            'apikey' => config('services.supabase.anon_key'),
            'Authorization' => 'Bearer ' . config('services.supabase.anon_key'),
        ])->get(config('services.supabase.url') . '/rest/v1/game_groups?select=id,name,group_game_type&limit=1');
        
        if ($result->successful()) {
            $data = $result->json();
            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'group_game_type column exists',
                'sample_data' => $data[0] ?? null,
                'has_group_game_type' => isset($data[0]['group_game_type'])
            ]);
        } else {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Query failed',
                'error' => $result->body(),
                'status_code' => $result->status()
            ]);
        }
    } catch (Exception $e) {
        return response()->json([
            'status' => 'EXCEPTION',
            'message' => 'Column might not exist',
            'error' => $e->getMessage()
        ]);
    }
});

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

// Route xem coverage report (chỉ nên dùng cho dev)
Route::get('/coverage/{file?}', function ($file = null) {
    $path = public_path('coverage/' . ($file ?: 'index.html'));
    if (!file_exists($path)) abort(404);
    return response()->file($path);
})->where('file', '.*');
