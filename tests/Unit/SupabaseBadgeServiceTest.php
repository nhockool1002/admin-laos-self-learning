<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseBadgeServiceTest extends TestCase
{
    protected $supabaseService;
    protected $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock configuration
        config(['services.supabase.url' => 'https://test.supabase.co']);
        config(['services.supabase.anon_key' => 'test-key']);
        
        $this->supabaseService = new SupabaseService();
        $this->baseUrl = 'https://test.supabase.co/rest/v1';
        
        // Mock Log facade
        Log::shouldReceive('debug')->andReturn(null);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // Test getBadges
    public function test_get_badges_returns_array_on_success()
    {
        $mockResponse = [
            ['id' => 1, 'name' => 'Badge 1', 'description' => 'First badge'],
            ['id' => 2, 'name' => 'Badge 2', 'description' => 'Second badge']
        ];

        Http::fake([
            $this->baseUrl . '/badges*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->supabaseService->getBadges();

        $this->assertEquals($mockResponse, $result);
    }

    public function test_get_badges_returns_null_on_failure()
    {
        Http::fake([
            $this->baseUrl . '/badges*' => Http::response([], 500)
        ]);

        $result = $this->supabaseService->getBadges();

        $this->assertNull($result);
    }

    public function test_get_badges_with_parameters()
    {
        $params = ['limit' => 10, 'offset' => 0];
        $mockResponse = [['id' => 1, 'name' => 'Badge 1']];

        Http::fake([
            $this->baseUrl . '/badges*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->supabaseService->getBadges($params);

        $this->assertEquals($mockResponse, $result);
        
        // Verify the request was made with correct parameters
        Http::assertSent(function ($request) use ($params) {
            return $request->url() === $this->baseUrl . '/badges?' . http_build_query($params);
        });
    }

    // Test getBadgeById
    public function test_get_badge_by_id_returns_badge_on_success()
    {
        $badgeId = 1;
        $mockResponse = [
            ['id' => 1, 'name' => 'Test Badge', 'description' => 'Test Description']
        ];

        Http::fake([
            $this->baseUrl . '/badges*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->supabaseService->getBadgeById($badgeId);

        $this->assertEquals($mockResponse[0], $result);
    }

    public function test_get_badge_by_id_returns_null_when_not_found()
    {
        Http::fake([
            $this->baseUrl . '/badges*' => Http::response([], 200)
        ]);

        $result = $this->supabaseService->getBadgeById(999);

        $this->assertNull($result);
    }

    public function test_get_badge_by_id_returns_null_on_error()
    {
        Http::fake([
            $this->baseUrl . '/badges*' => Http::response([], 500)
        ]);

        $result = $this->supabaseService->getBadgeById(1);

        $this->assertNull($result);
    }

    // Test createBadge
    public function test_create_badge_returns_created_badge_on_success()
    {
        $badgeData = [
            'name' => 'New Badge',
            'description' => 'New badge description',
            'image_url' => '/assets/images/badge.png'
        ];

        $mockResponse = [
            array_merge(['id' => 1], $badgeData)
        ];

        Http::fake([
            $this->baseUrl . '/badges' => Http::response($mockResponse, 201)
        ]);

        $result = $this->supabaseService->createBadge($badgeData);

        $this->assertEquals($mockResponse[0], $result);
        
        // Verify request was made with correct data
        Http::assertSent(function ($request) use ($badgeData) {
            $body = json_decode($request->body(), true);
            return $body['name'] === $badgeData['name'] &&
                   $body['description'] === $badgeData['description'] &&
                   $body['image_url'] === $badgeData['image_url'];
        });
    }

    public function test_create_badge_returns_null_on_failure()
    {
        $badgeData = ['name' => 'Test Badge'];

        Http::fake([
            $this->baseUrl . '/badges' => Http::response([], 500)
        ]);

        $result = $this->supabaseService->createBadge($badgeData);

        $this->assertNull($result);
    }

    // Test updateBadge
    public function test_update_badge_returns_updated_badge_on_success()
    {
        $badgeId = 1;
        $updateData = [
            'name' => 'Updated Badge',
            'description' => 'Updated description'
        ];

        $mockResponse = [
            array_merge(['id' => $badgeId], $updateData)
        ];

        Http::fake([
            $this->baseUrl . '/badges*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->supabaseService->updateBadge($badgeId, $updateData);

        $this->assertEquals($mockResponse[0], $result);
    }

    public function test_update_badge_returns_null_on_failure()
    {
        Http::fake([
            $this->baseUrl . '/badges*' => Http::response([], 500)
        ]);

        $result = $this->supabaseService->updateBadge(1, ['name' => 'Test']);

        $this->assertNull($result);
    }

    // Test deleteBadge
    public function test_delete_badge_returns_true_on_success()
    {
        Http::fake([
            $this->baseUrl . '/badges*' => Http::response([], 204)
        ]);

        $result = $this->supabaseService->deleteBadge(1);

        $this->assertTrue($result);
    }

    public function test_delete_badge_returns_false_on_failure()
    {
        Http::fake([
            $this->baseUrl . '/badges*' => Http::response([], 500)
        ]);

        $result = $this->supabaseService->deleteBadge(1);

        $this->assertFalse($result);
    }

    // Test getUserBadges
    public function test_get_user_badges_returns_array_on_success()
    {
        $mockResponse = [
            ['id' => 1, 'user_id' => 123, 'badge_id' => 1],
            ['id' => 2, 'user_id' => 456, 'badge_id' => 2]
        ];

        Http::fake([
            $this->baseUrl . '/user_badges*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->supabaseService->getUserBadges();

        $this->assertEquals($mockResponse, $result);
    }

    // Test getUserBadgesByUserId
    public function test_get_user_badges_by_user_id_returns_user_badges()
    {
        $userId = 123;
        $mockResponse = [
            [
                'id' => 1,
                'user_id' => 123,
                'badge_id' => 1,
                'badges' => ['id' => 1, 'name' => 'Test Badge']
            ]
        ];

        Http::fake([
            $this->baseUrl . '/user_badges*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->supabaseService->getUserBadgesByUserId($userId);

        $this->assertEquals($mockResponse, $result);
        
        // Verify correct query parameters
        Http::assertSent(function ($request) use ($userId) {
            return str_contains($request->url(), "user_id=eq.$userId") &&
                   str_contains($request->url(), 'select=*,badges(*)');
        });
    }

    // Test checkUserBadgeExists
    public function test_check_user_badge_exists_returns_true_when_exists()
    {
        $userId = 123;
        $badgeId = 1;
        $mockResponse = [['id' => 1]];

        Http::fake([
            $this->baseUrl . '/user_badges*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->supabaseService->checkUserBadgeExists($userId, $badgeId);

        $this->assertTrue($result);
    }

    public function test_check_user_badge_exists_returns_false_when_not_exists()
    {
        $userId = 123;
        $badgeId = 1;

        Http::fake([
            $this->baseUrl . '/user_badges*' => Http::response([], 200)
        ]);

        $result = $this->supabaseService->checkUserBadgeExists($userId, $badgeId);

        $this->assertFalse($result);
    }

    public function test_check_user_badge_exists_returns_false_on_error()
    {
        Http::fake([
            $this->baseUrl . '/user_badges*' => Http::response([], 500)
        ]);

        $result = $this->supabaseService->checkUserBadgeExists(123, 1);

        $this->assertFalse($result);
    }

    // Test awardUserBadge
    public function test_award_user_badge_returns_user_badge_on_success()
    {
        $awardData = [
            'user_id' => 123,
            'badge_id' => 1,
            'awarded_at' => '2024-01-15T10:30:00Z'
        ];

        $mockResponse = [
            array_merge(['id' => 1], $awardData)
        ];

        Http::fake([
            $this->baseUrl . '/user_badges' => Http::response($mockResponse, 201)
        ]);

        $result = $this->supabaseService->awardUserBadge($awardData);

        $this->assertEquals($mockResponse[0], $result);
    }

    public function test_award_user_badge_returns_null_on_failure()
    {
        $awardData = ['user_id' => 123, 'badge_id' => 1];

        Http::fake([
            $this->baseUrl . '/user_badges' => Http::response([], 500)
        ]);

        $result = $this->supabaseService->awardUserBadge($awardData);

        $this->assertNull($result);
    }

    // Test revokeUserBadge
    public function test_revoke_user_badge_returns_true_on_success()
    {
        $userId = 123;
        $badgeId = 1;

        Http::fake([
            $this->baseUrl . '/user_badges*' => Http::response([], 204)
        ]);

        $result = $this->supabaseService->revokeUserBadge($userId, $badgeId);

        $this->assertTrue($result);
        
        // Verify correct query parameters
        Http::assertSent(function ($request) use ($userId, $badgeId) {
            return str_contains($request->url(), "user_id=eq.$userId") &&
                   str_contains($request->url(), "badge_id=eq.$badgeId");
        });
    }

    public function test_revoke_user_badge_returns_false_on_failure()
    {
        Http::fake([
            $this->baseUrl . '/user_badges*' => Http::response([], 500)
        ]);

        $result = $this->supabaseService->revokeUserBadge(123, 1);

        $this->assertFalse($result);
    }

    // Test getUsersWithBadgeDetails
    public function test_get_users_with_badge_details_returns_users_with_badges()
    {
        $mockResponse = [
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
            $this->baseUrl . '/users*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->supabaseService->getUsersWithBadgeDetails();

        $this->assertEquals($mockResponse, $result);
        
        // Verify correct select parameter
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'select=*,user_badges(*,badges(*))');
        });
    }

    public function test_get_users_with_badge_details_with_parameters()
    {
        $params = ['limit' => 5];
        $mockResponse = [['id' => 123, 'username' => 'test']];

        Http::fake([
            $this->baseUrl . '/users*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->supabaseService->getUsersWithBadgeDetails($params);

        $this->assertEquals($mockResponse, $result);
    }

    public function test_get_users_with_badge_details_returns_null_on_failure()
    {
        Http::fake([
            $this->baseUrl . '/users*' => Http::response([], 500)
        ]);

        $result = $this->supabaseService->getUsersWithBadgeDetails();

        $this->assertNull($result);
    }

    // Test HTTP Headers
    public function test_badge_requests_include_correct_headers()
    {
        Http::fake([
            $this->baseUrl . '/badges' => Http::response([], 200)
        ]);

        $this->supabaseService->getBadges();

        Http::assertSent(function ($request) {
            $headers = $request->headers();
            return isset($headers['apikey']) && isset($headers['Authorization']);
        });
    }

    // Test Error Handling
    public function test_network_error_handling()
    {
        Http::fake(function () {
            throw new \Exception('Network error');
        });

        $result = $this->supabaseService->getBadges();
        
        // Should handle the exception gracefully
        $this->assertNull($result);
    }

    // Test Badge Data Validation
    public function test_create_badge_with_special_characters()
    {
        $badgeData = [
            'name' => 'Badge with émojis 🎉',
            'description' => 'Special chars: áéíóú ñ'
        ];

        $mockResponse = [array_merge(['id' => 1], $badgeData)];

        Http::fake([
            $this->baseUrl . '/badges' => Http::response($mockResponse, 201)
        ]);

        $result = $this->supabaseService->createBadge($badgeData);

        $this->assertEquals($mockResponse[0], $result);
    }

    // Test Edge Cases
    public function test_get_badge_by_id_with_zero_id()
    {
        Http::fake([
            $this->baseUrl . '/badges*' => Http::response([], 200)
        ]);

        $result = $this->supabaseService->getBadgeById(0);

        $this->assertNull($result);
    }

    public function test_check_user_badge_exists_with_string_ids()
    {
        Http::fake([
            $this->baseUrl . '/user_badges*' => Http::response([], 200)
        ]);

        $result = $this->supabaseService->checkUserBadgeExists('123', '1');

        $this->assertFalse($result);
        
        // Verify the IDs are properly handled in the URL
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'user_id=eq.123') &&
                   str_contains($request->url(), 'badge_id=eq.1');
        });
    }
}