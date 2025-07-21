<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\BadgeController;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Mockery;

class BadgeControllerTest extends TestCase
{
    protected $badgeController;
    protected $mockSupabaseService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock SupabaseService
        $this->mockSupabaseService = Mockery::mock(SupabaseService::class);
        $this->badgeController = new BadgeController($this->mockSupabaseService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a mock request with admin user
     */
    private function createAdminRequest($data = []): Request
    {
        $request = new Request($data);
        $adminUser = ['id' => 1, 'username' => 'admin', 'is_admin' => true];
        $request->headers->set('User', json_encode($adminUser));
        return $request;
    }

    /**
     * Create a mock request with non-admin user
     */
    private function createNonAdminRequest($data = []): Request
    {
        $request = new Request($data);
        $user = ['id' => 2, 'username' => 'user', 'is_admin' => false];
        $request->headers->set('User', json_encode($user));
        return $request;
    }

    /**
     * Create a mock request without user
     */
    private function createUnauthenticatedRequest($data = []): Request
    {
        return new Request($data);
    }

    // Test Authorization
    public function test_index_requires_admin_authorization()
    {
        $request = $this->createNonAdminRequest();
        $response = $this->badgeController->index($request);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized', $data['error']);
    }

    public function test_index_without_authentication_fails()
    {
        $request = $this->createUnauthenticatedRequest();
        $response = $this->badgeController->index($request);
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    // Test Index (List Badges)
    public function test_index_returns_badges_for_admin()
    {
        $mockBadges = [
            ['id' => 1, 'name' => 'Badge 1', 'description' => 'First badge'],
            ['id' => 2, 'name' => 'Badge 2', 'description' => 'Second badge']
        ];

        $this->mockSupabaseService
            ->shouldReceive('getBadges')
            ->once()
            ->with([])
            ->andReturn($mockBadges);

        $request = $this->createAdminRequest();
        $response = $this->badgeController->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($mockBadges, $data);
    }

    // Test Show (Get Badge Details)
    public function test_show_returns_badge_details()
    {
        $mockBadge = ['id' => 1, 'name' => 'Test Badge', 'description' => 'Test Description'];

        $this->mockSupabaseService
            ->shouldReceive('getBadgeById')
            ->once()
            ->with(1)
            ->andReturn($mockBadge);

        $request = $this->createAdminRequest();
        $response = $this->badgeController->show($request, 1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($mockBadge, $data);
    }

    public function test_show_requires_admin_authorization()
    {
        $request = $this->createNonAdminRequest();
        $response = $this->badgeController->show($request, 1);
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    // Test Store (Create Badge)
    public function test_store_creates_badge_successfully()
    {
        // Mock file upload
        $mockFile = UploadedFile::fake()->image('badge.png', 100, 100);
        
        $requestData = [
            'name' => 'New Badge',
            'description' => 'New badge description'
        ];

        $mockCreatedBadge = [
            'id' => 1,
            'name' => 'New Badge',
            'description' => 'New badge description',
            'image_url' => '/assets/images/test_badge.png'
        ];

        $this->mockSupabaseService
            ->shouldReceive('createBadge')
            ->once()
            ->andReturn($mockCreatedBadge);

        $request = $this->createAdminRequest($requestData);
        $request->files->set('image', $mockFile);

        $response = $this->badgeController->store($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($mockCreatedBadge, $data);
    }

    public function test_store_fails_without_required_fields()
    {
        $request = $this->createAdminRequest([]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->badgeController->store($request);
    }

    public function test_store_requires_admin_authorization()
    {
        $request = $this->createNonAdminRequest(['name' => 'Test']);
        $response = $this->badgeController->store($request);
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    // Test Update
    public function test_update_modifies_badge_successfully()
    {
        $requestData = [
            'name' => 'Updated Badge',
            'description' => 'Updated description'
        ];

        $mockUpdatedBadge = [
            'id' => 1,
            'name' => 'Updated Badge',
            'description' => 'Updated description',
            'image_url' => '/assets/images/badge.png'
        ];

        $this->mockSupabaseService
            ->shouldReceive('updateBadge')
            ->once()
            ->with(1, Mockery::type('array'))
            ->andReturn($mockUpdatedBadge);

        $request = $this->createAdminRequest($requestData);
        $response = $this->badgeController->update($request, 1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($mockUpdatedBadge, $data);
    }

    public function test_update_requires_admin_authorization()
    {
        $request = $this->createNonAdminRequest(['name' => 'Test']);
        $response = $this->badgeController->update($request, 1);
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    // Test Destroy (Delete Badge)
    public function test_destroy_deletes_badge_successfully()
    {
        $mockBadge = [
            'id' => 1,
            'name' => 'Test Badge',
            'image_url' => '/assets/images/test.png'
        ];

        $this->mockSupabaseService
            ->shouldReceive('getBadgeById')
            ->once()
            ->with(1)
            ->andReturn($mockBadge);

        $this->mockSupabaseService
            ->shouldReceive('deleteBadge')
            ->once()
            ->with(1)
            ->andReturn(true);

        $request = $this->createAdminRequest();
        $response = $this->badgeController->destroy($request, 1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function test_destroy_requires_admin_authorization()
    {
        $request = $this->createNonAdminRequest();
        $response = $this->badgeController->destroy($request, 1);
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    // Test Award Badge
    public function test_award_badge_successfully()
    {
        $requestData = [
            'user_id' => 123,
            'badge_id' => 1
        ];

        $mockUserBadge = [
            'id' => 1,
            'user_id' => 123,
            'badge_id' => 1,
            'awarded_at' => '2024-01-15T10:30:00Z'
        ];

        $this->mockSupabaseService
            ->shouldReceive('checkUserBadgeExists')
            ->once()
            ->with(123, 1)
            ->andReturn(false);

        $this->mockSupabaseService
            ->shouldReceive('awardUserBadge')
            ->once()
            ->andReturn($mockUserBadge);

        $request = $this->createAdminRequest($requestData);
        $response = $this->badgeController->awardBadge($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($mockUserBadge, $data);
    }

    public function test_award_badge_fails_if_user_already_has_badge()
    {
        $requestData = [
            'user_id' => 123,
            'badge_id' => 1
        ];

        $this->mockSupabaseService
            ->shouldReceive('checkUserBadgeExists')
            ->once()
            ->with(123, 1)
            ->andReturn(true);

        $request = $this->createAdminRequest($requestData);
        $response = $this->badgeController->awardBadge($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User đã có huy hiệu này rồi!', $data['error']);
    }

    public function test_award_badge_requires_valid_data()
    {
        $request = $this->createAdminRequest([]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->badgeController->awardBadge($request);
    }

    // Test Revoke Badge
    public function test_revoke_badge_successfully()
    {
        $requestData = [
            'user_id' => 123,
            'badge_id' => 1
        ];

        $this->mockSupabaseService
            ->shouldReceive('revokeUserBadge')
            ->once()
            ->with(123, 1)
            ->andReturn(true);

        $request = $this->createAdminRequest($requestData);
        $response = $this->badgeController->revokeBadge($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function test_revoke_badge_requires_admin_authorization()
    {
        $requestData = [
            'user_id' => 123,
            'badge_id' => 1
        ];

        $request = $this->createNonAdminRequest($requestData);
        $response = $this->badgeController->revokeBadge($request);
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    // Test Get User Badges
    public function test_get_user_badges_returns_data()
    {
        $mockUserBadges = [
            ['id' => 1, 'user_id' => 123, 'badge_id' => 1],
            ['id' => 2, 'user_id' => 123, 'badge_id' => 2]
        ];

        $this->mockSupabaseService
            ->shouldReceive('getUserBadges')
            ->once()
            ->andReturn($mockUserBadges);

        $request = $this->createAdminRequest();
        $response = $this->badgeController->getUserBadges($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($mockUserBadges, $data);
    }

    // Test API Methods (Public)
    public function test_api_badges_returns_public_data()
    {
        $mockBadges = [
            ['id' => 1, 'name' => 'Public Badge']
        ];

        $this->mockSupabaseService
            ->shouldReceive('getBadges')
            ->once()
            ->andReturn($mockBadges);

        $request = new Request();
        $response = $this->badgeController->apiBadges($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals($mockBadges, $data['data']);
    }

    public function test_api_badge_detail_returns_badge()
    {
        $mockBadge = ['id' => 1, 'name' => 'Test Badge'];

        $this->mockSupabaseService
            ->shouldReceive('getBadgeById')
            ->once()
            ->with(1)
            ->andReturn($mockBadge);

        $request = new Request();
        $response = $this->badgeController->apiBadgeDetail($request, 1);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals($mockBadge, $data['data']);
    }

    public function test_api_badge_detail_returns_404_for_nonexistent_badge()
    {
        $this->mockSupabaseService
            ->shouldReceive('getBadgeById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $request = new Request();
        $response = $this->badgeController->apiBadgeDetail($request, 999);

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Badge not found', $data['message']);
    }

    public function test_api_user_badges_returns_user_data()
    {
        $mockUserBadges = [
            ['id' => 1, 'user_id' => 123, 'badge_id' => 1]
        ];

        $this->mockSupabaseService
            ->shouldReceive('getUserBadgesByUserId')
            ->once()
            ->with(123)
            ->andReturn($mockUserBadges);

        $request = new Request();
        $response = $this->badgeController->apiUserBadges($request, 123);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals($mockUserBadges, $data['data']);
    }

    public function test_api_award_badge_works_without_admin()
    {
        $requestData = [
            'user_id' => 123,
            'badge_id' => 1
        ];

        $mockUserBadge = [
            'id' => 1,
            'user_id' => 123,
            'badge_id' => 1,
            'awarded_at' => '2024-01-15T10:30:00Z'
        ];

        $this->mockSupabaseService
            ->shouldReceive('checkUserBadgeExists')
            ->once()
            ->with(123, 1)
            ->andReturn(false);

        $this->mockSupabaseService
            ->shouldReceive('awardUserBadge')
            ->once()
            ->andReturn($mockUserBadge);

        $request = new Request($requestData);
        $response = $this->badgeController->apiAwardBadge($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals($mockUserBadge, $data['data']);
    }

    public function test_api_award_badge_prevents_duplicates()
    {
        $requestData = [
            'user_id' => 123,
            'badge_id' => 1
        ];

        $this->mockSupabaseService
            ->shouldReceive('checkUserBadgeExists')
            ->once()
            ->with(123, 1)
            ->andReturn(true);

        $request = new Request($requestData);
        $response = $this->badgeController->apiAwardBadge($request);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('User already has this badge', $data['message']);
    }

    public function test_api_revoke_badge_works()
    {
        $requestData = [
            'user_id' => 123,
            'badge_id' => 1
        ];

        $this->mockSupabaseService
            ->shouldReceive('revokeUserBadge')
            ->once()
            ->with(123, 1)
            ->andReturn(true);

        $request = new Request($requestData);
        $response = $this->badgeController->apiRevokeBadge($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Badge revoked successfully', $data['message']);
    }
}