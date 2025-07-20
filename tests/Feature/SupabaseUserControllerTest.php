<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\SupabaseService;
use Mockery;

class SupabaseUserControllerTest extends TestCase
{
    protected $supabaseServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock SupabaseService
        $this->supabaseServiceMock = Mockery::mock(SupabaseService::class);
        $this->app->instance(SupabaseService::class, $this->supabaseServiceMock);
    }

    public function test_get_users_with_valid_token()
    {
        $users = [
            $this->createMockUser(['id' => 1, 'username' => 'user1']),
            $this->createMockUser(['id' => 2, 'username' => 'user2'])
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getUsers')
            ->once()
            ->andReturn($users);

        $headers = $this->authHeaders();

        $response = $this->getJson('/supabase/users', $headers);

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['username' => 'user1'])
            ->assertJsonFragment(['username' => 'user2']);

        // Verify passwords are removed
        $responseData = $response->json();
        foreach ($responseData as $user) {
            $this->assertArrayNotHasKey('password', $user);
        }
    }

    public function test_get_users_without_token()
    {
        $response = $this->getJson('/supabase/users');

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_get_users_with_query_parameters()
    {
        $users = [$this->createMockUser()];

        $this->supabaseServiceMock
            ->shouldReceive('getUsers')
            ->with(['search' => 'test', 'limit' => 10])
            ->once()
            ->andReturn($users);

        $headers = $this->authHeaders();

        $response = $this->getJson('/supabase/users?search=test&limit=10', $headers);

        $response->assertStatus(200);
    }

    public function test_create_user_with_valid_data()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'password123',
            'is_admin' => false
        ];

        $createdUser = $this->createMockUser([
            'username' => 'newuser',
            'email' => 'new@example.com',
            'is_admin' => false
        ]);

        $this->supabaseServiceMock
            ->shouldReceive('createUser')
            ->once()
            ->with(\Mockery::on(function ($arg) {
                return $arg['username'] === 'newuser' 
                    && $arg['email'] === 'new@example.com'
                    && $arg['password'] === md5('password123')
                    && $arg['is_admin'] === false
                    && isset($arg['createdat']);
            }))
            ->andReturn($createdUser);

        $headers = $this->authHeaders();

        $response = $this->postJson('/supabase/users', $userData, $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['username' => 'newuser'])
            ->assertJsonFragment(['email' => 'new@example.com']);

        // Verify password is removed from response
        $this->assertArrayNotHasKey('password', $response->json());
    }

    public function test_create_user_with_missing_required_fields()
    {
        $incompleteData = [
            'username' => 'newuser'
            // Missing email and password
        ];

        $headers = $this->authHeaders();

        $response = $this->postJson('/supabase/users', $incompleteData, $headers);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Thiếu thông tin bắt buộc']);
    }

    public function test_create_user_service_failure()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'password123',
            'is_admin' => false
        ];

        $this->supabaseServiceMock
            ->shouldReceive('createUser')
            ->once()
            ->andReturn(false); // Service returns false on failure

        $headers = $this->authHeaders();

        $response = $this->postJson('/supabase/users', $userData, $headers);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Tạo user thất bại']);
    }

    public function test_update_user_with_valid_data()
    {
        $updateData = [
            'username' => 'updateduser',
            'email' => 'updated@example.com'
        ];

        $updatedUser = $this->createMockUser([
            'username' => 'updateduser',
            'email' => 'updated@example.com'
        ]);

        $this->supabaseServiceMock
            ->shouldReceive('updateUser')
            ->with('testuser', $updateData)
            ->once()
            ->andReturn($updatedUser);

        $headers = $this->authHeaders();

        $response = $this->putJson('/supabase/users/testuser', $updateData, $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['username' => 'updateduser']);

        // Verify password is removed from response
        $this->assertArrayNotHasKey('password', $response->json());
    }

    public function test_update_user_service_failure()
    {
        $updateData = ['username' => 'updateduser'];

        $this->supabaseServiceMock
            ->shouldReceive('updateUser')
            ->with('testuser', $updateData)
            ->once()
            ->andReturn(false);

        $headers = $this->authHeaders();

        $response = $this->putJson('/supabase/users/testuser', $updateData, $headers);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Cập nhật user thất bại']);
    }

    public function test_delete_user_success()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteUser')
            ->with('testuser')
            ->once()
            ->andReturn(true);

        $headers = $this->authHeaders();

        $response = $this->deleteJson('/supabase/users/testuser', [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Xóa user thành công']);
    }

    public function test_delete_user_failure()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteUser')
            ->with('testuser')
            ->once()
            ->andReturn(false);

        $headers = $this->authHeaders();

        $response = $this->deleteJson('/supabase/users/testuser', [], $headers);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Xóa user thất bại']);
    }

    public function test_update_user_role_success()
    {
        $roleData = ['is_admin' => true];

        $updatedUser = $this->createMockUser(['is_admin' => true]);

        $this->supabaseServiceMock
            ->shouldReceive('updateUser')
            ->with('testuser', $roleData)
            ->once()
            ->andReturn($updatedUser);

        $headers = $this->authHeaders();

        $response = $this->patchJson('/supabase/users/testuser/role', $roleData, $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['is_admin' => true]);

        // Verify password is removed from response
        $this->assertArrayNotHasKey('password', $response->json());
    }

    public function test_update_user_role_failure()
    {
        $roleData = ['is_admin' => true];

        $this->supabaseServiceMock
            ->shouldReceive('updateUser')
            ->with('testuser', $roleData)
            ->once()
            ->andReturn(false);

        $headers = $this->authHeaders();

        $response = $this->patchJson('/supabase/users/testuser/role', $roleData, $headers);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Cập nhật role thất bại']);
    }

    public function test_all_endpoints_require_authentication()
    {
        $endpoints = [
            ['GET', '/supabase/users'],
            ['POST', '/supabase/users'],
            ['PUT', '/supabase/users/testuser'],
            ['DELETE', '/supabase/users/testuser'],
            ['PATCH', '/supabase/users/testuser/role']
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}