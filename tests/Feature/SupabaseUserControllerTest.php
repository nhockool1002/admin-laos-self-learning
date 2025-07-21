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

    /**
     * Create authorization headers for API authentication
     */
    protected function getAuthHeaders(): array
    {
        return [
            'Authorization' => 'Bearer test-token-123',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }

    public function test_get_users_with_valid_token()
    {
        $mockUsers = [
            ['username' => 'user1', 'email' => 'user1@test.com', 'id' => 1, 'is_admin' => false],
            ['username' => 'user2', 'email' => 'user2@test.com', 'id' => 2, 'is_admin' => false],
        ];
        $mock = \Mockery::mock(\App\Services\SupabaseService::class)->makePartial();
        $mock->shouldReceive('getUsers')->andReturn($mockUsers);
        $this->app->instance(\App\Services\SupabaseService::class, $mock);
        $response = $this->getJson('/supabase/users', $this->getAuthHeaders());
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['username' => 'user1'])
            ->assertJsonFragment(['username' => 'user2']);
    }

    public function test_get_users_without_token_returns_unauthorized()
    {
        $response = $this->getJson('/supabase/users');

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_create_user_with_valid_data()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123'
        ];

        // The controller adds more fields before calling service
        $expectedServiceData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => md5('password123'),
            'createdat' => \Mockery::type('Illuminate\Support\Carbon')
        ];

        $createdUser = array_merge($userData, ['id' => 3]);

        $this->supabaseServiceMock
            ->shouldReceive('createUser')
            ->with(\Mockery::on(function ($arg) use ($expectedServiceData) {
                return $arg['username'] === $expectedServiceData['username'] &&
                       $arg['email'] === $expectedServiceData['email'] &&
                       $arg['password'] === $expectedServiceData['password'] &&
                       isset($arg['createdat']);
            }))
            ->once()
            ->andReturn($createdUser);

        $response = $this->postJson('/supabase/users', $userData, $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJsonFragment(['username' => 'newuser'])
            ->assertJsonFragment(['email' => 'newuser@example.com']);
    }

    public function test_create_user_missing_required_fields()
    {
        $userData = [
            'email' => 'newuser@example.com'
            // Missing username and password
        ];

        $response = $this->postJson('/supabase/users', $userData, $this->getAuthHeaders());

        $response->assertStatus(422)
            ->assertJson(['error' => 'Thiếu thông tin bắt buộc']);
    }

    public function test_create_user_service_failure()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123'
        ];

        $this->supabaseServiceMock
            ->shouldReceive('createUser')
            ->once()
            ->andReturn(false);

        $response = $this->postJson('/supabase/users', $userData, $this->getAuthHeaders());

        $response->assertStatus(500)
            ->assertJson(['error' => 'Tạo user thất bại']);
    }

    public function test_update_user()
    {
        $updateData = [
            'email' => 'updated@example.com'
        ];

        $updatedUser = array_merge($updateData, ['id' => 1, 'username' => 'testuser']);

        $this->supabaseServiceMock
            ->shouldReceive('updateUser')
            ->with('testuser', $updateData)
            ->once()
            ->andReturn($updatedUser);

        $response = $this->putJson('/supabase/users/testuser', $updateData, $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => 'updated@example.com']);
    }

    public function test_update_user_service_failure()
    {
        $updateData = ['email' => 'updated@example.com'];

        $this->supabaseServiceMock
            ->shouldReceive('updateUser')
            ->with('testuser', $updateData)
            ->once()
            ->andReturn(false);

        $response = $this->putJson('/supabase/users/testuser', $updateData, $this->getAuthHeaders());

        $response->assertStatus(500)
            ->assertJson(['error' => 'Cập nhật user thất bại']);
    }

    public function test_delete_user()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteUser')
            ->with('testuser')
            ->once()
            ->andReturn(true);

        $response = $this->deleteJson('/supabase/users/testuser', [], $this->getAuthHeaders());

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_delete_user_service_failure()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteUser')
            ->with('testuser')
            ->once()
            ->andReturn(false);

        $response = $this->deleteJson('/supabase/users/testuser', [], $this->getAuthHeaders());

        $response->assertStatus(500)
            ->assertJson(['error' => 'Xoá user thất bại']);
    }

    public function test_update_root_user_is_forbidden()
    {
        $updateData = ['email' => 'newemail@example.com'];

        $response = $this->putJson('/supabase/users/nhockool1002', $updateData, $this->getAuthHeaders());

        $response->assertStatus(403)
            ->assertJson(['error' => 'Không được cập nhật user root!']);
    }

    public function test_delete_root_user_is_forbidden()
    {
        $response = $this->deleteJson('/supabase/users/nhockool1002', [], $this->getAuthHeaders());

        $response->assertStatus(403)
            ->assertJson(['error' => 'Không được xoá user root!']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}