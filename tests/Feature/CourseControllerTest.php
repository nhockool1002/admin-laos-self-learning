<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\SupabaseService;
use Mockery;

class CourseControllerTest extends TestCase
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
     * Create admin user header for authentication
     */
    protected function getAdminHeaders(): array
    {
        $adminUser = [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'is_admin' => true
        ];

        return [
            'User' => json_encode($adminUser),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Create non-admin user header
     */
    protected function getNonAdminHeaders(): array
    {
        $regularUser = [
            'id' => 2,
            'username' => 'user',
            'email' => 'user@example.com',
            'is_admin' => false
        ];

        return [
            'User' => json_encode($regularUser),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }

    public function test_get_courses_returns_list()
    {
        $courses = [
            [
                'id' => 1,
                'title' => 'Khóa học tiếng Lào cơ bản',
                'description' => 'Khóa học dành cho người mới bắt đầu',
                'is_active' => true
            ],
            [
                'id' => 2,
                'title' => 'Khóa học tiếng Lào nâng cao',
                'description' => 'Khóa học dành cho người có kinh nghiệm',
                'is_active' => true
            ]
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getCourses')
            ->once()
            ->andReturn($courses);

        $response = $this->getJson('/supabase/courses', $this->getAdminHeaders());

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['title' => 'Khóa học tiếng Lào cơ bản'])
            ->assertJsonFragment(['title' => 'Khóa học tiếng Lào nâng cao']);
    }

    public function test_get_courses_unauthorized_without_admin()
    {
        $response = $this->getJson('/supabase/courses', $this->getNonAdminHeaders());

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_get_courses_unauthorized_without_user_header()
    {
        $response = $this->getJson('/supabase/courses');

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_get_course_by_id_returns_course()
    {
        $course = [
            'id' => 1,
            'title' => 'Khóa học tiếng Lào cơ bản',
            'description' => 'Khóa học dành cho người mới bắt đầu',
            'is_active' => true
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getCourseById')
            ->with(1)
            ->once()
            ->andReturn($course);

        $response = $this->getJson('/supabase/courses/1', $this->getAdminHeaders());

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Khóa học tiếng Lào cơ bản']);
    }

    public function test_create_course_with_valid_data()
    {
        $courseData = [
            'id' => 3,
            'title' => 'Khóa học mới',
            'description' => 'Mô tả khóa học mới'
        ];

        $this->supabaseServiceMock
            ->shouldReceive('createCourse')
            ->with($courseData)
            ->once()
            ->andReturn($courseData);

        $response = $this->postJson('/supabase/courses', $courseData, $this->getAdminHeaders());

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Khóa học mới']);
    }

    public function test_create_course_missing_required_fields()
    {
        $courseData = [
            'description' => 'Mô tả khóa học mới'
            // Missing id and title
        ];

        $response = $this->postJson('/supabase/courses', $courseData, $this->getAdminHeaders());

        $response->assertStatus(422)
            ->assertJson(['error' => 'Thiếu id hoặc title']);
    }

    public function test_update_course()
    {
        $courseData = [
            'title' => 'Khóa học đã cập nhật',
            'description' => 'Mô tả đã cập nhật'
        ];

        $updatedCourse = array_merge(['id' => 1], $courseData);

        $this->supabaseServiceMock
            ->shouldReceive('updateCourse')
            ->with(1, $courseData)
            ->once()
            ->andReturn($updatedCourse);

        $response = $this->putJson('/supabase/courses/1', $courseData, $this->getAdminHeaders());

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Khóa học đã cập nhật']);
    }

    public function test_delete_course()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteCourse')
            ->with(1)
            ->once()
            ->andReturn(true);

        $response = $this->deleteJson('/supabase/courses/1', [], $this->getAdminHeaders());

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_lessons_for_course()
    {
        $lessons = [
            [
                'id' => 1,
                'title' => 'Bài học 1',
                'course_id' => 1
            ],
            [
                'id' => 2,
                'title' => 'Bài học 2',
                'course_id' => 1
            ]
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getLessons')
            ->with(1)
            ->once()
            ->andReturn($lessons);

        $response = $this->getJson('/supabase/courses/1/lessons', $this->getAdminHeaders());

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['title' => 'Bài học 1']);
    }

    public function test_create_lesson_for_course()
    {
        $lessonData = [
            'title' => 'Bài học mới',
            'content' => 'Nội dung bài học'
        ];

        $expectedData = array_merge($lessonData, ['course_id' => 1]);
        $createdLesson = array_merge($expectedData, ['id' => 3]);

        $this->supabaseServiceMock
            ->shouldReceive('createLesson')
            ->with($expectedData)
            ->once()
            ->andReturn($createdLesson);

        $response = $this->postJson('/supabase/courses/1/lessons', $lessonData, $this->getAdminHeaders());

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Bài học mới'])
            ->assertJsonFragment(['course_id' => 1]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}