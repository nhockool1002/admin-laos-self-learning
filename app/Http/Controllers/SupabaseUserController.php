<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;

class SupabaseUserController extends Controller
{
    protected $supabaseService;

    public function __construct(SupabaseService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
    }

    /**
     * Lấy danh sách users từ Supabase
     */
    public function index(Request $request)
    {
        // Kiểm tra authentication
        $token = $request->header('Authorization');
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Trong thực tế, bạn sẽ validate token với database
        // Ở đây tôi giả định token hợp lệ nếu có
        
        $params = $request->all();
        $users = $this->supabaseService->getUsers($params);
        // Loại bỏ password cho từng user
        foreach ($users as &$user) {
            unset($user['password']);
        }
        return response()->json($users);
    }

    /**
     * Tạo user mới
     */
    public function store(Request $request)
    {
        $data = $request->only(['username', 'email', 'password', 'is_admin']);
        // Validate đơn giản
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return response()->json(['error' => 'Thiếu thông tin bắt buộc'], 422);
        }
        // Mã hoá password md5 (giống login)
        $data['password'] = md5($data['password']);
        $data['createdat'] = now();
        $user = $this->supabaseService->createUser($data);
        if ($user) {
            unset($user['password']);
            return response()->json($user);
        }
        return response()->json(['error' => 'Tạo user thất bại'], 500);
    }

    /**
     * Cập nhật user (trừ username)
     */
    public function update(Request $request, $username)
    {
        if ($username === 'nhockool1002') {
            return response()->json(['error' => 'Không được cập nhật user root!'], 403);
        }
        $data = $request->only(['email', 'password', 'is_admin']);
        if (isset($data['password'])) {
            $data['password'] = md5($data['password']);
        }
        $user = $this->supabaseService->updateUser($username, $data);
        if ($user) {
            unset($user['password']);
            return response()->json($user);
        }
        return response()->json(['error' => 'Cập nhật user thất bại'], 500);
    }

    /**
     * Xoá user
     */
    public function destroy($username)
    {
        if ($username === 'nhockool1002') {
            return response()->json(['error' => 'Không được xoá user root!'], 403);
        }
        $ok = $this->supabaseService->deleteUser($username);
        if ($ok) {
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Xoá user thất bại'], 500);
    }

    /**
     * Cập nhật role (is_admin)
     */
    public function updateRole(Request $request, $username)
    {
        if ($username === 'nhockool1002') {
            return response()->json(['error' => 'Không được cập nhật role user root!'], 403);
        }
        $isAdmin = $request->input('is_admin');
        $user = $this->supabaseService->updateUserRole($username, $isAdmin);
        if ($user) {
            return response()->json($user);
        }
        return response()->json(['error' => 'Cập nhật role thất bại'], 500);
    }
}
