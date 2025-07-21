<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Default Supabase configuration for testing
        config([
            'services.supabase.url' => env('SUPABASE_URL', 'https://test.supabase.co'),
            'services.supabase.anon_key' => env('SUPABASE_ANON_KEY', 'test-key')
        ]);
    }

    /**
     * Create a mock admin user for testing
     */
    protected function createMockAdminUser(): array
    {
        return [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@test.com',
            'is_admin' => true,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];
    }

    /**
     * Create a mock regular user for testing
     */
    protected function createMockUser(): array
    {
        return [
            'id' => 2,
            'username' => 'user',
            'email' => 'user@test.com',
            'is_admin' => false,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];
    }

    /**
     * Create a mock badge for testing
     */
    protected function createMockBadge($id = 1, $overrides = []): array
    {
        $defaults = [
            'id' => $id,
            'name' => "Test Badge {$id}",
            'description' => "Description for test badge {$id}",
            'image_url' => "/assets/images/badge_{$id}.png",
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create a mock user badge for testing
     */
    protected function createMockUserBadge($userId = 123, $badgeId = 1, $overrides = []): array
    {
        $defaults = [
            'id' => 1,
            'user_id' => $userId,
            'badge_id' => $badgeId,
            'awarded_at' => now()->toISOString()
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create admin authentication headers
     */
    protected function adminHeaders(): array
    {
        return [
            'User' => json_encode($this->createMockAdminUser()),
            'X-CSRF-TOKEN' => csrf_token()
        ];
    }

    /**
     * Create user authentication headers
     */
    protected function userHeaders(): array
    {
        return [
            'User' => json_encode($this->createMockUser()),
            'X-CSRF-TOKEN' => csrf_token()
        ];
    }

    /**
     * Mock successful Supabase responses for common badge operations
     */
    protected function mockSupabaseSuccess(): void
    {
        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::response([
                $this->createMockBadge(1),
                $this->createMockBadge(2)
            ], 200),
            
            'https://test.supabase.co/rest/v1/user_badges*' => Http::response([
                $this->createMockUserBadge(123, 1),
                $this->createMockUserBadge(456, 2)
            ], 200),
            
            'https://test.supabase.co/rest/v1/users*' => Http::response([
                array_merge($this->createMockAdminUser(), [
                    'user_badges' => [$this->createMockUserBadge(1, 1)]
                ]),
                array_merge($this->createMockUser(), [
                    'user_badges' => [$this->createMockUserBadge(2, 2)]
                ])
            ], 200)
        ]);
    }

    /**
     * Mock Supabase error responses
     */
    protected function mockSupabaseError(): void
    {
        Http::fake([
            'https://test.supabase.co/rest/v1/*' => Http::response([], 500)
        ]);
    }

    /**
     * Mock empty Supabase responses
     */
    protected function mockSupabaseEmpty(): void
    {
        Http::fake([
            'https://test.supabase.co/rest/v1/*' => Http::response([], 200)
        ]);
    }

    /**
     * Assert that a response contains badge data
     */
    protected function assertBadgeResponse($response, $expectedBadge = null): void
    {
        $response->assertStatus(200);
        
        if ($expectedBadge) {
            $response->assertJson($expectedBadge);
        } else {
            $response->assertJsonStructure([
                '*' => ['id', 'name', 'description', 'image_url']
            ]);
        }
    }

    /**
     * Assert that a response contains user badge data
     */
    protected function assertUserBadgeResponse($response, $expectedUserBadge = null): void
    {
        $response->assertStatus(200);
        
        if ($expectedUserBadge) {
            $response->assertJson($expectedUserBadge);
        } else {
            $response->assertJsonStructure([
                '*' => ['id', 'user_id', 'badge_id', 'awarded_at']
            ]);
        }
    }

    /**
     * Assert that a response is a successful API response
     */
    protected function assertApiSuccess($response, $expectedData = null): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data'
        ]);
        
        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        
        if ($expectedData) {
            $this->assertEquals($expectedData, $responseData['data']);
        }
    }

    /**
     * Assert that a response is an API error response
     */
    protected function assertApiError($response, $expectedMessage = null, $expectedStatus = 422): void
    {
        $response->assertStatus($expectedStatus);
        $response->assertJsonStructure([
            'success',
            'message'
        ]);
        
        $responseData = $response->json();
        $this->assertFalse($responseData['success']);
        
        if ($expectedMessage) {
            $this->assertEquals($expectedMessage, $responseData['message']);
        }
    }

    /**
     * Assert that a response is unauthorized
     */
    protected function assertUnauthorized($response): void
    {
        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    /**
     * Create a temporary test image file
     */
    protected function createTestImageFile($filename = 'test.png', $width = 100, $height = 100)
    {
        return \Illuminate\Http\UploadedFile::fake()->image($filename, $width, $height);
    }

    /**
     * Create a temporary test file (non-image)
     */
    protected function createTestFile($filename = 'test.txt', $sizeInKB = 100)
    {
        return \Illuminate\Http\UploadedFile::fake()->create($filename, $sizeInKB);
    }

    /**
     * Clean up test files after tests
     */
    protected function tearDown(): void
    {
        // Clean up any test files created
        $testImagePath = public_path('assets/images');
        if (file_exists($testImagePath)) {
            $testFiles = glob($testImagePath . '/test_*');
            foreach ($testFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        parent::tearDown();
    }
}
