<!DOCTYPE html>
<html lang="vi">
@section('title', 'Trang chủ | Học Tiếng Lào Admin Panel')
@include('components.head')
<body class="bg-gradient-to-br from-[#232946] to-[#3b2f63] min-h-screen text-gray-100">
<div class="flex">
    <!-- Sidebar -->
    @include('components.sidebar')
    <div class="flex-1 flex flex-col min-h-screen">
        <x-header title="Trang chủ">
            <x-slot name="right">
                <button id="logout-btn" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-8 py-3 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition text-lg">Đăng xuất</button>
            </x-slot>
        </x-header>
        <!-- Main Content -->
        <main class="flex-1 flex flex-col items-center justify-center py-12 px-4 min-h-screen">
            <div class="w-full max-w-4xl mx-auto">
                <!-- Chào mừng & Thông tin admin -->
                <div class="bg-[#232946] rounded-2xl shadow-xl p-8 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <img src="/assets/imgs/laos.png" alt="Avatar" class="w-16 h-16 rounded-full shadow-lg border-4 border-purple-400/30 bg-white object-contain">
                        <div>
                            <h2 class="text-3xl font-extrabold text-purple-200 mb-1">Chào mừng, <span id="username" class="text-purple-300">Admin</span>!</h2>
                            <p class="text-lg text-purple-100">Chúc bạn một ngày làm việc hiệu quả 🎉</p>
                        </div>
                    </div>
                </div>
                <!-- Card thống kê nhanh -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-purple-500/80 to-indigo-500/80 rounded-xl p-5 flex flex-col items-center shadow-md">
                        <span class="text-3xl mb-2">👤</span>
                        <span class="text-2xl font-bold">3</span>
                        <span class="text-sm text-purple-100 mt-1">Users</span>
                    </div>
                    <div class="bg-gradient-to-br from-pink-400/80 to-purple-500/80 rounded-xl p-5 flex flex-col items-center shadow-md">
                        <span class="text-3xl mb-2">📚</span>
                        <span class="text-2xl font-bold">12</span>
                        <span class="text-sm text-purple-100 mt-1">Bài học</span>
                    </div>
                    <div class="bg-gradient-to-br from-blue-400/80 to-purple-500/80 rounded-xl p-5 flex flex-col items-center shadow-md">
                        <span class="text-3xl mb-2">🎯</span>
                        <span class="text-2xl font-bold">8</span>
                        <span class="text-sm text-purple-100 mt-1">Luyện tập</span>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-400/80 to-purple-500/80 rounded-xl p-5 flex flex-col items-center shadow-md">
                        <span class="text-3xl mb-2">📝</span>
                        <span class="text-2xl font-bold">5</span>
                        <span class="text-sm text-purple-100 mt-1">Kiểm tra</span>
                    </div>
                </div>
                <!-- Shortcut chức năng -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-8">
                    <a href="/admin/users" class="flex items-center gap-3 bg-[#2d3250] hover:bg-purple-700/40 transition rounded-xl p-5 shadow group">
                        <span class="text-2xl">👤</span>
                        <span class="font-semibold text-purple-100 group-hover:text-white">Quản lý Users</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 bg-[#2d3250] hover:bg-purple-700/40 transition rounded-xl p-5 shadow group">
                        <span class="text-2xl">📚</span>
                        <span class="font-semibold text-purple-100 group-hover:text-white">Quản lý Bài học</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 bg-[#2d3250] hover:bg-purple-700/40 transition rounded-xl p-5 shadow group">
                        <span class="text-2xl">🔤</span>
                        <span class="font-semibold text-purple-100 group-hover:text-white">Quản lý Bảng chữ cái</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 bg-[#2d3250] hover:bg-purple-700/40 transition rounded-xl p-5 shadow group">
                        <span class="text-2xl">🎯</span>
                        <span class="font-semibold text-purple-100 group-hover:text-white">Luyện tập</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 bg-[#2d3250] hover:bg-purple-700/40 transition rounded-xl p-5 shadow group">
                        <span class="text-2xl">📝</span>
                        <span class="font-semibold text-purple-100 group-hover:text-white">Kiểm tra</span>
                    </a>
                </div>
                <!-- Thông báo/hướng dẫn -->
                <div class="bg-[#232946] rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-purple-200 mb-2">Thông báo & Hướng dẫn</h3>
                    <ul class="list-disc pl-6 text-purple-100 space-y-1">
                        <li>Chào mừng bạn đến với hệ thống quản trị Tiếng Lào!</li>
                        <li>Bạn có thể quản lý Users, Bài học, Bảng chữ cái, Luyện tập và Kiểm tra từ menu bên trái hoặc các shortcut phía trên.</li>
                        <li>Nhấn vào các card thống kê để xem chi tiết từng mục.</li>
                        <li>Liên hệ admin nếu cần hỗ trợ thêm.</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
// Kiểm tra authentication khi trang load
document.addEventListener('DOMContentLoaded', function() {
    const token = sessionStorage.getItem('access_token');
    const user = sessionStorage.getItem('user');
    
    if (!token || !user) {
        // Nếu không có token hoặc user, chuyển hướng về trang login
        window.location.href = '/login';
        return;
    }
    
    try {
        const userData = JSON.parse(user);
        if (!userData.is_admin) {
            // Nếu không phải admin, chuyển hướng về trang login
            window.location.href = '/login';
            return;
        }
        
        // Hiển thị username
        document.getElementById('username').textContent = userData.username || 'Admin';
        
        // Không cần load users ở trang admin chính nữa
    } catch (error) {
        // Nếu có lỗi parse JSON, chuyển hướng về trang login
        window.location.href = '/login';
    }
});

// Xóa các function loadUsers và displayUsers vì đã chuyển sang trang riêng biệt

// Chuyển đổi section khi click menu
const menuHome = document.getElementById('menu-home');
const menuUsers = document.getElementById('menu-users');

function setActiveMenu(menu) {
    [menuHome, menuUsers].forEach(m => m.classList.remove('bg-purple-800/40', 'shadow-sm'));
    menu.classList.add('bg-purple-800/40', 'shadow-sm');
}

// Khi click menu, chuyển route
menuHome.onclick = function(e) {
    e.preventDefault();
    window.history.pushState({}, '', '/');
    setActiveMenu(menuHome);
};

menuUsers.onclick = function(e) {
    // Không cần preventDefault vì đã có href="/admin/users"
    setActiveMenu(menuUsers);
};

// Khi load trang, set active menu dựa trên URL hiện tại
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    if (currentPath === '/admin/users') {
        setActiveMenu(menuUsers);
    } else {
        setActiveMenu(menuHome);
    }
});

// Format ngày tạo
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return '';
    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

// Xử lý logout
document.getElementById('logout-btn').addEventListener('click', async function() {
    try {
        const token = sessionStorage.getItem('access_token');
        
        const response = await fetch('/logout', {
            method: 'GET',
            headers: {
                'Authorization': token
            }
        });
        
        // Xóa sessionStorage
        sessionStorage.removeItem('access_token');
        sessionStorage.removeItem('user');
        
        // Chuyển hướng về trang login
        window.location.href = '/login';
    } catch (error) {
        // Ngay cả khi có lỗi, vẫn xóa sessionStorage và chuyển hướng
        sessionStorage.removeItem('access_token');
        sessionStorage.removeItem('user');
        window.location.href = '/login';
    }
});

// Đăng xuất từ menu
const logoutMenu = document.getElementById('logout-menu');
if (logoutMenu) {
    logoutMenu.onclick = function(e) {
        e.preventDefault();
        document.getElementById('logout-btn').click();
    };
}
</script>
<!-- Thêm Alpine.js cho hiệu ứng toggle -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html> 