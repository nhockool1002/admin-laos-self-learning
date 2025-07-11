<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('APP_SUPABASE_URL'), '/') . '/rest/v1';
        $this->apiKey = env('APP_SUPABASE_ANON_KEY');
    }

    /**
     * Lấy danh sách users từ Supabase
     */
    public function getUsers($params = [])
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->baseUrl . '/users', $params);

        if ($response->successful()) {
            return $response->json();
        }
        return null;
    }

    /**
     * Lấy user theo email từ Supabase
     */
    public function getUserByEmail($email)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->baseUrl . '/users', [
            'email' => 'eq.' . $email,
            'select' => '*',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data[0] ?? null;
        }
        return null;
    }

    /**
     * Lấy user theo username từ Supabase
     */
    public function getUserByUsername($username)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->baseUrl . '/users', [
            'username' => 'eq.' . $username,
            'select' => '*',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data[0] ?? null;
        }
        return null;
    }

    /**
     * Tạo user mới
     */
    public function createUser($data)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post($this->baseUrl . '/users', $data);

        if ($response->successful()) {
            return $response->json()[0] ?? null;
        }
        return null;
    }

    /**
     * Cập nhật user (theo username)
     */
    public function updateUser($username, $data)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->patch($this->baseUrl . '/users?username=eq.' . $username, $data);

        if ($response->successful()) {
            return $response->json()[0] ?? null;
        }
        return null;
    }

    /**
     * Xoá user (theo username)
     */
    public function deleteUser($username)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Prefer' => 'return=representation',
        ])->delete($this->baseUrl . '/users?username=eq.' . $username);

        return $response->successful();
    }

    /**
     * Cập nhật role (is_admin) cho user
     */
    public function updateUserRole($username, $isAdmin)
    {
        return $this->updateUser($username, ['is_admin' => $isAdmin]);
    }

    /**
     * CRUD cho video_courses
     */
    public function getCourses($params = [])
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->baseUrl . '/video_courses', $params);
        return $response->successful() ? $response->json() : null;
    }

    public function getCourseById($id)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->baseUrl . '/video_courses', [
            'id' => 'eq.' . $id,
            'select' => '*',
        ]);
        $data = $response->successful() ? $response->json() : null;
        return $data[0] ?? null;
    }

    public function createCourse($data)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post($this->baseUrl . '/video_courses', $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function updateCourse($id, $data)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->patch($this->baseUrl . '/video_courses?id=eq.' . $id, $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function deleteCourse($id)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Prefer' => 'return=representation',
        ])->delete($this->baseUrl . '/video_courses?id=eq.' . $id);
        return $response->successful();
    }

    /**
     * CRUD cho video_lessons
     */
    public function getLessons($courseId = null)
    {
        $params = [];
        if ($courseId) {
            $params['course_id'] = 'eq.' . $courseId;
        }
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->baseUrl . '/video_lessons', $params);
        return $response->successful() ? $response->json() : null;
    }

    public function getLessonById($id)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->baseUrl . '/video_lessons', [
            'id' => 'eq.' . $id,
            'select' => '*',
        ]);
        $data = $response->successful() ? $response->json() : null;
        return $data[0] ?? null;
    }

    public function createLesson($data)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post($this->baseUrl . '/video_lessons', $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function updateLesson($id, $data)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->patch($this->baseUrl . '/video_lessons?id=eq.' . $id, $data);
        return $response->successful() ? ($response->json()[0] ?? null) : null;
    }

    public function deleteLesson($id)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Prefer' => 'return=representation',
        ])->delete($this->baseUrl . '/video_lessons?id=eq.' . $id);
        return $response->successful();
    }
} 