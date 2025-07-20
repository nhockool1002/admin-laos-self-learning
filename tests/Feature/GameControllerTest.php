<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\SupabaseService;
use Mockery;

class GameControllerTest extends TestCase
{
    protected $supabaseServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock SupabaseService
        $this->supabaseServiceMock = Mockery::mock(SupabaseService::class);
        $this->app->instance(SupabaseService::class, $this->supabaseServiceMock);
    }

    public function test_get_games_returns_list()
    {
        $games = [
            [
                'id' => 1,
                'title' => 'Trò chơi từ vựng',
                'description' => 'Học từ vựng qua trò chơi',
                'type' => 'vocabulary',
                'is_active' => true
            ],
            [
                'id' => 2,
                'title' => 'Trò chơi ngữ pháp',
                'description' => 'Luyện tập ngữ pháp',
                'type' => 'grammar',
                'is_active' => true
            ]
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getGames')
            ->once()
            ->andReturn($games);

        $response = $this->getJson('/supabase/games');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['title' => 'Trò chơi từ vựng'])
            ->assertJsonFragment(['title' => 'Trò chơi ngữ pháp']);
    }

    public function test_create_game_with_valid_data()
    {
        $gameData = [
            'title' => 'Trò chơi mới',
            'description' => 'Mô tả trò chơi mới',
            'type' => 'quiz',
            'is_active' => true
        ];

        $createdGame = array_merge($gameData, ['id' => 1]);

        $this->supabaseServiceMock
            ->shouldReceive('createGame')
            ->with($gameData)
            ->once()
            ->andReturn($createdGame);

        $response = $this->postJson('/supabase/games', $gameData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Trò chơi mới'])
            ->assertJsonFragment(['id' => 1]);
    }

    public function test_create_game_service_failure()
    {
        $gameData = [
            'title' => 'Trò chơi mới',
            'description' => 'Mô tả trò chơi mới'
        ];

        $this->supabaseServiceMock
            ->shouldReceive('createGame')
            ->with($gameData)
            ->once()
            ->andReturn(false);

        $response = $this->postJson('/supabase/games', $gameData);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Tạo trò chơi thất bại']);
    }

    public function test_get_game_by_id()
    {
        $game = [
            'id' => 1,
            'title' => 'Trò chơi từ vựng',
            'description' => 'Mô tả chi tiết',
            'type' => 'vocabulary',
            'is_active' => true
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getGame')
            ->with(1)
            ->once()
            ->andReturn($game);

        $response = $this->getJson('/supabase/games/1');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => 1])
            ->assertJsonFragment(['title' => 'Trò chơi từ vựng']);
    }

    public function test_get_nonexistent_game()
    {
        $this->supabaseServiceMock
            ->shouldReceive('getGame')
            ->with(999)
            ->once()
            ->andReturn(null);

        $response = $this->getJson('/supabase/games/999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Không tìm thấy trò chơi']);
    }

    public function test_update_game_with_valid_data()
    {
        $updateData = [
            'title' => 'Trò chơi đã cập nhật',
            'description' => 'Mô tả mới'
        ];

        $updatedGame = array_merge($updateData, ['id' => 1]);

        $this->supabaseServiceMock
            ->shouldReceive('updateGame')
            ->with(1, $updateData)
            ->once()
            ->andReturn($updatedGame);

        $response = $this->putJson('/supabase/games/1', $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Trò chơi đã cập nhật']);
    }

    public function test_update_game_service_failure()
    {
        $updateData = ['title' => 'Trò chơi đã cập nhật'];

        $this->supabaseServiceMock
            ->shouldReceive('updateGame')
            ->with(1, $updateData)
            ->once()
            ->andReturn(false);

        $response = $this->putJson('/supabase/games/1', $updateData);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Cập nhật trò chơi thất bại']);
    }

    public function test_delete_game_success()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteGame')
            ->with(1)
            ->once()
            ->andReturn(true);

        $response = $this->deleteJson('/supabase/games/1');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Xóa trò chơi thành công']);
    }

    public function test_delete_game_failure()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteGame')
            ->with(1)
            ->once()
            ->andReturn(false);

        $response = $this->deleteJson('/supabase/games/1');

        $response->assertStatus(500)
            ->assertJson(['error' => 'Xóa trò chơi thất bại']);
    }

    // Game Groups Tests

    public function test_get_game_groups_returns_list()
    {
        $gameGroups = [
            [
                'id' => 1,
                'name' => 'Nhóm từ vựng cơ bản',
                'description' => 'Các từ vựng cơ bản trong tiếng Lào',
                'is_active' => true
            ],
            [
                'id' => 2,
                'name' => 'Nhóm ngữ pháp',
                'description' => 'Các bài tập ngữ pháp',
                'is_active' => true
            ]
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getGameGroups')
            ->once()
            ->andReturn($gameGroups);

        $response = $this->getJson('/supabase/game-groups');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Nhóm từ vựng cơ bản'])
            ->assertJsonFragment(['name' => 'Nhóm ngữ pháp']);
    }

    public function test_create_game_group_with_valid_data()
    {
        $groupData = [
            'name' => 'Nhóm mới',
            'description' => 'Mô tả nhóm mới',
            'is_active' => true
        ];

        $createdGroup = array_merge($groupData, ['id' => 1]);

        $this->supabaseServiceMock
            ->shouldReceive('createGameGroup')
            ->with($groupData)
            ->once()
            ->andReturn($createdGroup);

        $response = $this->postJson('/supabase/game-groups', $groupData);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Nhóm mới'])
            ->assertJsonFragment(['id' => 1]);
    }

    public function test_create_game_group_service_failure()
    {
        $groupData = [
            'name' => 'Nhóm mới',
            'description' => 'Mô tả nhóm mới'
        ];

        $this->supabaseServiceMock
            ->shouldReceive('createGameGroup')
            ->with($groupData)
            ->once()
            ->andReturn(false);

        $response = $this->postJson('/supabase/game-groups', $groupData);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Tạo nhóm trò chơi thất bại']);
    }

    public function test_get_game_group_by_id()
    {
        $gameGroup = [
            'id' => 1,
            'name' => 'Nhóm từ vựng',
            'description' => 'Mô tả chi tiết',
            'is_active' => true
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getGameGroup')
            ->with(1)
            ->once()
            ->andReturn($gameGroup);

        $response = $this->getJson('/supabase/game-groups/1');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => 1])
            ->assertJsonFragment(['name' => 'Nhóm từ vựng']);
    }

    public function test_get_nonexistent_game_group()
    {
        $this->supabaseServiceMock
            ->shouldReceive('getGameGroup')
            ->with(999)
            ->once()
            ->andReturn(null);

        $response = $this->getJson('/supabase/game-groups/999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Không tìm thấy nhóm trò chơi']);
    }

    public function test_update_game_group_with_valid_data()
    {
        $updateData = [
            'name' => 'Nhóm đã cập nhật',
            'description' => 'Mô tả mới'
        ];

        $updatedGroup = array_merge($updateData, ['id' => 1]);

        $this->supabaseServiceMock
            ->shouldReceive('updateGameGroup')
            ->with(1, $updateData)
            ->once()
            ->andReturn($updatedGroup);

        $response = $this->putJson('/supabase/game-groups/1', $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Nhóm đã cập nhật']);
    }

    public function test_update_game_group_service_failure()
    {
        $updateData = ['name' => 'Nhóm đã cập nhật'];

        $this->supabaseServiceMock
            ->shouldReceive('updateGameGroup')
            ->with(1, $updateData)
            ->once()
            ->andReturn(false);

        $response = $this->putJson('/supabase/game-groups/1', $updateData);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Cập nhật nhóm trò chơi thất bại']);
    }

    public function test_delete_game_group_success()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteGameGroup')
            ->with(1)
            ->once()
            ->andReturn(true);

        $response = $this->deleteJson('/supabase/game-groups/1');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Xóa nhóm trò chơi thành công']);
    }

    public function test_delete_game_group_failure()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteGameGroup')
            ->with(1)
            ->once()
            ->andReturn(false);

        $response = $this->deleteJson('/supabase/game-groups/1');

        $response->assertStatus(500)
            ->assertJson(['error' => 'Xóa nhóm trò chơi thất bại']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}