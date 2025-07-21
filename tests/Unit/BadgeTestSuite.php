<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\TestSuite;

/**
 * Badge Management Test Suite
 * 
 * This class provides a comprehensive test suite for all badge management functionality.
 * It can be used to run all badge-related tests at once.
 */
class BadgeTestSuite extends TestCase
{
    /**
     * Create a test suite with all badge-related tests
     */
    public static function suite(): TestSuite
    {
        $suite = new TestSuite('Badge Management Test Suite');
        
        // Add unit tests
        $suite->addTestSuite(BadgeControllerTest::class);
        $suite->addTestSuite(SupabaseBadgeServiceTest::class);
        
        // Add feature tests
        $suite->addTestSuite(\Tests\Feature\BadgeManagementTest::class);
        
        return $suite;
    }

    /**
     * Test that all badge components are properly integrated
     */
    public function test_badge_system_integration()
    {
        // Mock successful responses
        $this->mockSupabaseSuccess();
        
        // Test that we can access the badges page
        $response = $this->get('/admin/badges');
        $response->assertStatus(200);
        $response->assertViewIs('badges');
        
        // Test that admin can access badge API
        $response = $this->withHeaders($this->adminHeaders())
                         ->get('/supabase/badges');
        $response->assertStatus(200);
        
        // Test that public API works
        $response = $this->get('/api/v1/badges');
        $response->assertStatus(200);
        $this->assertApiSuccess($response);
    }

    /**
     * Test badge system with different user roles
     */
    public function test_badge_system_permissions()
    {
        $this->mockSupabaseSuccess();
        
        // Admin should have access
        $adminResponse = $this->withHeaders($this->adminHeaders())
                              ->get('/supabase/badges');
        $adminResponse->assertStatus(200);
        
        // Regular user should not have admin access
        $userResponse = $this->withHeaders($this->userHeaders())
                             ->get('/supabase/badges');
        $this->assertUnauthorized($userResponse);
        
        // But public API should work for everyone
        $publicResponse = $this->get('/api/v1/badges');
        $publicResponse->assertStatus(200);
    }

    /**
     * Test complete badge workflow
     */
    public function test_complete_badge_workflow()
    {
        // This test ensures the entire badge management workflow works end-to-end
        
        // 1. Mock creating a badge
        $badge = $this->createMockBadge(1, ['name' => 'Workflow Badge']);
        
        \Illuminate\Support\Facades\Http::fake([
            'https://test.supabase.co/rest/v1/badges' => \Illuminate\Support\Facades\Http::response([$badge], 201)
        ]);

        $createResponse = $this->withHeaders($this->adminHeaders())
                               ->postJson('/supabase/badges', [
                                   'name' => 'Workflow Badge',
                                   'description' => 'Test workflow',
                                   'image' => $this->createTestImageFile()
                               ]);
        $createResponse->assertStatus(200);

        // 2. Mock awarding the badge
        $userBadge = $this->createMockUserBadge(123, 1);
        
        \Illuminate\Support\Facades\Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => \Illuminate\Support\Facades\Http::sequence()
                ->push([], 200) // Check doesn't exist
                ->push([$userBadge], 201) // Award badge
        ]);

        $awardResponse = $this->withHeaders($this->adminHeaders())
                              ->postJson('/supabase/user-badges/award', [
                                  'user_id' => 123,
                                  'badge_id' => 1
                              ]);
        $awardResponse->assertStatus(200);

        // 3. Mock revoking the badge
        \Illuminate\Support\Facades\Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => \Illuminate\Support\Facades\Http::response([], 204)
        ]);

        $revokeResponse = $this->withHeaders($this->adminHeaders())
                               ->postJson('/supabase/user-badges/revoke', [
                                   'user_id' => 123,
                                   'badge_id' => 1
                               ]);
        $revokeResponse->assertStatus(200);

        // 4. Mock deleting the badge
        \Illuminate\Support\Facades\Http::fake([
            'https://test.supabase.co/rest/v1/badges*' => \Illuminate\Support\Facades\Http::sequence()
                ->push($badge, 200) // Get badge for cleanup
                ->push([], 204) // Delete badge
        ]);

        $deleteResponse = $this->withHeaders($this->adminHeaders())
                               ->delete('/supabase/badges/1');
        $deleteResponse->assertStatus(200);
    }

    /**
     * Test error handling across the badge system
     */
    public function test_badge_system_error_handling()
    {
        // Mock error responses
        $this->mockSupabaseError();
        
        // Admin endpoints should handle errors gracefully
        $response = $this->withHeaders($this->adminHeaders())
                         ->get('/supabase/badges');
        $response->assertStatus(200);
        $response->assertJson(null);
        
        // Public API should also handle errors
        $response = $this->get('/api/v1/badges');
        $response->assertStatus(200);
        $this->assertApiSuccess($response, null);
    }

    /**
     * Test validation across the badge system
     */
    public function test_badge_system_validation()
    {
        // Test missing required fields
        $response = $this->withHeaders($this->adminHeaders())
                         ->postJson('/supabase/badges', []);
        $response->assertStatus(422);
        
        // Test invalid image file
        $response = $this->withHeaders($this->adminHeaders())
                         ->post('/supabase/badges', [
                             'name' => 'Test Badge',
                             'description' => 'Test Description',
                             'image' => $this->createTestFile('document.txt')
                         ]);
        $response->assertStatus(422);
        
        // Test missing user/badge IDs for awarding
        $response = $this->withHeaders($this->adminHeaders())
                         ->postJson('/supabase/user-badges/award', []);
        $response->assertStatus(422);
    }

    /**
     * Test performance aspects of the badge system
     */
    public function test_badge_system_performance()
    {
        // This test ensures the system can handle multiple operations efficiently
        
        $this->mockSupabaseSuccess();
        
        $startTime = microtime(true);
        
        // Simulate multiple rapid requests
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->withHeaders($this->adminHeaders())
                                ->get('/supabase/badges');
        }
        
        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        
        // Ensure all requests succeeded
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }
        
        // Ensure reasonable performance (less than 5 seconds for 5 requests)
        $this->assertLessThan(5.0, $totalTime, 'Badge system should handle multiple requests efficiently');
    }

    /**
     * Test edge cases in the badge system
     */
    public function test_badge_system_edge_cases()
    {
        // Test with empty responses
        $this->mockSupabaseEmpty();
        
        // Should handle empty badge list
        $response = $this->withHeaders($this->adminHeaders())
                         ->get('/supabase/badges');
        $response->assertStatus(200);
        $response->assertJson([]);
        
        // Should handle non-existent badge
        $response = $this->get('/api/v1/badges/999');
        $response->assertStatus(404);
        $this->assertApiError($response, 'Badge not found', 404);
        
        // Test awarding badge that user already has
        \Illuminate\Support\Facades\Http::fake([
            'https://test.supabase.co/rest/v1/user_badges*' => \Illuminate\Support\Facades\Http::response([['id' => 1]], 200)
        ]);
        
        $response = $this->withHeaders($this->adminHeaders())
                         ->postJson('/supabase/user-badges/award', [
                             'user_id' => 123,
                             'badge_id' => 1
                         ]);
        $response->assertStatus(422);
    }

    /**
     * Test security aspects of the badge system
     */
    public function test_badge_system_security()
    {
        // Test that non-admin users cannot access admin endpoints
        $adminEndpoints = [
            '/supabase/badges',
            '/supabase/user-badges',
            '/supabase/users-with-badges'
        ];
        
        foreach ($adminEndpoints as $endpoint) {
            // Test without authentication
            $response = $this->get($endpoint);
            $this->assertUnauthorized($response);
            
            // Test with regular user
            $response = $this->withHeaders($this->userHeaders())
                             ->get($endpoint);
            $this->assertUnauthorized($response);
        }
        
        // Test that admin endpoints require proper headers
        $response = $this->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
                         ->get('/supabase/badges');
        $this->assertUnauthorized($response);
    }

    /**
     * Run all badge management tests and return a summary
     */
    public function runCompleteTestSuite(): array
    {
        $results = [
            'total_tests' => 0,
            'passed_tests' => 0,
            'failed_tests' => 0,
            'test_details' => []
        ];
        
        $testMethods = [
            'test_badge_system_integration',
            'test_badge_system_permissions',
            'test_complete_badge_workflow',
            'test_badge_system_error_handling',
            'test_badge_system_validation',
            'test_badge_system_performance',
            'test_badge_system_edge_cases',
            'test_badge_system_security'
        ];
        
        foreach ($testMethods as $method) {
            $results['total_tests']++;
            
            try {
                $this->$method();
                $results['passed_tests']++;
                $results['test_details'][$method] = 'PASSED';
            } catch (\Exception $e) {
                $results['failed_tests']++;
                $results['test_details'][$method] = 'FAILED: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
}