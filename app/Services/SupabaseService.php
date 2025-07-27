<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class SupabaseService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.supabase.url'), '/') . '/rest/v1';
        $this->apiKey = config('services.supabase.anon_key');

        // Log để kiểm tra config đã load đúng chưa
        Log::debug('SupabaseService initialized', [
            'base_url' => $this->baseUrl,
            'api_key_length' => strlen($this->apiKey),
        ]);
    }

    private function headers(array $extra = []): array
    {
        return array_merge([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ], $extra);
    }

    /**
     * Helper method để lấy thông tin user và IP
     */
    private function getRequestInfo()
    {
        $user = Auth::user();
        $request = Request::instance();
        
        return [
            'user_id' => $user ? $user->id : null,
            'username' => $user ? $user->username : null,
            'user_email' => $user ? $user->email : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
        ];
    }

    /**
     * Helper method để ghi log debug theo mẫu yêu cầu
     */
    private function logDebug($component, $action, $details = [])
    {
        $message = "[Supabase][{$component}] {$action}";
        Log::debug($message, $details);
    }

    /**
     * Helper method để ghi log request chi tiết
     */
    private function logRequest($component, $method, $endpoint, $params = [], $data = null)
    {
        $requestInfo = $this->getRequestInfo();
        
        $details = [
            'method' => $method,
            'endpoint' => $endpoint,
            'full_url' => $this->baseUrl . $endpoint,
            'params' => $params,
            'data' => $data,
            'headers' => $this->headers(),
            'timestamp' => now()->toISOString(),
            'user_id' => $requestInfo['user_id'],
            'username' => $requestInfo['username'],
            'user_email' => $requestInfo['user_email'],
            'ip_address' => $requestInfo['ip_address'],
            'user_agent' => $requestInfo['user_agent'],
            'request_url' => $requestInfo['request_url'],
            'request_method' => $requestInfo['request_method']
        ];

        // Chuyển thành text liên tục
        $textDetails = [];
        foreach ($details as $key => $value) {
            if (is_array($value)) {
                $textDetails[] = "{$key}: " . json_encode($value);
            } else {
                $textDetails[] = "{$key}: {$value}";
            }
        }

        $this->logDebug($component, "Request: {$method} {$endpoint} - " . implode(' | ', $textDetails));
    }

    /**
     * Helper method để ghi log response chi tiết
     */
    private function logResponse($component, $response, $action = 'Response received')
    {
        $responseData = null;
        $errorMessage = null;
        
        try {
            if ($response->successful()) {
                $responseData = $response->json();
            } else {
                $errorMessage = $response->body();
            }
        } catch (\Exception $e) {
            $errorMessage = 'Failed to parse response: ' . $e->getMessage();
        }

        $requestInfo = $this->getRequestInfo();

        $details = [
            'status_code' => $response->status(),
            'success' => $response->successful() ? 'true' : 'false',
            'response_data' => $responseData,
            'error_message' => $errorMessage,
            'response_headers' => $response->headers(),
            'response_time' => $response->handlerStats()['total_time'] ?? null,
            'timestamp' => now()->toISOString(),
            'user_id' => $requestInfo['user_id'],
            'username' => $requestInfo['username'],
            'user_email' => $requestInfo['user_email'],
            'ip_address' => $requestInfo['ip_address'],
            'user_agent' => $requestInfo['user_agent'],
            'request_url' => $requestInfo['request_url'],
            'request_method' => $requestInfo['request_method']
        ];

        // Chuyển thành text liên tục
        $textDetails = [];
        foreach ($details as $key => $value) {
            if (is_array($value)) {
                $textDetails[] = "{$key}: " . json_encode($value);
            } else {
                $textDetails[] = "{$key}: {$value}";
            }
        }

        $this->logDebug($component, $action . " - " . implode(' | ', $textDetails));
    }

    public function getUsers($params = [])
    {
        $this->logRequest('Users', 'GET', '/users', $params);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/users', $params);
        $this->logResponse('Users', $response, 'GET /users completed');
        return $response->successful() ? $response->json() : null;
    }

    public function getUserByEmail($email)
    {
        $params = [
            'email' => 'eq.' . $email,
            'select' => '*',
        ];
        $this->logRequest('Users', 'GET', '/users', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/users', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('Users', $response, "GET /users by email '{$email}' completed");
        return $data[0] ?? null;
    }

    public function getUserByUsername($username)
    {
        $params = [
            'username' => 'eq.' . $username,
            'select' => '*',
        ];
        $this->logRequest('Users', 'GET', '/users', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/users', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('Users', $response, "GET /users by username '{$username}' completed");
        return $data[0] ?? null;
    }

    public function createUser($data)
    {
        $this->logRequest('Users', 'POST', '/users', [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/users', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('Users', $response, 'POST /users completed');
        return $result;
    }

    public function updateUser($username, $data)
    {
        $this->logRequest('Users', 'PATCH', "/users?username=eq.{$username}", [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/users?username=eq.' . $username, $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('Users', $response, "PATCH /users for username '{$username}' completed");
        return $result;
    }

    public function deleteUser($username)
    {
        $this->logRequest('Users', 'DELETE', "/users?username=eq.{$username}", [], null);
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/users?username=eq.' . $username);
        $this->logResponse('Users', $response, "DELETE /users for username '{$username}' completed");
        return $response->successful();
    }

    public function updateUserRole($username, $isAdmin)
    {
        $this->logDebug('Users', "Update user role for '{$username}' to admin: " . ($isAdmin ? 'true' : 'false'), [
            'username' => $username,
            'is_admin' => $isAdmin,
            'action' => 'updateUserRole'
        ]);
        return $this->updateUser($username, ['is_admin' => $isAdmin]);
    }

    public function getCourses($params = [])
    {
        $this->logRequest('Course', 'GET', '/video_courses', $params);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/video_courses', $params);
        $this->logResponse('Course', $response, 'GET /video_courses completed');
        return $response->successful() ? $response->json() : null;
    }

    public function getCourseById($id)
    {
        $params = [
            'id' => 'eq.' . $id,
            'select' => '*',
        ];
        $this->logRequest('Course', 'GET', '/video_courses', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/video_courses', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('Course', $response, "GET /video_courses by id '{$id}' completed");
        return $data[0] ?? null;
    }

    public function createCourse($data)
    {
        $this->logRequest('Course', 'POST', '/video_courses', [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/video_courses', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('Course', $response, 'POST /video_courses completed');
        return $result;
    }

    public function updateCourse($id, $data)
    {
        $this->logRequest('Course', 'PATCH', "/video_courses?id=eq.{$id}", [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/video_courses?id=eq.' . $id, $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('Course', $response, "PATCH /video_courses for id '{$id}' completed");
        return $result;
    }

    public function deleteCourse($id)
    {
        $this->logRequest('Course', 'DELETE', "/video_courses?id=eq.{$id}", [], null);
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/video_courses?id=eq.' . $id);
        $this->logResponse('Course', $response, "DELETE /video_courses for id '{$id}' completed");
        return $response->successful();
    }

    public function getLessons($courseId = null)
    {
        $params = [];
        if ($courseId) {
            $params['course_id'] = 'eq.' . $courseId;
        }
        $this->logRequest('Lessons', 'GET', '/video_lessons', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/video_lessons', $params);
        $this->logResponse('Lessons', $response, "GET /video_lessons" . ($courseId ? " for course_id '{$courseId}'" : '') . " completed");
        return $response->successful() ? $response->json() : null;
    }

    public function getLessonById($id)
    {
        $params = [
            'id' => 'eq.' . $id,
            'select' => '*',
        ];
        $this->logRequest('Lessons', 'GET', '/video_lessons', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/video_lessons', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('Lessons', $response, "GET /video_lessons by id '{$id}' completed");
        return $data[0] ?? null;
    }

    public function createLesson($data)
    {
        $this->logRequest('Lessons', 'POST', '/video_lessons', [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/video_lessons', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('Lessons', $response, 'POST /video_lessons completed');
        return $result;
    }

    public function updateLesson($id, $data)
    {
        $this->logRequest('Lessons', 'PATCH', "/video_lessons?id=eq.{$id}", [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/video_lessons?id=eq.' . $id, $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('Lessons', $response, "PATCH /video_lessons for id '{$id}' completed");
        return $result;
    }

    public function deleteLesson($id)
    {
        $this->logRequest('Lessons', 'DELETE', "/video_lessons?id=eq.{$id}", [], null);
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/video_lessons?id=eq.' . $id);
        $this->logResponse('Lessons', $response, "DELETE /video_lessons for id '{$id}' completed");
        return $response->successful();
    }

    // ================= GAME GROUPS =================
    public function getGameGroups($params = [])
    {
        $this->logRequest('GameGroups', 'GET', '/game_groups', $params);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/game_groups', $params);
        $this->logResponse('GameGroups', $response, 'GET /game_groups completed');
        return $response->successful() ? $response->json() : null;
    }

    public function getGameGroupById($id)
    {
        $params = [
            'id' => 'eq.' . $id,
            'select' => '*',
        ];
        $this->logRequest('GameGroups', 'GET', '/game_groups', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/game_groups', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('GameGroups', $response, "GET /game_groups by id '{$id}' completed");
        return $data[0] ?? null;
    }

    public function createGameGroup($data)
    {
        $this->logRequest('GameGroups', 'POST', '/game_groups', [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/game_groups', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('GameGroups', $response, 'POST /game_groups completed');
        return $result;
    }

    public function updateGameGroup($id, $data)
    {
        $this->logRequest('GameGroups', 'PATCH', "/game_groups?id=eq.{$id}", [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/game_groups?id=eq.' . $id, $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('GameGroups', $response, "PATCH /game_groups for id '{$id}' completed");
        return $result;
    }

    public function deleteGameGroup($id)
    {
        $this->logRequest('GameGroups', 'DELETE', "/game_groups?id=eq.{$id}", [], null);
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/game_groups?id=eq.' . $id);
        $this->logResponse('GameGroups', $response, "DELETE /game_groups for id '{$id}' completed");
        return $response->successful();
    }

    // ================= FLASH GAMES =================
    public function getFlashGames($params = [])
    {
        // Add game_type filter for existing game management (type A only)
        $params['game_type'] = 'eq.A';
        $this->logRequest('FlashGames', 'GET', '/flash_games', $params);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/flash_games', $params);
        $this->logResponse('FlashGames', $response, 'GET /flash_games completed');
        return $response->successful() ? $response->json() : null;
    }

    public function getFlashGameById($id)
    {
        $params = [
            'id' => 'eq.' . $id,
            'select' => '*',
        ];
        $this->logRequest('FlashGames', 'GET', '/flash_games', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/flash_games', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('FlashGames', $response, "GET /flash_games by id '{$id}' completed");
        return $data[0] ?? null;
    }

    public function getFlashGamesByGroup($group_id)
    {
        $params = [
            'group_id' => 'eq.' . $group_id,
            'select' => '*',
        ];
        $this->logRequest('FlashGames', 'GET', '/flash_games', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/flash_games', $params);
        $this->logResponse('FlashGames', $response, "GET /flash_games by group_id '{$group_id}' completed");
        return $response->successful() ? $response->json() : null;
    }

    public function createFlashGame($data)
    {
        // Set game_type to 'A' for existing game management
        $data['game_type'] = 'A';
        $this->logRequest('FlashGames', 'POST', '/flash_games', [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/flash_games', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('FlashGames', $response, 'POST /flash_games completed');
        return $result;
    }

    public function updateFlashGame($id, $data)
    {
        $this->logRequest('FlashGames', 'PATCH', "/flash_games?id=eq.{$id}", [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/flash_games?id=eq.' . $id, $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('FlashGames', $response, "PATCH /flash_games for id '{$id}' completed");
        return $result;
    }

    public function deleteFlashGame($id)
    {
        $this->logRequest('FlashGames', 'DELETE', "/flash_games?id=eq.{$id}", [], null);
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/flash_games?id=eq.' . $id);
        $this->logResponse('FlashGames', $response, "DELETE /flash_games for id '{$id}' completed");
        return $response->successful();
    }

    // ================= LESSON GAMES (Type B) =================
    public function getLessonGames($params = [])
    {
        // Filter for lesson games (type B only)
        $params['game_type'] = 'eq.B';
        $this->logRequest('LessonGames', 'GET', '/flash_games', $params);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/flash_games', $params);
        $this->logResponse('LessonGames', $response, 'GET /flash_games (lesson games) completed');
        return $response->successful() ? $response->json() : null;
    }

    public function getLessonGameById($id)
    {
        $params = [
            'id' => 'eq.' . $id,
            'game_type' => 'eq.B',
            'select' => '*',
        ];
        $this->logRequest('LessonGames', 'GET', '/flash_games', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/flash_games', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('LessonGames', $response, "GET /flash_games (lesson game) by id '{$id}' completed");
        return $data[0] ?? null;
    }

    public function getLessonGamesByGroup($group_id)
    {
        $params = [
            'group_id' => 'eq.' . $group_id,
            'game_type' => 'eq.B',
            'select' => '*',
        ];
        $this->logRequest('LessonGames', 'GET', '/flash_games', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/flash_games', $params);
        $this->logResponse('LessonGames', $response, "GET /flash_games (lesson games) by group_id '{$group_id}' completed");
        return $response->successful() ? $response->json() : null;
    }

    public function createLessonGame($data)
    {
        // Set game_type to 'B' for lesson games
        $data['game_type'] = 'B';
        $this->logRequest('LessonGames', 'POST', '/flash_games', [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/flash_games', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('LessonGames', $response, 'POST /flash_games (lesson game) completed');
        return $result;
    }

    public function updateLessonGame($id, $data)
    {
        // Ensure game_type remains 'B' for lesson games
        $data['game_type'] = 'B';
        $this->logRequest('LessonGames', 'PATCH', "/flash_games?id=eq.{$id}", [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/flash_games?id=eq.' . $id . '&game_type=eq.B', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('LessonGames', $response, "PATCH /flash_games (lesson game) for id '{$id}' completed");
        return $result;
    }

    public function deleteLessonGame($id)
    {
        $this->logRequest('LessonGames', 'DELETE', "/flash_games?id=eq.{$id}&game_type=eq.B", [], null);
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/flash_games?id=eq.' . $id . '&game_type=eq.B');
        $this->logResponse('LessonGames', $response, "DELETE /flash_games (lesson game) for id '{$id}' completed");
        return $response->successful();
    }

    // ================= LESSON GAME GROUPS (Type B Groups) =================
    public function getLessonGameGroups($params = [])
    {
        $this->logRequest('LessonGameGroups', 'GET', '/game_groups', $params);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/game_groups', $params);
        $this->logResponse('LessonGameGroups', $response, 'GET /game_groups (lesson game groups) completed');
        return $response->successful() ? $response->json() : null;
    }

    public function getLessonGameGroupById($id)
    {
        $params = [
            'id' => 'eq.' . $id,
            'select' => '*',
        ];
        $this->logRequest('LessonGameGroups', 'GET', '/game_groups', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/game_groups', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('LessonGameGroups', $response, "GET /game_groups (lesson game group) by id '{$id}' completed");
        return $data[0] ?? null;
    }

    public function createLessonGameGroup($data)
    {
        $this->logRequest('LessonGameGroups', 'POST', '/game_groups', [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/game_groups', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('LessonGameGroups', $response, 'POST /game_groups (lesson game group) completed');
        return $result;
    }

    public function updateLessonGameGroup($id, $data)
    {
        $this->logRequest('LessonGameGroups', 'PATCH', "/game_groups?id=eq.{$id}", [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/game_groups?id=eq.' . $id, $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('LessonGameGroups', $response, "PATCH /game_groups (lesson game group) for id '{$id}' completed");
        return $result;
    }

    public function deleteLessonGameGroup($id)
    {
        $this->logRequest('LessonGameGroups', 'DELETE', "/game_groups?id=eq.{$id}", [], null);
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/game_groups?id=eq.' . $id);
        $this->logResponse('LessonGameGroups', $response, "DELETE /game_groups (lesson game group) for id '{$id}' completed");
        return $response->successful();
    }

    // ================= BADGES =================
    public function getBadges($params = [])
    {
        $this->logRequest('Badges', 'GET', '/badges_system', $params);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/badges_system', $params);
        $this->logResponse('Badges', $response, 'GET /badges_system completed');
        return $response->successful() ? $response->json() : null;
    }

    public function getBadgeById($id)
    {
        $params = [
            'id' => 'eq.' . $id,
            'select' => '*',
        ];
        $this->logRequest('Badges', 'GET', '/badges_system', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/badges_system', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('Badges', $response, "GET /badges_system by id '{$id}' completed");
        return $data[0] ?? null;
    }

    public function createBadge($data)
    {
        $this->logRequest('Badges', 'POST', '/badges_system', [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/badges_system', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('Badges', $response, 'POST /badges_system completed');
        return $result;
    }

    public function updateBadge($id, $data)
    {
        $this->logRequest('Badges', 'PATCH', "/badges_system?id=eq.{$id}", [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/badges_system?id=eq.' . $id, $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('Badges', $response, "PATCH /badges_system for id '{$id}' completed");
        return $result;
    }

    public function deleteBadge($id)
    {
        $this->logRequest('Badges', 'DELETE', "/badges_system?id=eq.{$id}", [], null);
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/badges_system?id=eq.' . $id);
        $this->logResponse('Badges', $response, "DELETE /badges_system for id '{$id}' completed");
        return $response->successful();
    }

    // ================= USER BADGES =================
    public function getUserBadges($params = [])
    {
        $this->logRequest('UserBadges', 'GET', '/user_badges', $params);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/user_badges', $params);
        $this->logResponse('UserBadges', $response, 'GET /user_badges completed');
        return $response->successful() ? $response->json() : null;
    }

    public function getUserBadgesByUsername($username)
    {
        $params = [
            'username' => 'eq.' . $username,
            'select' => '*, badges_system(*)',
        ];
        $this->logRequest('UserBadges', 'GET', '/user_badges', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/user_badges', $params);
        $this->logResponse('UserBadges', $response, "GET /user_badges for username '{$username}' completed");
        return $response->successful() ? $response->json() : null;
    }

    // Keep the old method for backward compatibility
    public function getUserBadgesByUserId($userId)
    {
        // For compatibility, assume userId is actually username in the new schema
        return $this->getUserBadgesByUsername($userId);
    }

    public function checkUserBadgeExists($username, $badgeId)
    {
        $params = [
            'username' => 'eq.' . $username,
            'badge_id' => 'eq.' . $badgeId,
            'select' => 'id',
        ];
        $this->logRequest('UserBadges', 'GET', '/user_badges', $params, null);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/user_badges', $params);
        $data = $response->successful() ? $response->json() : null;
        $this->logResponse('UserBadges', $response, "Check user badge exists for username '{$username}' and badge_id '{$badgeId}' completed");
        return !empty($data);
    }

    public function awardUserBadge($data)
    {
        $this->logRequest('UserBadges', 'POST', '/user_badges', [], $data);
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/user_badges', $data);
        $result = $response->successful() ? ($response->json()[0] ?? null) : null;
        $this->logResponse('UserBadges', $response, 'POST /user_badges completed');
        return $result;
    }

    public function revokeUserBadge($username, $badgeId)
    {
        $this->logRequest('UserBadges', 'DELETE', "/user_badges?username=eq.{$username}&badge_id=eq.{$badgeId}", [], null);
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/user_badges?username=eq.' . $username . '&badge_id=eq.' . $badgeId);
        $this->logResponse('UserBadges', $response, "DELETE /user_badges for username '{$username}' and badge_id '{$badgeId}' completed");
        return $response->successful();
    }

    public function getUsersWithBadgeDetails($params = [])
    {
        $baseParams = ['select' => '*, user_badges(*, badges_system(*))'];
        $allParams = array_merge($baseParams, $params);
        $this->logRequest('Users', 'GET', '/users with badges', $allParams);
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/users', $allParams);
        $this->logResponse('Users', $response, 'GET /users with badge details completed');
        return $response->successful() ? $response->json() : null;
    }
}
