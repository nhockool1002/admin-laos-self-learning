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
                'description' => 'Học ngữ pháp qua trò chơi',
                'type' => 'grammar',
                'is_active' => true
            ]
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getFlashGames')
            ->once()
            ->andReturn($games);

        $response = $this->getJson('/supabase/games');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['title' => 'Trò chơi từ vựng'])
            ->assertJsonFragment(['title' => 'Trò chơi ngữ pháp']);
    }

    public function test_get_game_by_id()
    {
        $game = [
            'id' => 1,
            'title' => 'Trò chơi từ vựng',
            'description' => 'Học từ vựng qua trò chơi',
            'type' => 'vocabulary',
            'is_active' => true
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getFlashGameById')
            ->with(1)
            ->once()
            ->andReturn($game);

        $response = $this->getJson('/supabase/games/1');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Trò chơi từ vựng']);
    }

    public function test_create_game()
    {
        $gameData = [
            'title' => 'Trò chơi mới',
            'description' => 'Mô tả trò chơi mới',
            'type' => 'quiz'
        ];

        $createdGame = array_merge($gameData, ['id' => 3]);

        $this->supabaseServiceMock
            ->shouldReceive('createFlashGame')
            ->with($gameData)
            ->once()
            ->andReturn($createdGame);

        $response = $this->postJson('/supabase/games', $gameData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Trò chơi mới']);
    }

    public function test_update_game()
    {
        $updateData = [
            'title' => 'Trò chơi đã cập nhật',
            'description' => 'Mô tả đã cập nhật'
        ];

        $updatedGame = array_merge($updateData, ['id' => 1]);

        $this->supabaseServiceMock
            ->shouldReceive('updateFlashGame')
            ->with(1, $updateData)
            ->once()
            ->andReturn($updatedGame);

        $response = $this->putJson('/supabase/games/1', $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Trò chơi đã cập nhật']);
    }

    public function test_delete_game()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteFlashGame')
            ->with(1)
            ->once()
            ->andReturn(true);

        $response = $this->deleteJson('/supabase/games/1');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_game_groups_returns_list()
    {
        $groups = [
            [
                'id' => 1,
                'name' => 'Nhóm trò chơi cơ bản',
                'description' => 'Các trò chơi dành cho người mới',
                'is_active' => true
            ],
            [
                'id' => 2,
                'name' => 'Nhóm trò chơi nâng cao',
                'description' => 'Các trò chơi dành cho người có kinh nghiệm',
                'is_active' => true
            ]
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getGameGroups')
            ->once()
            ->andReturn($groups);

        $response = $this->getJson('/supabase/game-groups');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Nhóm trò chơi cơ bản'])
            ->assertJsonFragment(['name' => 'Nhóm trò chơi nâng cao']);
    }

    public function test_get_game_group_by_id()
    {
        $group = [
            'id' => 1,
            'name' => 'Nhóm trò chơi cơ bản',
            'description' => 'Các trò chơi dành cho người mới',
            'is_active' => true
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getGameGroupById')
            ->with(1)
            ->once()
            ->andReturn($group);

        $response = $this->getJson('/supabase/game-groups/1');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Nhóm trò chơi cơ bản']);
    }

    public function test_create_game_group()
    {
        $groupData = [
            'name' => 'Nhóm trò chơi mới',
            'description' => 'Mô tả nhóm mới'
        ];

        $createdGroup = array_merge($groupData, ['id' => 3]);

        $this->supabaseServiceMock
            ->shouldReceive('createGameGroup')
            ->with($groupData)
            ->once()
            ->andReturn($createdGroup);

        $response = $this->postJson('/supabase/game-groups', $groupData);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Nhóm trò chơi mới']);
    }

    public function test_update_game_group()
    {
        $updateData = [
            'name' => 'Nhóm đã cập nhật',
            'description' => 'Mô tả đã cập nhật'
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

    public function test_delete_game_group()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteGameGroup')
            ->with(1)
            ->once()
            ->andReturn(true);

        $response = $this->deleteJson('/supabase/game-groups/1');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}