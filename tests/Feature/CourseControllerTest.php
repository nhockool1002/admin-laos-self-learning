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
                'description' => 'Khóa học dành cho người đã có kiến thức cơ bản',
                'is_active' => true
            ]
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getCourses')
            ->once()
            ->andReturn($courses);

        $response = $this->getJson('/supabase/courses');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['title' => 'Khóa học tiếng Lào cơ bản'])
            ->assertJsonFragment(['title' => 'Khóa học tiếng Lào nâng cao']);
    }

    public function test_create_course_with_valid_data()
    {
        $courseData = [
            'title' => 'Khóa học mới',
            'description' => 'Mô tả khóa học mới',
            'is_active' => true
        ];

        $createdCourse = array_merge($courseData, ['id' => 1]);

        $this->supabaseServiceMock
            ->shouldReceive('createCourse')
            ->with($courseData)
            ->once()
            ->andReturn($createdCourse);

        $response = $this->postJson('/supabase/courses', $courseData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Khóa học mới'])
            ->assertJsonFragment(['id' => 1]);
    }

    public function test_create_course_service_failure()
    {
        $courseData = [
            'title' => 'Khóa học mới',
            'description' => 'Mô tả khóa học mới'
        ];

        $this->supabaseServiceMock
            ->shouldReceive('createCourse')
            ->with($courseData)
            ->once()
            ->andReturn(false);

        $response = $this->postJson('/supabase/courses', $courseData);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Tạo khóa học thất bại']);
    }

    public function test_get_course_by_id()
    {
        $course = [
            'id' => 1,
            'title' => 'Khóa học tiếng Lào',
            'description' => 'Mô tả khóa học',
            'is_active' => true
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getCourse')
            ->with(1)
            ->once()
            ->andReturn($course);

        $response = $this->getJson('/supabase/courses/1');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => 1])
            ->assertJsonFragment(['title' => 'Khóa học tiếng Lào']);
    }

    public function test_get_nonexistent_course()
    {
        $this->supabaseServiceMock
            ->shouldReceive('getCourse')
            ->with(999)
            ->once()
            ->andReturn(null);

        $response = $this->getJson('/supabase/courses/999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Không tìm thấy khóa học']);
    }

    public function test_update_course_with_valid_data()
    {
        $updateData = [
            'title' => 'Khóa học đã cập nhật',
            'description' => 'Mô tả mới'
        ];

        $updatedCourse = array_merge($updateData, ['id' => 1]);

        $this->supabaseServiceMock
            ->shouldReceive('updateCourse')
            ->with(1, $updateData)
            ->once()
            ->andReturn($updatedCourse);

        $response = $this->putJson('/supabase/courses/1', $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Khóa học đã cập nhật']);
    }

    public function test_update_course_service_failure()
    {
        $updateData = ['title' => 'Khóa học đã cập nhật'];

        $this->supabaseServiceMock
            ->shouldReceive('updateCourse')
            ->with(1, $updateData)
            ->once()
            ->andReturn(false);

        $response = $this->putJson('/supabase/courses/1', $updateData);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Cập nhật khóa học thất bại']);
    }

    public function test_delete_course_success()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteCourse')
            ->with(1)
            ->once()
            ->andReturn(true);

        $response = $this->deleteJson('/supabase/courses/1');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Xóa khóa học thành công']);
    }

    public function test_delete_course_failure()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteCourse')
            ->with(1)
            ->once()
            ->andReturn(false);

        $response = $this->deleteJson('/supabase/courses/1');

        $response->assertStatus(500)
            ->assertJson(['error' => 'Xóa khóa học thất bại']);
    }

    public function test_get_course_lessons()
    {
        $lessons = [
            [
                'id' => 1,
                'title' => 'Bài học 1',
                'content' => 'Nội dung bài học 1',
                'course_id' => 1
            ],
            [
                'id' => 2,
                'title' => 'Bài học 2',
                'content' => 'Nội dung bài học 2',
                'course_id' => 1
            ]
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getCourseLessons')
            ->with(1)
            ->once()
            ->andReturn($lessons);

        $response = $this->getJson('/supabase/courses/1/lessons');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['title' => 'Bài học 1'])
            ->assertJsonFragment(['title' => 'Bài học 2']);
    }

    public function test_create_course_lesson()
    {
        $lessonData = [
            'title' => 'Bài học mới',
            'content' => 'Nội dung bài học mới',
            'order' => 1
        ];

        $createdLesson = array_merge($lessonData, [
            'id' => 1,
            'course_id' => 1
        ]);

        $this->supabaseServiceMock
            ->shouldReceive('createLesson')
            ->with(1, $lessonData)
            ->once()
            ->andReturn($createdLesson);

        $response = $this->postJson('/supabase/courses/1/lessons', $lessonData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Bài học mới'])
            ->assertJsonFragment(['course_id' => 1]);
    }

    public function test_create_lesson_service_failure()
    {
        $lessonData = [
            'title' => 'Bài học mới',
            'content' => 'Nội dung bài học mới'
        ];

        $this->supabaseServiceMock
            ->shouldReceive('createLesson')
            ->with(1, $lessonData)
            ->once()
            ->andReturn(false);

        $response = $this->postJson('/supabase/courses/1/lessons', $lessonData);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Tạo bài học thất bại']);
    }

    public function test_get_lesson_by_id()
    {
        $lesson = [
            'id' => 1,
            'title' => 'Bài học 1',
            'content' => 'Nội dung chi tiết',
            'course_id' => 1
        ];

        $this->supabaseServiceMock
            ->shouldReceive('getLesson')
            ->with(1)
            ->once()
            ->andReturn($lesson);

        $response = $this->getJson('/supabase/lessons/1');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => 1])
            ->assertJsonFragment(['title' => 'Bài học 1']);
    }

    public function test_get_nonexistent_lesson()
    {
        $this->supabaseServiceMock
            ->shouldReceive('getLesson')
            ->with(999)
            ->once()
            ->andReturn(null);

        $response = $this->getJson('/supabase/lessons/999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Không tìm thấy bài học']);
    }

    public function test_update_lesson()
    {
        $updateData = [
            'title' => 'Bài học đã cập nhật',
            'content' => 'Nội dung mới'
        ];

        $updatedLesson = array_merge($updateData, ['id' => 1]);

        $this->supabaseServiceMock
            ->shouldReceive('updateLesson')
            ->with(1, $updateData)
            ->once()
            ->andReturn($updatedLesson);

        $response = $this->putJson('/supabase/lessons/1', $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Bài học đã cập nhật']);
    }

    public function test_update_lesson_service_failure()
    {
        $updateData = ['title' => 'Bài học đã cập nhật'];

        $this->supabaseServiceMock
            ->shouldReceive('updateLesson')
            ->with(1, $updateData)
            ->once()
            ->andReturn(false);

        $response = $this->putJson('/supabase/lessons/1', $updateData);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Cập nhật bài học thất bại']);
    }

    public function test_delete_lesson_success()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteLesson')
            ->with(1)
            ->once()
            ->andReturn(true);

        $response = $this->deleteJson('/supabase/lessons/1');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Xóa bài học thành công']);
    }

    public function test_delete_lesson_failure()
    {
        $this->supabaseServiceMock
            ->shouldReceive('deleteLesson')
            ->with(1)
            ->once()
            ->andReturn(false);

        $response = $this->deleteJson('/supabase/lessons/1');

        $response->assertStatus(500)
            ->assertJson(['error' => 'Xóa bài học thất bại']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}