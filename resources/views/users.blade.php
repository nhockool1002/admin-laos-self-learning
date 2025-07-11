<!DOCTYPE html>
<html lang="vi">
@section('title', 'Quản lý Users | Học Tiếng Lào Admin Panel')
@include('components.head')
<body class="bg-gradient-to-br from-[#232946] to-[#3b2f63] min-h-screen text-gray-100">
<div class="flex">
    <!-- Sidebar -->
    @include('components.sidebar')
    <!-- Header Navigation Bar -->
    <div class="flex-1 flex flex-col min-h-screen">
        <x-header title="Quản lý Users">
            <x-slot name="left">
                <a href="/" class="bg-gradient-to-r from-gray-600 to-gray-700 text-white font-bold p-2 rounded-lg shadow hover:from-gray-700 hover:to-gray-800 transition flex items-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
            </x-slot>
        </x-header>
        <!-- Main Content -->
        <main class="flex-1 flex flex-col items-center justify-start py-8 px-2">
            <div class="w-full h-full">
                <section class="h-full flex flex-col justify-start">
                    <div class="table-card w-full h-full flex flex-col">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 w-full mb-4">
                            <input id="user-search" type="text" placeholder="Tìm kiếm username/email..." class="table-search">
                            <button id="add-user-btn" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-6 py-2 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Thêm user
                            </button>
                        </div>
                        <div class="overflow-x-auto w-full">
                            <table class="table-admin">
                                <thead class="table-header">
                                    <tr>
                                        <th class="table-cell">Username</th>
                                        <th class="table-cell">Email</th>
                                        <th class="table-cell">Ngày tạo</th>
                                        <th class="table-cell text-center">Is Admin</th>
                                        <th class="table-cell">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody id="users-tbody" class="w-full"></tbody>
                            </table>
                        </div>
                        <div id="users-loading" class="flex justify-center items-center py-8 w-full">
                            <div class="w-8 h-8 border-4 border-purple-400 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                        <div id="users-error" class="hidden mt-4 p-3 rounded-lg bg-red-500/20 text-red-200 w-full"></div>
                    </div>
                </section>
            </div>
            <!-- Modal Thêm/Sửa User -->
            <div id="user-modal" tabindex="-1" class="hidden fixed top-0 left-0 right-0 z-50 flex justify-center items-center w-full h-full bg-black/40">
                <div class="bg-[#232946] rounded-2xl shadow-xl p-8 w-full max-w-md relative">
                    <button id="close-user-modal" class="absolute top-3 right-3 text-purple-300 hover:text-pink-400 text-2xl">&times;</button>
                    <h3 id="user-modal-title" class="text-xl font-bold text-purple-200 mb-4">Thêm user</h3>
                    <form id="user-form" class="space-y-4">
                        <div>
                            <label class="block text-purple-100 mb-1">Username</label>
                            <input id="user-username" name="username" type="text" class="w-full px-4 py-2 rounded-lg bg-[#2d3250] text-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-400 placeholder-purple-400" required>
                        </div>
                        <div>
                            <label class="block text-purple-100 mb-1">Email</label>
                            <input id="user-email" name="email" type="email" class="w-full px-4 py-2 rounded-lg bg-[#2d3250] text-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-400 placeholder-purple-400" required>
                        </div>
                        <div>
                            <label class="block text-purple-100 mb-1">Mật khẩu</label>
                            <input id="user-password" name="password" type="password" class="w-full px-4 py-2 rounded-lg bg-[#2d3250] text-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-400 placeholder-purple-400">
                        </div>
                        <div class="flex items-center gap-2">
                            <input id="user-is-admin" name="is_admin" type="checkbox" class="w-5 h-5 text-purple-400 bg-[#2d3250] border-purple-400 rounded focus:ring-purple-400">
                            <label for="user-is-admin" class="text-purple-100">Admin</label>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-6 py-2 rounded-lg shadow hover:from-pink-400 hover:to-purple-400 transition">Lưu</button>
                    </form>
                </div>
            </div>
            <!-- Modal Confirm Xoá -->
            <div id="confirm-modal" tabindex="-1" class="hidden fixed top-0 left-0 right-0 z-50 flex justify-center items-center w-full h-full bg-black/40">
                <div class="bg-[#232946] rounded-2xl shadow-xl p-8 w-full max-w-sm relative">
                    <button id="close-confirm-modal" class="absolute top-3 right-3 text-purple-300 hover:text-pink-400 text-2xl">&times;</button>
                    <h3 class="text-xl font-bold text-red-400 mb-4">Xác nhận xoá user</h3>
                    <p class="text-purple-100 mb-6">Bạn có chắc chắn muốn xoá user <span id="confirm-username" class="font-bold text-pink-400"></span> không?</p>
                    <div class="flex gap-4">
                        <button id="confirm-delete-btn" class="flex-1 bg-red-500/80 hover:bg-red-600 text-white font-bold px-6 py-2 rounded-lg shadow transition">Xoá</button>
                        <button id="cancel-delete-btn" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold px-6 py-2 rounded-lg shadow transition">Huỷ</button>
                    </div>
                </div>
            </div>
            <div id="toast-alert" class="toast-alert"></div>
        </main>
    </div>
</div>
<!-- Script xử lý CRUD sẽ được bổ sung ở bước tiếp theo -->
<script>
// Biến global để lưu danh sách users
let usersList = [];

// Kiểm tra authentication khi trang load
document.addEventListener('DOMContentLoaded', function() {
    const token = sessionStorage.getItem('access_token');
    const user = sessionStorage.getItem('user');
    
    if (!token || !user) {
        window.location.href = '/login';
        return;
    }
    
    try {
        const userData = JSON.parse(user);
        if (!userData.is_admin) {
            window.location.href = '/login';
            return;
        }
        
        // Load danh sách users
        loadUsers();
    } catch (error) {
        window.location.href = '/login';
    }
});

// Load danh sách users
async function loadUsers() {
    const token = sessionStorage.getItem('access_token');
    const loading = document.getElementById('users-loading');
    const tbody = document.getElementById('users-tbody');
    const errorMessage = document.getElementById('users-error');
    
    try {
        const response = await fetch('/supabase/users', {
            method: 'GET',
            headers: {
                'Authorization': token
            }
        });
        
        if (response.ok) {
            const users = await response.json();
            displayUsers(users);
            loading.style.display = 'none';
            tbody.style.display = 'table-row-group';
        } else {
            throw new Error('Không thể tải danh sách users');
        }
    } catch (error) {
        loading.style.display = 'none';
        errorMessage.textContent = 'Có lỗi xảy ra khi tải danh sách users: ' + error.message;
        errorMessage.style.display = 'block';
    }
}

// Format ngày tạo
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return '';
    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

// Hiển thị danh sách users
function displayUsers(users) {
    const tbody = document.getElementById('users-tbody');
    tbody.innerHTML = '';
    
    // Lưu danh sách users để sử dụng cho edit
    usersList = users;
    
    users.forEach(user => {
        const isProtectedUser = user.username === 'nhockool1002';
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="table-cell">
                ${user.username || 'N/A'}
                ${isProtectedUser ? '<span class="ml-2 px-2 py-1 text-xs bg-red-500/20 text-red-300 rounded">Root Administrator</span>' : ''}
            </td>
            <td class="table-cell">${user.email || 'N/A'}</td>
            <td class="table-cell">${formatDate(user.createdat)}</td>
            <td class="table-cell text-center">
                ${user.is_admin ? '<span class="inline-block w-4 h-4 rounded-full bg-green-400 table-badge"></span>' : '<span class="inline-table-dot"></span>'}
            </td>
            <td class="table-cell text-center">
                <button onclick="editUser('${user.username}')" 
                        class="mr-2 ${isProtectedUser ? 'text-gray-500 cursor-not-allowed opacity-50' : 'text-blue-400 hover:text-blue-300 table-action-edit'}" 
                        ${isProtectedUser ? 'disabled' : ''}>
                    Sửa
                </button>
                <button onclick="deleteUser('${user.username}')" 
                        class="${isProtectedUser ? 'text-gray-500 cursor-not-allowed opacity-50' : 'text-red-400 hover:text-red-300 table-action-delete'}" 
                        ${isProtectedUser ? 'disabled' : ''}>
                    Xoá
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Xử lý thêm/sửa user
let currentEditUser = null;

document.getElementById('add-user-btn').addEventListener('click', function() {
    currentEditUser = null;
    document.getElementById('user-modal-title').textContent = 'Thêm user';
    document.getElementById('user-form').reset();
    document.getElementById('user-password').required = true; // Bắt buộc nhập password khi thêm mới
    document.getElementById('user-password').placeholder = 'Nhập mật khẩu';
    document.getElementById('user-username').readOnly = false; // Cho phép sửa username khi thêm mới
    document.getElementById('user-modal').classList.remove('hidden');
});

document.getElementById('close-user-modal').addEventListener('click', function() {
    document.getElementById('user-modal').classList.add('hidden');
    // Reset form khi đóng modal
    document.getElementById('user-form').reset();
    document.getElementById('user-username').readOnly = false;
    document.getElementById('user-password').required = true;
    document.getElementById('user-password').placeholder = 'Nhập mật khẩu';
});

function editUser(username) {
    // Kiểm tra nếu là user được bảo vệ
    if (username === 'nhockool1002') {
        showToast('Không thể sửa user này!', 'error');
        return;
    }
    
    currentEditUser = username;
    document.getElementById('user-modal-title').textContent = 'Sửa user';
    
    // Tìm thông tin user từ danh sách
    const user = usersList.find(u => u.username === username);
    if (user) {
        // Load thông tin user lên form
        document.getElementById('user-username').value = user.username || '';
        document.getElementById('user-username').readOnly = true; // Không cho phép sửa username khi edit
        document.getElementById('user-email').value = user.email || '';
        document.getElementById('user-password').value = ''; // Không hiển thị password cũ
        document.getElementById('user-password').required = false; // Không bắt buộc nhập password khi sửa
        document.getElementById('user-password').placeholder = 'Để trống nếu không thay đổi';
        document.getElementById('user-is-admin').checked = user.is_admin || false;
    }
    
    document.getElementById('user-modal').classList.remove('hidden');
}

document.getElementById('user-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const username = formData.get('username');
    const email = formData.get('email');
    const password = formData.get('password');
    const isAdmin = formData.get('is_admin') === 'on';
    
    // Kiểm tra nếu đang sửa user được bảo vệ
    if (currentEditUser && currentEditUser === 'nhockool1002') {
        showToast('Không thể sửa user này!', 'error');
        return;
    }
    
    // Validation
    if (!username || !email) {
        showToast('Vui lòng nhập đầy đủ thông tin!', 'error');
        return;
    }
    
    // Kiểm tra email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showToast('Email không hợp lệ!', 'error');
        return;
    }
    
    // Kiểm tra password khi thêm mới
    if (!currentEditUser && !password) {
        showToast('Vui lòng nhập mật khẩu!', 'error');
        return;
    }
    
    const userData = {
        username: username,
        email: email,
        is_admin: isAdmin
    };
    
    // Chỉ thêm password nếu có nhập
    if (password) {
        userData.password = password;
    }
    
    const token = sessionStorage.getItem('access_token');
    const csrfToken = getCsrfToken();
    const url = currentEditUser ? `/supabase/users/${currentEditUser}` : '/supabase/users';
    const method = currentEditUser ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': token,
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(userData)
        });
        
        if (response.ok) {
            document.getElementById('user-modal').classList.add('hidden');
            loadUsers(); // Reload danh sách
            showToast(currentEditUser ? 'Cập nhật user thành công!' : 'Thêm user thành công!', 'success');
        } else {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        showToast('Có lỗi xảy ra: ' + error.message, 'error');
    }
});

// Xử lý xoá user
function deleteUser(username) {
    // Kiểm tra nếu là user được bảo vệ
    if (username === 'nhockool1002') {
        showToast('Không thể xoá user này!', 'error');
        return;
    }
    
    document.getElementById('confirm-username').textContent = username;
    document.getElementById('confirm-modal').classList.remove('hidden');
}

document.getElementById('close-confirm-modal').addEventListener('click', function() {
    document.getElementById('confirm-modal').classList.add('hidden');
});

document.getElementById('cancel-delete-btn').addEventListener('click', function() {
    document.getElementById('confirm-modal').classList.add('hidden');
});

document.getElementById('confirm-delete-btn').addEventListener('click', async function() {
    const username = document.getElementById('confirm-username').textContent;
    const token = sessionStorage.getItem('access_token');
    const csrfToken = getCsrfToken();
    
    try {
        const response = await fetch(`/supabase/users/${username}`, {
            method: 'DELETE',
            headers: {
                'Authorization': token,
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        if (response.ok) {
            document.getElementById('confirm-modal').classList.add('hidden');
            loadUsers(); // Reload danh sách
            showToast('Xoá user thành công!', 'success');
        } else {
            throw new Error('Có lỗi xảy ra');
        }
    } catch (error) {
        showToast('Có lỗi xảy ra: ' + error.message, 'error');
    }
});

// Hiển thị toast message
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-alert');
    toast.textContent = message;
    toast.className = 'toast-alert ' + (type === 'success' ? 'toast-success' : 'toast-failed');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2500);
}

// Tìm kiếm users
document.getElementById('user-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#users-tbody tr');
    
    rows.forEach(row => {
        const username = row.cells[0].textContent.toLowerCase();
        const email = row.cells[1].textContent.toLowerCase();
        
        if (username.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Xử lý logout
document.getElementById('logout-menu').addEventListener('click', async function(e) {
    e.preventDefault();
    
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

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}
</script>
<!-- Thêm Alpine.js cho hiệu ứng toggle -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html> 