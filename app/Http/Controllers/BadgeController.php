<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Storage;

class BadgeController extends Controller
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

    // Lấy danh sách huy hiệu
    public function index(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $badges = $this->supabaseService->getBadges($request->all());
        return response()->json($badges);
    }

    // Lấy chi tiết huy hiệu
    public function show(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $badge = $this->supabaseService->getBadgeById($id);
        return response()->json($badge);
    }

    // Tạo huy hiệu mới
    public function store(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        // Xử lý upload hình ảnh
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('assets/images'), $imageName);
            $imagePath = '/assets/images/' . $imageName;
        } else {
            return response()->json(['error' => 'Thiếu hình ảnh huy hiệu'], 422);
        }

        $data = [
            'id' => 'badge_' . time(), // Generate unique ID
            'name' => $request->name,
            'description' => $request->description,
            'image_path' => $imagePath,
            'condition' => $request->condition ?? 'nan'
        ];

        $badge = $this->supabaseService->createBadge($data);
        if (!$badge) {
            // Xóa file nếu tạo badge thất bại
            if (file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }
            return response()->json(['error' => 'Không thể tạo huy hiệu, kiểm tra lại dữ liệu!'], 500);
        }
        return response()->json($badge);
    }

    // Cập nhật huy hiệu
    public function update(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Handle both PUT with JSON and POST with form data (for file uploads)
        $isFileUpload = $request->hasFile('image');
        
        if ($isFileUpload) {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);
        } else {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string'
            ]);
        }

        $data = [];
        if ($request->has('name')) {
            $data['name'] = $request->name;
        }
        if ($request->has('description')) {
            $data['description'] = $request->description;
        }
        
        if ($request->has('condition')) {
            $data['condition'] = $request->condition;
        }

        // Xử lý upload hình ảnh mới nếu có
        if ($request->hasFile('image')) {
            // Lấy thông tin badge cũ để xóa hình ảnh cũ
            $oldBadge = $this->supabaseService->getBadgeById($id);
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('assets/images'), $imageName);
            $imagePath = '/assets/images/' . $imageName;
            $data['image_path'] = $imagePath;

            // Xóa hình ảnh cũ nếu tồn tại
            if ($oldBadge && !empty($oldBadge['image_path'])) {
                $oldImagePath = public_path($oldBadge['image_path']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }

        $badge = $this->supabaseService->updateBadge($id, $data);
        return response()->json($badge);
    }

    // Xóa huy hiệu
    public function destroy(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Lấy thông tin badge để xóa hình ảnh
        $badge = $this->supabaseService->getBadgeById($id);
        
        $ok = $this->supabaseService->deleteBadge($id);
        
        if ($ok && $badge && !empty($badge['image_path'])) {
            // Xóa hình ảnh khỏi server
            $imagePath = public_path($badge['image_path']);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        return response()->json(['success' => $ok]);
    }

    // Lấy danh sách user badge
    public function getUserBadges(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userBadges = $this->supabaseService->getUserBadges($request->all());
        return response()->json($userBadges);
    }

    // Lấy danh sách badge của một user
    public function getUserBadgesByUserId(Request $request, $userId)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $userBadges = $this->supabaseService->getUserBadgesByUserId($userId);
        return response()->json($userBadges);
    }

    // Tặng huy hiệu cho user
    public function awardBadge(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'username' => 'required|string',
            'badge_id' => 'required|string'
        ]);

        $username = $request->username;
        $badgeId = $request->badge_id;

        // Kiểm tra xem user đã có badge này chưa
        $exists = $this->supabaseService->checkUserBadgeExists($username, $badgeId);
        if ($exists) {
            return response()->json(['error' => 'User đã có huy hiệu này rồi!'], 422);
        }

        $data = [
            'username' => $username,
            'badge_id' => $badgeId,
            'achieved_date' => now()->toISOString()
        ];

        $userBadge = $this->supabaseService->awardUserBadge($data);
        if (!$userBadge) {
            return response()->json(['error' => 'Không thể tặng huy hiệu, kiểm tra lại dữ liệu!'], 500);
        }
        return response()->json($userBadge);
    }

    // Thu hồi huy hiệu từ user
    public function revokeBadge(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'username' => 'required|string',
            'badge_id' => 'required|string'
        ]);

        $username = $request->username;
        $badgeId = $request->badge_id;

        $ok = $this->supabaseService->revokeUserBadge($username, $badgeId);
        return response()->json(['success' => $ok]);
    }

    // Lấy danh sách users với thông tin badge
    public function getUsersWithBadges(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $users = $this->supabaseService->getUsersWithBadgeDetails($request->all());
        return response()->json($users);
    }

    // API Endpoints for third-party integration
    
    // API: Lấy danh sách badges (public API)
    public function apiBadges(Request $request)
    {
        $badges = $this->supabaseService->getBadges($request->all());
        return response()->json([
            'success' => true,
            'data' => $badges
        ]);
    }

    // API: Lấy chi tiết badge (public API)
    public function apiBadgeDetail(Request $request, $id)
    {
        $badge = $this->supabaseService->getBadgeById($id);
        if (!$badge) {
            return response()->json([
                'success' => false,
                'message' => 'Badge not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $badge
        ]);
    }

    // API: Lấy badges của user (public API)
    public function apiUserBadges(Request $request, $username)
    {
        $userBadges = $this->supabaseService->getUserBadgesByUsername($username);
        return response()->json([
            'success' => true,
            'data' => $userBadges
        ]);
    }

    // API: Tặng badge cho user (cần auth)
    public function apiAwardBadge(Request $request)
    {
        // Có thể thêm API key authentication ở đây
        $request->validate([
            'username' => 'required|string',
            'badge_id' => 'required|string'
        ]);

        $username = $request->username;
        $badgeId = $request->badge_id;

        // Kiểm tra xem user đã có badge này chưa
        $exists = $this->supabaseService->checkUserBadgeExists($username, $badgeId);
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'User already has this badge'
            ], 422);
        }

        $data = [
            'username' => $username,
            'badge_id' => $badgeId,
            'achieved_date' => now()->toISOString()
        ];

        $userBadge = $this->supabaseService->awardUserBadge($data);
        if (!$userBadge) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to award badge'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'data' => $userBadge
        ]);
    }

    // API: Thu hồi badge từ user (cần auth)
    public function apiRevokeBadge(Request $request)
    {
        // Có thể thêm API key authentication ở đây
        $request->validate([
            'username' => 'required|string',
            'badge_id' => 'required|string'
        ]);

        $username = $request->username;
        $badgeId = $request->badge_id;

        $ok = $this->supabaseService->revokeUserBadge($username, $badgeId);
        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Badge revoked successfully' : 'Failed to revoke badge'
        ]);
    }
}