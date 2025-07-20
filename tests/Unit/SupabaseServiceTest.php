<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseServiceTest extends TestCase
{
    protected $supabaseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->supabaseService = new SupabaseService();
        
        // Mock Log facade
        Log::shouldReceive('debug')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);
    }

    public function test_supabase_service_initializes_correctly()
    {
        $this->assertInstanceOf(SupabaseService::class, $this->supabaseService);
    }

    public function test_get_users_returns_array()
    {
        // Mock HTTP response
        Http::fake([
            '*' => Http::response([
                [
                    'id' => 1,
                    'username' => 'testuser',
                    'email' => 'test@example.com'
                ]
            ], 200)
        ]);

        $users = $this->supabaseService->getUsers();
        
        $this->assertIsArray($users);
        $this->assertCount(1, $users);
        $this->assertEquals('testuser', $users[0]['username']);
    }

    public function test_get_user_by_email_returns_user()
    {
        Http::fake([
            '*' => Http::response([
                [
                    'id' => 1,
                    'email' => 'test@example.com',
                    'username' => 'testuser'
                ]
            ], 200)
        ]);

        $user = $this->supabaseService->getUserByEmail('test@example.com');
        
        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user['email']);
    }

    public function test_get_user_by_username_returns_user()
    {
        Http::fake([
            '*' => Http::response([
                [
                    'id' => 1,
                    'username' => 'testuser',
                    'email' => 'test@example.com'
                ]
            ], 200)
        ]);

        $user = $this->supabaseService->getUserByUsername('testuser');
        
        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user['username']);
    }

    public function test_create_user_returns_created_user()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => md5('password123')
        ];

        Http::fake([
            '*' => Http::response([
                [
                    'id' => 1,
                    'username' => 'newuser',
                    'email' => 'new@example.com'
                ]
            ], 201)
        ]);

        $user = $this->supabaseService->createUser($userData);
        
        $this->assertNotNull($user);
        $this->assertEquals('newuser', $user['username']);
    }

    public function test_update_user_returns_updated_user()
    {
        $userData = [
            'username' => 'updateduser',
            'email' => 'updated@example.com'
        ];

        Http::fake([
            '*' => Http::response([
                [
                    'id' => 1,
                    'username' => 'updateduser',
                    'email' => 'updated@example.com'
                ]
            ], 200)
        ]);

        $user = $this->supabaseService->updateUser('testuser', $userData);
        
        $this->assertNotNull($user);
        $this->assertEquals('updateduser', $user['username']);
    }

    public function test_delete_user_returns_success()
    {
        Http::fake([
            '*' => Http::response([], 204)
        ]);

        $result = $this->supabaseService->deleteUser('testuser');
        
        $this->assertTrue($result);
    }

    public function test_get_courses_returns_array()
    {
        Http::fake([
            '*' => Http::response([
                [
                    'id' => 1,
                    'title' => 'Test Course',
                    'description' => 'A test course'
                ]
            ], 200)
        ]);

        $courses = $this->supabaseService->getCourses();
        
        $this->assertIsArray($courses);
        $this->assertCount(1, $courses);
    }

    public function test_http_error_handling()
    {
        Http::fake([
            '*' => Http::response([
                'error' => 'Internal Server Error'
            ], 500)
        ]);

        $users = $this->supabaseService->getUsers();
        
        $this->assertEmpty($users);
    }

    public function test_network_error_handling()
    {
        Http::fake([
            '*' => Http::response([
                'error' => 'Network Error'
            ], 503)
        ]);

        $users = $this->supabaseService->getUsers();
        
        $this->assertEmpty($users);
    }
}