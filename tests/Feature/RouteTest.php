<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteTest extends TestCase
{
    public function test_home_page_redirects_to_admin_panel()
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertViewIs('admin');
    }

    public function test_login_page_renders()
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertViewIs('login');
    }

    public function test_admin_users_page_renders()
    {
        $response = $this->get('/admin/users');

        $response->assertStatus(200)
            ->assertViewIs('users');
    }

    public function test_admin_courses_page_renders()
    {
        $response = $this->get('/admin/courses');

        $response->assertStatus(200)
            ->assertViewIs('courses');
    }

    public function test_admin_lessons_page_renders()
    {
        $response = $this->get('/admin/lessons');

        $response->assertStatus(200)
            ->assertViewIs('lessons');
    }

    public function test_admin_games_page_renders()
    {
        $response = $this->get('/admin/games');

        $response->assertStatus(200)
            ->assertViewIs('games');
    }

    public function test_admin_game_groups_page_renders()
    {
        $response = $this->get('/admin/game-groups');

        $response->assertStatus(200)
            ->assertViewIs('game-groups');
    }

    public function test_csrf_test_page_renders()
    {
        $response = $this->get('/test-csrf');

        $response->assertStatus(200)
            ->assertViewIs('test-csrf');
    }

    public function test_csrf_endpoint_returns_success()
    {
        $response = $this->postJson('/test-csrf');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'CSRF working'
            ]);
    }

    public function test_session_test_endpoint()
    {
        $response = $this->getJson('/test-session');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'test'
            ])
            ->assertJson([
                'test' => 'working'
            ]);
    }

    public function test_api_routes_exist()
    {
        $apiRoutes = [
            'GET' => [
                '/supabase/users',
                '/supabase/courses',
                '/supabase/games',
                '/supabase/game-groups',
                '/check-auth',
                '/logout'
            ],
            'POST' => [
                '/login',
                '/supabase/users',
                '/supabase/courses',
                '/supabase/games',
                '/supabase/game-groups'
            ]
        ];

        foreach ($apiRoutes as $method => $routes) {
            foreach ($routes as $route) {
                $response = $this->json($method, $route);
                
                // We expect these routes to exist (not 404)
                // They may return 401, 422, 500 etc. depending on authentication/validation
                $this->assertNotEquals(404, $response->getStatusCode(), 
                    "Route {$method} {$route} should exist");
            }
        }
    }

    public function test_invalid_routes_return_404()
    {
        $invalidRoutes = [
            '/nonexistent',
            '/admin/nonexistent',
            '/supabase/nonexistent',
            '/api/nonexistent'
        ];

        foreach ($invalidRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(404);
        }
    }

    public function test_route_parameter_validation()
    {
        // Test routes with parameters - should exist but may fail validation
        $parameterRoutes = [
            ['GET', '/supabase/courses/1'],
            ['PUT', '/supabase/courses/1'],
            ['DELETE', '/supabase/courses/1'],
            ['GET', '/supabase/users/testuser'],
            ['PUT', '/supabase/users/testuser'],
            ['DELETE', '/supabase/users/testuser'],
            ['PATCH', '/supabase/users/testuser/role'],
        ];

        foreach ($parameterRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            
            // Routes should exist (not 404)
            $this->assertNotEquals(404, $response->getStatusCode(), 
                "Route {$method} {$route} should exist");
        }
    }

    public function test_nested_resource_routes()
    {
        // Test course lessons nested routes
        $nestedRoutes = [
            ['GET', '/supabase/courses/1/lessons'],
            ['POST', '/supabase/courses/1/lessons'],
            ['GET', '/supabase/lessons/1'],
            ['PUT', '/supabase/lessons/1'],
            ['DELETE', '/supabase/lessons/1']
        ];

        foreach ($nestedRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            
            // Routes should exist (not 404)
            $this->assertNotEquals(404, $response->getStatusCode(), 
                "Nested route {$method} {$route} should exist");
        }
    }

    public function test_route_methods_are_restricted()
    {
        // Test that wrong methods return 405 (Method Not Allowed)
        $restrictedMethods = [
            ['DELETE', '/login'],
            ['PUT', '/login'],
            ['PATCH', '/admin/users'],
            ['POST', '/admin/courses'],
            ['DELETE', '/']
        ];

        foreach ($restrictedMethods as [$method, $route]) {
            $response = $this->json($method, $route);
            
            // Should return 405 Method Not Allowed or redirect (302)
            $this->assertContains($response->getStatusCode(), [302, 405], 
                "Route {$method} {$route} should not allow this method");
        }
    }
}