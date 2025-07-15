<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    public function getUsers($params = [])
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/users', $params);
        return $response->successful() ? $response->json() : null;
    }

    public function getUserByEmail($email)
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/users', [
            'email' => 'eq.' . $email,
            'select' => '*',
        ]);
        $data = $response->successful() ? $response->json() : null;
        return $data[0] ?? null;
    }

    public function getUserByUsername($username)
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/users', [
            'username' => 'eq.' . $username,
            'select' => '*',
        ]);
        $data = $response->successful() ? $response->json() : null;
        return $data[0] ?? null;
    }

    public function createUser($data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/users', $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function updateUser($username, $data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/users?username=eq.' . $username, $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function deleteUser($username)
    {
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/users?username=eq.' . $username);
        return $response->successful();
    }

    public function updateUserRole($username, $isAdmin)
    {
        return $this->updateUser($username, ['is_admin' => $isAdmin]);
    }

    public function getCourses($params = [])
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/video_courses', $params);
        return $response->successful() ? $response->json() : null;
    }

    public function getCourseById($id)
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/video_courses', [
            'id' => 'eq.' . $id,
            'select' => '*',
        ]);
        $data = $response->successful() ? $response->json() : null;
        return $data[0] ?? null;
    }

    public function createCourse($data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/video_courses', $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function updateCourse($id, $data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/video_courses?id=eq.' . $id, $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function deleteCourse($id)
    {
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/video_courses?id=eq.' . $id);
        return $response->successful();
    }

    public function getLessons($courseId = null)
    {
        $params = [];
        if ($courseId) {
            $params['course_id'] = 'eq.' . $courseId;
        }
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/video_lessons', $params);
        return $response->successful() ? $response->json() : null;
    }

    public function getLessonById($id)
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/video_lessons', [
            'id' => 'eq.' . $id,
            'select' => '*',
        ]);
        $data = $response->successful() ? $response->json() : null;
        return $data[0] ?? null;
    }

    public function createLesson($data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/video_lessons', $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function updateLesson($id, $data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/video_lessons?id=eq.' . $id, $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function deleteLesson($id)
    {
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/video_lessons?id=eq.' . $id);
        return $response->successful();
    }

    // ================= GAME GROUPS =================
    public function getGameGroups($params = [])
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/game_groups', $params);
        return $response->successful() ? $response->json() : null;
    }

    public function getGameGroupById($id)
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/game_groups', [
            'id' => 'eq.' . $id,
            'select' => '*',
        ]);
        $data = $response->successful() ? $response->json() : null;
        return $data[0] ?? null;
    }

    public function createGameGroup($data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/game_groups', $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function updateGameGroup($id, $data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/game_groups?id=eq.' . $id, $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function deleteGameGroup($id)
    {
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/game_groups?id=eq.' . $id);
        return $response->successful();
    }

    // ================= FLASH GAMES =================
    public function getFlashGames($params = [])
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/flash_games', $params);
        return $response->successful() ? $response->json() : null;
    }

    public function getFlashGameById($id)
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/flash_games', [
            'id' => 'eq.' . $id,
            'select' => '*',
        ]);
        $data = $response->successful() ? $response->json() : null;
        return $data[0] ?? null;
    }

    public function getFlashGamesByGroup($group_id)
    {
        $response = Http::withHeaders($this->headers())->get($this->baseUrl . '/flash_games', [
            'group_id' => 'eq.' . $group_id,
            'select' => '*',
        ]);
        return $response->successful() ? $response->json() : null;
    }

    public function createFlashGame($data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->post($this->baseUrl . '/flash_games', $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function updateFlashGame($id, $data)
    {
        $response = Http::withHeaders($this->headers([
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]))->patch($this->baseUrl . '/flash_games?id=eq.' . $id, $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function deleteFlashGame($id)
    {
        $response = Http::withHeaders($this->headers([
            'Prefer' => 'return=representation',
        ]))->delete($this->baseUrl . '/flash_games?id=eq.' . $id);
        return $response->successful();
    }
}
