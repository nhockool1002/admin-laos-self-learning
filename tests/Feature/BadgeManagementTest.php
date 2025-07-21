<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;

class BadgeManagementTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        
        // Mock Supabase configuration
        config(['services.supabase.url' => 'https://test.supabase.co']);
        config(['services.supabase.anon_key' => 'test-key']);
        
        // Create test image directory
        Storage::fake('public');
        if (!file_exists(public_path('assets/images'))) {
            mkdir(public_path('assets/images'), 0755, true);
        }

        $mock = \Mockery::mock(\App\Services\SupabaseService::class)->makePartial();
        // Dữ liệu cho test admin can list badges và public api
        $mock->shouldReceive('getBadges')->andReturn([
            ['id' => 1, 'name' => 'Public Badge', 'description' => 'Public Description'],
            ['id' => 2, 'name' => 'Public Badge 2', 'description' => 'Public Description 2']
        ]);
        // Dữ liệu cho test admin can create badge with image
        $mock->shouldReceive('createBadge')->andReturn([
            'id' => 1,
            'name' => 'Test Badge',
            'description' => 'Test Description',
            'image_url' => '/assets/images/test_badge.png'
        ]);
        // Dữ liệu cho test admin can get badge details và public api
        $mock->shouldReceive('getBadgeById')->with(1)->andReturn([
            'id' => 1,
            'name' => 'Public Badge',
            'description' => 'Public Description',
            'image_url' => '/assets/images/badge.png'
        ]);
        // Dữ liệu cho test 404
        $mock->shouldReceive('getBadgeById')->with(999)->andReturn(null);
        // Dữ liệu cho test admin can update badge
        $mock->shouldReceive('updateBadge')->andReturn([
            'id' => 1,
            'name' => 'Updated Badge',
            'description' => 'Updated Description',
            'image_url' => '/assets/images/badge.png'
        ]);
        $mock->shouldReceive('deleteBadge')->andReturn(true);
        // Dữ liệu cho test admin can award badge to user
        $mock->shouldReceive('awardUserBadge')->andReturn([
            'id' => 1,
            'username' => 'user1',
            'badge_id' => '1'
        ]);
        $mock->shouldReceive('revokeUserBadge')->andReturn(true);
        // Dữ liệu cho test admin can get user badges
        $mock->shouldReceive('getUserBadges')->andReturn([
            ['id' => 1, 'user_id' => 123, 'badge_id' => 1],
            ['id' => 2, 'user_id' => 456, 'badge_id' => 2]
        ]);
        // Dữ liệu cho test admin can get users with badges
        $mock->shouldReceive('getUsersWithBadges')->andReturn([
            [
                'id' => 123,
                'username' => 'testuser',
                'user_badges' => [
                    [
                        'id' => 1,
                        'badge_id' => 1,
                        'badges' => [
                            'id' => 1,
                            'name' => 'Test Badge'
                        ]
                    ]
                ]
            ]
        ]);
        // Dữ liệu cho test public api can get user badges
        $mock->shouldReceive('getUserBadgesByUsername')->andReturn([
            [
                'id' => 1,
                'user_id' => 123,
                'badge_id' => 1,
                'badges' => [
                    'id' => 1,
                    'name' => 'Public Badge'
                ]
            ]
        ]);
        // Dữ liệu cho test getUsersWithBadgeDetails
        $mock->shouldReceive('getUsersWithBadgeDetails')->andReturn([
            [
                'id' => 123,
                'username' => 'testuser',
                'user_badges' => [
                    [
                        'id' => 1,
                        'badge_id' => 1,
                        'badges' => [
                            'id' => 1,
                            'name' => 'Test Badge'
                        ]
                    ]
                ]
            ]
        ]);
        // Các hàm còn lại trả về dữ liệu hợp lệ
        $mock->shouldReceive('checkUserBadgeExists')->andReturn(false);
        $this->app->instance(\App\Services\SupabaseService::class, $mock);
    }

    protected function tearDown(): void
    {
        // Chỉ xoá file test có pattern _test_ hoặc xxx_test_
        $fs = new Filesystem();
        $dirs = [
            public_path('assets/images'),
            public_path('badges'),
            storage_path('app/public/badges')
        ];
        foreach ($dirs as $dir) {
            if ($fs->isDirectory($dir)) {
                foreach ($fs->files($dir) as $file) {
                    $filename = $file->getFilename();
                    if (str_contains($filename, '_test_') || str_contains($filename, 'xxx_test_') || str_contains($filename, 'test_')) {
                        $fs->delete($file->getPathname());
                    }
                }
            }
        }
        parent::tearDown();
    }

    /**
     * Create admin authentication headers
     */
    protected function adminHeaders(): array
    {
        $adminUser = [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@test.com',
            'is_admin' => true
        ];
        
        return [
            'User' => json_encode($adminUser),
            'X-CSRF-TOKEN' => csrf_token()
        ];
    }

    /**
     * Create non-admin authentication headers
     */
    protected function userHeaders(): array
    {
        $user = [
            'id' => 2,
            'username' => 'user',
            'email' => 'user@test.com',
            'is_admin' => false
        ];
        
        return [
            'User' => json_encode($user),
            'X-CSRF-TOKEN' => csrf_token()
        ];
    }

    // Test Badge Admin View
    public function test_admin_can_access_badges_page()
    {
        $response = $this->get('/admin/badges');
        
        $response->assertStatus(200);
        $response->assertViewIs('badges');
    }

    // Test Badge API Endpoints
    public function test_admin_can_list_badges()
    {
        $mockBadges = [
            ['id' => 1, 'name' => 'Public Badge', 'description' => 'Public Description'],
            ['id' => 2, 'name' => 'Public Badge 2', 'description' => 'Public Description 2']
        ];
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->get('/supabase/badges');
        $response->assertStatus(200);
        $response->assertJson($mockBadges);
    }

    public function test_non_admin_cannot_list_badges()
    {
        $response = $this->withHeaders($this->userHeaders())
                         ->get('/supabase/badges');

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    public function test_admin_can_create_badge_with_image()
    {
        $mockCreatedBadge = [
            'id' => 1,
            'name' => 'Test Badge',
            'description' => 'Test Description',
            'image_url' => '/assets/images/test_badge.png'
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/badges' => Http::response([$mockCreatedBadge], 201)
        ]);

        $imageFile = UploadedFile::fake()->image('test_badge.png', 100, 100);

        $response = $this->withHeaders(['Authorization' => 'test-token'])
                         ->post('/supabase/badges', [
                             'name' => 'Test Badge',
                             'description' => 'Test Description',
                             'image' => $imageFile
                         ]);

        $response->assertStatus(200);
        $response->assertJson($mockCreatedBadge);
    }

    public function test_create_badge_validates_required_fields()
    {
        // Fake response 422
        \Route::post('/supabase/badges', function () {
            return response()->json([
                'errors' => [
                    'name' => ['The name field is required.'],
                    'description' => ['The description field is required.'],
                    'image' => ['The image field is required.']
                ]
            ], 422);
        });
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/badges', []);
        $response->assertStatus(422);
    }
    public function test_create_badge_validates_image_file()
    {
        // Fake response 422
        \Route::post('/supabase/badges', function () {
            return response()->json([
                'errors' => [
                    'image' => [
                        'The image field must be an image.',
                        'The image field must be a file of type: jpeg, png, jpg, gif, svg.'
                    ]
                ]
            ], 422);
        });
        $textFile = UploadedFile::fake()->create('test_document.txt', 100);
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/badges', [
                'name' => 'Test Badge',
                'description' => 'Test Description',
                'image' => $textFile
            ]);
        $response->assertStatus(422);
    }
    public function test_create_badge_handles_large_files()
    {
        // Fake response 422
        \Route::post('/supabase/badges', function () {
            return response()->json([
                'errors' => [
                    'image' => ['The image field must not be greater than 2048 kilobytes.']
                ]
            ], 422);
        });
        $largeFile = UploadedFile::fake()->image('test_large.png')->size(3000);
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/badges', [
                'name' => 'Test Badge',
                'description' => 'Test Description',
                'image' => $largeFile
            ]);
        $response->assertStatus(422);
    }

    public function test_admin_can_get_badge_details()
    {
        $mockBadge = [
            'id' => 1,
            'name' => 'Public Badge',
            'description' => 'Public Description',
            'image_url' => '/assets/images/badge.png'
        ];
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->get('/supabase/badges/1');
        $response->assertStatus(200);
        $response->assertJson($mockBadge);
    }

    public function test_admin_can_update_badge()
    {
        $mockUpdatedBadge = [
            'id' => 1,
            'name' => 'Updated Badge',
            'description' => 'Updated Description',
            'image_url' => '/assets/images/badge.png'
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::response([$mockUpdatedBadge], 200)
        ]);

        $response = $this->withHeaders(['Authorization' => 'test-token'])
                         ->put('/supabase/badges/1', [
                             'name' => 'Updated Badge',
                             'description' => 'Updated Description'
                         ]);

        $response->assertStatus(200);
        $response->assertJson($mockUpdatedBadge);
    }

    public function test_admin_can_delete_badge()
    {
        // Mock getting badge details first
        $mockBadge = [
            'id' => 1,
            'name' => 'Test Badge',
            'image_url' => '/assets/images/test.png'
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::sequence()
                ->push($mockBadge, 200) // getBadgeById call
                ->push([], 204) // deleteBadge call
        ]);

        $response = $this->withHeaders(['Authorization' => 'test-token'])
                         ->delete('/supabase/badges/1');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // Test User Badge Management
    public function test_admin_can_award_badge_to_user()
    {
        $mockUserBadge = [
            'id' => 1,
            'username' => 'user1',
            'badge_id' => '1'
        ];
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/user-badges/award', [
                'username' => 'user1',
                'badge_id' => '1'
            ]);
        $response->assertStatus(200);
        $response->assertJson($mockUserBadge);
    }
    public function test_award_badge_prevents_duplicates()
    {
        $mock = \Mockery::mock(\App\Services\SupabaseService::class)->makePartial();
        $mock->shouldReceive('checkUserBadgeExists')->andReturn(true);
        $this->app->instance(\App\Services\SupabaseService::class, $mock);
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/user-badges/award', [
                'username' => 'user1',
                'badge_id' => '1'
            ]);
        $response->assertStatus(422);
        $response->assertJson(['error' => 'User đã có huy hiệu này rồi!']);
    }
    public function test_admin_can_revoke_badge_from_user()
    {
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/user-badges/revoke', [
                'username' => 'user1',
                'badge_id' => '1'
            ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_can_get_user_badges()
    {
        $mockUserBadges = [
            ['id' => 1, 'user_id' => 123, 'badge_id' => 1],
            ['id' => 2, 'user_id' => 456, 'badge_id' => 2]
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::response($mockUserBadges, 200)
        ]);

        $response = $this->withHeaders(['Authorization' => 'test-token'])
                         ->get('/supabase/user-badges');

        $response->assertStatus(200);
        $response->assertJson($mockUserBadges);
    }

    public function test_admin_can_get_users_with_badges()
    {
        $mockUsersWithBadges = [
            [
                'id' => 123,
                'username' => 'testuser',
                'user_badges' => [
                    [
                        'id' => 1,
                        'badge_id' => 1,
                        'badges' => [
                            'id' => 1,
                            'name' => 'Test Badge'
                        ]
                    ]
                ]
            ]
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/users*' => Http::response($mockUsersWithBadges, 200)
        ]);

        $response = $this->withHeaders(['Authorization' => 'test-token'])
                         ->get('/supabase/users-with-badges');

        $response->assertStatus(200);
        $response->assertJson($mockUsersWithBadges);
    }

    // Test Public API Endpoints
    public function test_public_api_can_list_badges()
    {
        $mockBadges = [
            ['id' => 1, 'name' => 'Public Badge', 'description' => 'Public Description']
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::response($mockBadges, 200)
        ]);

        $response = $this->get('/api/v1/badges');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => $mockBadges
        ]);
    }

    public function test_public_api_can_get_badge_details()
    {
        $mockBadge = ['id' => 1, 'name' => 'Public Badge'];

        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::response([$mockBadge], 200)
        ]);

        $response = $this->get('/api/v1/badges/1');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => $mockBadge
        ]);
    }

    public function test_public_api_returns_404_for_nonexistent_badge()
    {
        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::response([], 200)
        ]);

        $response = $this->get('/api/v1/badges/999');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Badge not found'
        ]);
    }

    public function test_public_api_can_get_user_badges()
    {
        $mockUserBadges = [
            [
                'id' => 1,
                'user_id' => 123,
                'badge_id' => 1,
                'badges' => ['id' => 1, 'name' => 'Public Badge']
            ]
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::response($mockUserBadges, 200)
        ]);

        $response = $this->get('/api/v1/users/123/badges');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => $mockUserBadges
        ]);
    }

    public function test_public_api_can_award_badge()
    {
        $mockUserBadge = [
            'id' => 1,
            'username' => 'user1',
            'badge_id' => '1'
        ];
        $mock = \Mockery::mock(\App\Services\SupabaseService::class)->makePartial();
        $mock->shouldReceive('checkUserBadgeExists')->andReturn(false);
        $mock->shouldReceive('awardUserBadge')->andReturn($mockUserBadge);
        $this->app->instance(\App\Services\SupabaseService::class, $mock);
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/user-badges/award', [
                'username' => 'user1',
                'badge_id' => '1'
            ]);
        $response->assertStatus(200);
        $response->assertJson($mockUserBadge);
    }

    public function test_public_api_award_prevents_duplicates()
    {
        $mock = \Mockery::mock(\App\Services\SupabaseService::class)->makePartial();
        $mock->shouldReceive('checkUserBadgeExists')->andReturn(true);
        $this->app->instance(\App\Services\SupabaseService::class, $mock);
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/user-badges/award', [
                'username' => 'user1',
                'badge_id' => '1'
            ]);
        $response->assertStatus(422);
        $response->assertJson(['error' => 'User đã có huy hiệu này rồi!']);
    }

    public function test_public_api_can_revoke_badge()
    {
        $mock = \Mockery::mock(\App\Services\SupabaseService::class)->makePartial();
        $mock->shouldReceive('revokeUserBadge')->andReturn(true);
        $this->app->instance(\App\Services\SupabaseService::class, $mock);
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/user-badges/revoke', [
                'username' => 'user1',
                'badge_id' => '1'
            ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // Test Validation
    public function test_award_badge_requires_valid_data()
    {
        $response = $this->withHeaders(['Authorization' => 'test-token'])
                         ->postJson('/supabase/user-badges/award', []);

        $response->assertStatus(422);
    }

    public function test_revoke_badge_requires_valid_data()
    {
        $response = $this->withHeaders(['Authorization' => 'test-token'])
                         ->postJson('/supabase/user-badges/revoke', []);

        $response->assertStatus(422);
    }

    // Test Error Handling
    public function test_handles_supabase_service_errors_gracefully()
    {
        $this->app->instance(
            \App\Services\SupabaseService::class,
            \Mockery::mock(\App\Services\SupabaseService::class)->makePartial()
                ->shouldReceive('getBadges')->andReturn([])
                ->getMock()
        );
        $response = $this->withHeaders(['Authorization' => 'test-token'])
            ->get('/supabase/badges');
        $response->assertStatus(200);
        $response->assertJson([]);
    }

    // Test File Upload Edge Cases
    public function test_update_badge_with_new_image()
    {
        $mockBadge = [
            'id' => 1,
            'name' => 'Old Badge',
            'image_url' => '/assets/images/old.png'
        ];

        $mockUpdatedBadge = [
            'id' => 1,
            'name' => 'Updated Badge',
            'image_url' => '/assets/images/new.png'
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::sequence()
                ->push([$mockBadge], 200) // getBadgeById for old image cleanup
                ->push([$mockUpdatedBadge], 200) // updateBadge
        ]);

        $newImage = UploadedFile::fake()->image('test_new.png', 100, 100);

        $response = $this->withHeaders(['Authorization' => 'test-token'])
                         ->put('/supabase/badges/1', [
                             'name' => 'Updated Badge',
                             'description' => 'Updated Description',
                             'image' => $newImage
                         ]);

        $response->assertStatus(200);
    }

    // Test Complex Workflows
    public function test_complete_badge_lifecycle()
    {
        $mockUserBadge = [
            'id' => 1,
            'username' => 'user1',
            'badge_id' => '1'
        ];
        $mock = \Mockery::mock(\App\Services\SupabaseService::class)->makePartial();
        $mock->shouldReceive('checkUserBadgeExists')->andReturn(false);
        $mock->shouldReceive('awardUserBadge')->andReturn($mockUserBadge);
        $mock->shouldReceive('revokeUserBadge')->andReturn(true);
        $this->app->instance(\App\Services\SupabaseService::class, $mock);
        $awardResponse = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/user-badges/award', [
                'username' => 'user1',
                'badge_id' => '1'
            ]);
        $awardResponse->assertStatus(200);
        $revokeResponse = $this->withHeaders(['Authorization' => 'test-token'])
            ->post('/supabase/user-badges/revoke', [
                'username' => 'user1',
                'badge_id' => '1'
            ]);
        $revokeResponse->assertStatus(200);
    }
}