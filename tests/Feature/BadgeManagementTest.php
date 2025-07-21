<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BadgeManagementTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Supabase configuration
        config(['services.supabase.url' => 'https://test.supabase.co']);
        config(['services.supabase.anon_key' => 'test-key']);
        
        // Create test image directory
        Storage::fake('public');
        if (!file_exists(public_path('assets/images'))) {
            mkdir(public_path('assets/images'), 0755, true);
        }
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
            ['id' => 1, 'name' => 'First Badge', 'description' => 'First badge description'],
            ['id' => 2, 'name' => 'Second Badge', 'description' => 'Second badge description']
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::response($mockBadges, 200)
        ]);

        $response = $this->withHeaders($this->adminHeaders())
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

        $imageFile = UploadedFile::fake()->image('badge.png', 100, 100);

        $response = $this->withHeaders($this->adminHeaders())
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
        $response = $this->withHeaders($this->adminHeaders())
                         ->post('/supabase/badges', []);

        $response->assertStatus(422);
    }

    public function test_create_badge_validates_image_file()
    {
        $textFile = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->withHeaders($this->adminHeaders())
                         ->post('/supabase/badges', [
                             'name' => 'Test Badge',
                             'description' => 'Test Description',
                             'image' => $textFile
                         ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_get_badge_details()
    {
        $mockBadge = [
            'id' => 1,
            'name' => 'Test Badge',
            'description' => 'Test Description',
            'image_url' => '/assets/images/badge.png'
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::response([$mockBadge], 200)
        ]);

        $response = $this->withHeaders($this->adminHeaders())
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

        $response = $this->withHeaders($this->adminHeaders())
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

        $response = $this->withHeaders($this->adminHeaders())
                         ->delete('/supabase/badges/1');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // Test User Badge Management
    public function test_admin_can_award_badge_to_user()
    {
        $mockUserBadge = [
            'id' => 1,
            'user_id' => 123,
            'badge_id' => 1,
            'awarded_at' => '2024-01-15T10:30:00Z'
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::sequence()
                ->push([], 200) // checkUserBadgeExists call (empty = doesn't exist)
                ->push([$mockUserBadge], 201) // awardUserBadge call
        ]);

        $response = $this->withHeaders($this->adminHeaders())
                         ->post('/supabase/user-badges/award', [
                             'user_id' => 123,
                             'badge_id' => 1
                         ]);

        $response->assertStatus(200);
        $response->assertJson($mockUserBadge);
    }

    public function test_award_badge_prevents_duplicates()
    {
        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::response([['id' => 1]], 200) // Badge exists
        ]);

        $response = $this->withHeaders($this->adminHeaders())
                         ->post('/supabase/user-badges/award', [
                             'user_id' => 123,
                             'badge_id' => 1
                         ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'User đã có huy hiệu này rồi!']);
    }

    public function test_admin_can_revoke_badge_from_user()
    {
        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::response([], 204)
        ]);

        $response = $this->withHeaders($this->adminHeaders())
                         ->post('/supabase/user-badges/revoke', [
                             'user_id' => 123,
                             'badge_id' => 1
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

        $response = $this->withHeaders($this->adminHeaders())
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
                        'badges' => ['id' => 1, 'name' => 'Test Badge']
                    ]
                ]
            ]
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/users*' => Http::response($mockUsersWithBadges, 200)
        ]);

        $response = $this->withHeaders($this->adminHeaders())
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
        $mockBadge = ['id' => 1, 'name' => 'Test Badge'];

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
                'badges' => ['id' => 1, 'name' => 'Test Badge']
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
            'user_id' => 123,
            'badge_id' => 1,
            'awarded_at' => '2024-01-15T10:30:00Z'
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::sequence()
                ->push([], 200) // checkUserBadgeExists
                ->push([$mockUserBadge], 201) // awardUserBadge
        ]);

        $response = $this->postJson('/api/v1/badges/award', [
            'user_id' => 123,
            'badge_id' => 1
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => $mockUserBadge
        ]);
    }

    public function test_public_api_award_prevents_duplicates()
    {
        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::response([['id' => 1]], 200)
        ]);

        $response = $this->postJson('/api/v1/badges/award', [
            'user_id' => 123,
            'badge_id' => 1
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'User already has this badge'
        ]);
    }

    public function test_public_api_can_revoke_badge()
    {
        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::response([], 204)
        ]);

        $response = $this->postJson('/api/v1/badges/revoke', [
            'user_id' => 123,
            'badge_id' => 1
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Badge revoked successfully'
        ]);
    }

    // Test Validation
    public function test_award_badge_requires_valid_data()
    {
        $response = $this->withHeaders($this->adminHeaders())
                         ->postJson('/supabase/user-badges/award', []);

        $response->assertStatus(422);
    }

    public function test_revoke_badge_requires_valid_data()
    {
        $response = $this->withHeaders($this->adminHeaders())
                         ->postJson('/supabase/user-badges/revoke', []);

        $response->assertStatus(422);
    }

    // Test Error Handling
    public function test_handles_supabase_service_errors_gracefully()
    {
        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::response([], 500)
        ]);

        $response = $this->withHeaders($this->adminHeaders())
                         ->get('/supabase/badges');

        $response->assertStatus(200);
        $response->assertJson(null);
    }

    // Test File Upload Edge Cases
    public function test_create_badge_handles_large_files()
    {
        $largeFile = UploadedFile::fake()->image('large.png')->size(3000); // 3MB

        $response = $this->withHeaders($this->adminHeaders())
                         ->post('/supabase/badges', [
                             'name' => 'Test Badge',
                             'description' => 'Test Description',
                             'image' => $largeFile
                         ]);

        $response->assertStatus(422);
    }

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

        $newImage = UploadedFile::fake()->image('new.png', 100, 100);

        $response = $this->withHeaders($this->adminHeaders())
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
        // 1. Create badge
        $mockBadge = [
            'id' => 1,
            'name' => 'Lifecycle Badge',
            'description' => 'Test badge lifecycle',
            'image_url' => '/assets/images/lifecycle.png'
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/badges' => Http::response([$mockBadge], 201)
        ]);

        $image = UploadedFile::fake()->image('lifecycle.png', 100, 100);

        $createResponse = $this->withHeaders($this->adminHeaders())
                               ->post('/supabase/badges', [
                                   'name' => 'Lifecycle Badge',
                                   'description' => 'Test badge lifecycle',
                                   'image' => $image
                               ]);

        $createResponse->assertStatus(200);

        // 2. Award badge to user
        $mockUserBadge = [
            'id' => 1,
            'user_id' => 123,
            'badge_id' => 1,
            'awarded_at' => '2024-01-15T10:30:00Z'
        ];

        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::sequence()
                ->push([], 200) // Badge doesn't exist for user
                ->push([$mockUserBadge], 201) // Award badge
        ]);

        $awardResponse = $this->withHeaders($this->adminHeaders())
                              ->post('/supabase/user-badges/award', [
                                  'user_id' => 123,
                                  'badge_id' => 1
                              ]);

        $awardResponse->assertStatus(200);

        // 3. Revoke badge from user
        Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => Http::response([], 204)
        ]);

        $revokeResponse = $this->withHeaders($this->adminHeaders())
                               ->post('/supabase/user-badges/revoke', [
                                   'user_id' => 123,
                                   'badge_id' => 1
                               ]);

        $revokeResponse->assertStatus(200);

        // 4. Delete badge
        Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => Http::sequence()
                ->push($mockBadge, 200) // Get badge for cleanup
                ->push([], 204) // Delete badge
        ]);

        $deleteResponse = $this->withHeaders($this->adminHeaders())
                               ->delete('/supabase/badges/1');

        $deleteResponse->assertStatus(200);
    }
}