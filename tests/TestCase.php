<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test environment variables for Supabase
        config([
            'services.supabase.url' => 'https://test.supabase.co',
            'services.supabase.anon_key' => 'test-key-123'
        ]);
    }

    /**
     * Create a mock user data for testing
     */
    protected function createMockUser($overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => md5('password123'),
            'is_admin' => false,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ], $overrides);
    }

    /**
     * Create mock course data for testing
     */
    protected function createMockCourse($overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'title' => 'Test Course',
            'description' => 'A test course description',
            'is_active' => true,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ], $overrides);
    }

    /**
     * Create mock lesson data for testing
     */
    protected function createMockLesson($overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'course_id' => 1,
            'title' => 'Test Lesson',
            'content' => 'Test lesson content',
            'video_url' => 'https://example.com/video.mp4',
            'order' => 1,
            'is_active' => true,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ], $overrides);
    }

    /**
     * Create mock game data for testing
     */
    protected function createMockGame($overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'group_id' => 1,
            'title' => 'Test Game',
            'description' => 'A test game description',
            'type' => 'quiz',
            'is_active' => true,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ], $overrides);
    }

    /**
     * Create mock game group data for testing
     */
    protected function createMockGameGroup($overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'title' => 'Test Game Group',
            'description' => 'A test game group description',
            'is_active' => true,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ], $overrides);
    }
}
