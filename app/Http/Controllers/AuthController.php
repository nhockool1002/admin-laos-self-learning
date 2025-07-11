<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\SupabaseService;

class AuthController extends Controller
{
    protected $supabaseService;

    public function __construct(SupabaseService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
    }

    /**
     * API login: trả về access token nếu thành công
     */
    public function login(Request $request)
    {
        // Debug: log request data
        \Log::info('Login attempt', [
            'email' => $request->email,
            'has_csrf' => $request->has('_token'),
            'session_id' => session()->getId(),
        ]);
        
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $loginValue = $request->email;
        if (filter_var($loginValue, FILTER_VALIDATE_EMAIL)) {
            $user = $this->supabaseService->getUserByEmail($loginValue);
        } else {
            $user = $this->supabaseService->getUserByUsername($loginValue);
        }
        
        if (!$user) {
            return response()->json(['message' => 'Tài khoản không tồn tại!'], 401);
        }
        
        if (empty($user['is_admin']) || $user['is_admin'] !== true) {
            return response()->json(['message' => 'Bạn không có quyền truy cập!'], 403);
        }
        
        // Kiểm tra mật khẩu: so sánh md5
        $passwordValid = (md5($request->password) === $user['password']);
        if (!$passwordValid) {
            return response()->json(['message' => 'Sai mật khẩu!'], 401);
        }
        
        $token = base64_encode(\Str::random(40));
        
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'user' => [
                'username' => $user['username'],
                'email' => $user['email'],
                'is_admin' => $user['is_admin'],
            ]
        ]);
    }

    /**
     * API kiểm tra authentication
     */
    public function checkAuth(Request $request)
    {
        $token = $request->header('Authorization');
        if (!$token) {
            return response()->json(['authenticated' => false], 401);
        }
        
        // Trong thực tế, bạn sẽ validate token với database
        // Ở đây tôi giả định token hợp lệ nếu có
        return response()->json(['authenticated' => true]);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Đăng xuất thành công']);
    }
}
