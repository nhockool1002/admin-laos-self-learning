<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;

class CourseController extends Controller
{
    protected $supabaseService;

    public function __construct(SupabaseService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
    }

    // Middleware kiểm tra quyền admin
    protected function checkAdmin(Request $request)
    {
        $user = $request->header('User');
        if (!$user) return false;
        $user = json_decode($user, true);
        return $user && !empty($user['is_admin']);
    }

    // Lấy danh sách khoá học
    public function index(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $courses = $this->supabaseService->getCourses($request->all());
        return response()->json($courses);
    }

    // Lấy chi tiết khoá học
    public function show(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $course = $this->supabaseService->getCourseById($id);
        return response()->json($course);
    }

    // Tạo khoá học mới
    public function store(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $data = $request->all();
        // Validate bắt buộc
        if (empty($data['id']) || empty($data['title'])) {
            return response()->json(['error' => 'Thiếu id hoặc title'], 422);
        }
        $course = $this->supabaseService->createCourse($data);
        if (!$course) {
            return response()->json(['error' => 'Không thể tạo khoá học, kiểm tra lại dữ liệu hoặc id đã tồn tại!'], 500);
        }
        return response()->json($course);
    }

    // Cập nhật khoá học
    public function update(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $data = $request->all();
        $course = $this->supabaseService->updateCourse($id, $data);
        return response()->json($course);
    }

    // Xoá khoá học
    public function destroy(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $ok = $this->supabaseService->deleteCourse($id);
        return response()->json(['success' => $ok]);
    }

    // Lấy danh sách bài học theo khoá
    public function listLessons(Request $request, $courseId)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $lessons = $this->supabaseService->getLessons($courseId);
        return response()->json($lessons);
    }

    // Tạo bài học mới cho khoá
    public function createLesson(Request $request, $courseId)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $data = $request->all();
        $data['course_id'] = $courseId;
        $lesson = $this->supabaseService->createLesson($data);
        return response()->json($lesson);
    }

    // Lấy chi tiết bài học
    public function showLesson(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $lesson = $this->supabaseService->getLessonById($id);
        return response()->json($lesson);
    }

    // Cập nhật bài học
    public function updateLesson(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $data = $request->all();
        $lesson = $this->supabaseService->updateLesson($id, $data);
        return response()->json($lesson);
    }

    // Xoá bài học
    public function deleteLesson(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $ok = $this->supabaseService->deleteLesson($id);
        return response()->json(['success' => $ok]);
    }
} 