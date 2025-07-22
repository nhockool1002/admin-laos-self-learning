<!DOCTYPE html>
<html lang="vi">
@section('title', 'Quản lý Huy hiệu | Học Tiếng Lào Admin Panel')
@include('components.head')
<body class="bg-gradient-to-br from-[#232946] to-[#3b2f63] min-h-screen text-gray-100">
<div class="flex">
    <!-- Sidebar -->
    @include('components.sidebar')
    <div class="flex-1 flex flex-col min-h-screen lg:ml-0">
        <x-header title="Quản lý Huy hiệu">
        </x-header>
        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Filters and Search -->
            <div class="bg-[#232946] rounded-2xl shadow-xl p-6 mb-6">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex-1">
                        <input type="text" id="search-input" placeholder="Tìm kiếm huy hiệu..." class="w-full px-4 py-3 bg-[#2d3250] text-white rounded-xl border border-purple-400/30 focus:border-purple-400 focus:outline-none">
                    </div>
                    <button id="refresh-btn" class="bg-gradient-to-r from-blue-500 to-purple-500 text-white px-6 py-3 rounded-xl shadow hover:from-purple-500 hover:to-blue-500 transition">
                        <i class="fa-solid fa-refresh mr-2"></i>Làm mới
                    </button>
                </div>
            </div>

            <!-- Badge List -->
            <div class="bg-[#232946] rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-purple-200">Danh sách Huy hiệu</h2>
                    <div class="flex items-center gap-2">
                        <div class="text-sm text-purple-100 mr-4">
                            Tổng: <span id="total-badges" class="text-purple-300 font-semibold">0</span> huy hiệu
                        </div>
                        <!-- Nút Thêm Huy hiệu trong table -->
                        <button id="add-badge-btn" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-6 py-3 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition text-sm">
                            <i class="fa-solid fa-plus mr-2"></i>Thêm Huy hiệu
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-purple-400/30">
                                <th class="text-left py-3 px-4 text-purple-200 font-semibold">Hình ảnh</th>
                                <th class="text-left py-3 px-4 text-purple-200 font-semibold">Tên</th>
                                <th class="text-left py-3 px-4 text-purple-200 font-semibold">Mô tả</th>
                                <th class="text-left py-3 px-4 text-purple-200 font-semibold">Ngày tạo</th>
                                <th class="text-center py-3 px-4 text-purple-200 font-semibold">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="badges-table">
                            <!-- Content will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Loading state -->
                <div id="loading" class="text-center py-8 hidden">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-400"></div>
                    <p class="mt-2 text-purple-200">Đang tải...</p>
                </div>

                <!-- Empty state -->
                <div id="empty-state" class="text-center py-12 hidden">
                    <i class="fa-solid fa-award text-6xl text-purple-400/50 mb-4"></i>
                    <h3 class="text-xl font-semibold text-purple-200 mb-2">Chưa có huy hiệu nào</h3>
                    <p class="text-purple-100 mb-4">Hãy tạo huy hiệu đầu tiên để bắt đầu!</p>
                    <button class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-6 py-3 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition" onclick="document.getElementById('add-badge-btn').click()">
                        <i class="fa-solid fa-plus mr-2"></i>Thêm Huy hiệu
                    </button>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add/Edit Badge Modal -->
<div id="badge-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-[#232946] rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 id="modal-title" class="text-xl font-bold text-purple-200">Thêm Huy hiệu</h3>
                <button id="close-modal" class="text-purple-200 hover:text-white transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="badge-form" enctype="multipart/form-data">
                <input type="hidden" id="badge-id" name="badge_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-purple-200 mb-2">Tên huy hiệu *</label>
                    <input type="text" id="badge-name" name="name" required class="w-full px-4 py-3 bg-[#2d3250] text-white rounded-xl border border-purple-400/30 focus:border-purple-400 focus:outline-none">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-purple-200 mb-2">Mô tả *</label>
                    <textarea id="badge-description" name="description" required rows="3" class="w-full px-4 py-3 bg-[#2d3250] text-white rounded-xl border border-purple-400/30 focus:border-purple-400 focus:outline-none resize-none"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-purple-200 mb-2">Điều kiện</label>
                    <input type="text" id="badge-condition" name="condition" class="w-full px-4 py-3 bg-[#2d3250] text-white rounded-xl border border-purple-400/30 focus:border-purple-400 focus:outline-none" placeholder="Điều kiện để đạt huy hiệu">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-purple-200 mb-2">Hình ảnh *</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="badge-image" class="flex flex-col items-center justify-center w-full h-32 border-2 border-purple-400/30 border-dashed rounded-xl cursor-pointer bg-[#2d3250] hover:bg-[#3a3465] transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fa-solid fa-cloud-upload-alt text-3xl text-purple-400 mb-2"></i>
                                <p class="mb-2 text-sm text-purple-200"><span class="font-semibold">Click để tải lên</span> hoặc kéo thả</p>
                                <p class="text-xs text-purple-100">PNG, JPG, GIF, SVG (MAX. 2MB)</p>
                            </div>
                            <input id="badge-image" name="image" type="file" class="hidden" accept="image/*">
                        </label>
                    </div>
                    
                    <!-- Image Preview -->
                    <div id="image-preview" class="mt-4 hidden">
                        <img id="preview-img" src="" alt="Preview" class="w-20 h-20 object-cover rounded-xl border border-purple-400/30">
                        <button type="button" id="remove-image" class="ml-2 text-red-400 hover:text-red-300 text-sm">
                            <i class="fa-solid fa-trash mr-1"></i>Xóa
                        </button>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" id="cancel-btn" class="flex-1 bg-gray-600 text-white py-3 rounded-xl hover:bg-gray-700 transition font-medium">Hủy</button>
                    <button type="submit" id="submit-btn" class="flex-1 bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] py-3 rounded-xl hover:from-pink-400 hover:to-purple-400 transition font-bold">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Award Badge Modal -->
<div id="award-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-[#232946] rounded-2xl shadow-2xl w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-purple-200">Tặng Huy hiệu</h3>
                <button id="close-award-modal" class="text-purple-200 hover:text-white transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="award-form">
                <input type="hidden" id="award-badge-id" name="badge_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-purple-200 mb-2">Tên người dùng *</label>
                    <input type="text" id="username-input" name="username" required list="user-list" class="w-full px-4 py-3 bg-[#2d3250] text-white rounded-xl border border-purple-400/30 focus:border-purple-400 focus:outline-none" placeholder="Nhập username">
                    <datalist id="user-list"></datalist>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" id="cancel-award-btn" class="flex-1 bg-gray-600 text-white py-3 rounded-xl hover:bg-gray-700 transition font-medium">Hủy</button>
                    <button type="submit" class="flex-1 bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] py-3 rounded-xl hover:from-pink-400 hover:to-purple-400 transition font-bold">Tặng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- User Badges Modal -->
<div id="user-badges-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-[#232946] rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-purple-200">Quản lý Huy hiệu Người dùng</h3>
                <button id="close-user-badges-modal" class="text-purple-200 hover:text-white transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="user-badges-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
let badges = [];
let users = [];
let isEditing = false;

// Load page data
document.addEventListener('DOMContentLoaded', function() {
    loadBadges();
    loadUsers();
    setupEventListeners();
});

function setupEventListeners() {
    // Add badge button (đảm bảo gán lại sự kiện)
    document.getElementById('add-badge-btn').addEventListener('click', function() {
        openBadgeModal();
    });
    
    // Modal close buttons
    document.getElementById('close-modal').addEventListener('click', closeBadgeModal);
    document.getElementById('cancel-btn').addEventListener('click', closeBadgeModal);
    document.getElementById('close-award-modal').addEventListener('click', closeAwardModal);
    document.getElementById('cancel-award-btn').addEventListener('click', closeAwardModal);
    document.getElementById('close-user-badges-modal').addEventListener('click', closeUserBadgesModal);
    
    // Search
    document.getElementById('search-input').addEventListener('input', function() {
        filterBadges(this.value);
    });
    
    // Refresh
    document.getElementById('refresh-btn').addEventListener('click', function() {
        loadBadges();
    });
    
    // Badge form
    document.getElementById('badge-form').addEventListener('submit', handleBadgeSubmit);
    document.getElementById('award-form').addEventListener('submit', handleAwardSubmit);
    
    // Image upload
    document.getElementById('badge-image').addEventListener('change', handleImagePreview);
    document.getElementById('remove-image').addEventListener('click', removeImagePreview);
}

async function loadBadges() {
    try {
        showLoading();
        const token = sessionStorage.getItem('access_token');
        const response = await fetch('/supabase/badges', {
            headers: {
                'Authorization': token
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load badges');
        }

        badges = await response.json() || [];
        renderBadges(badges);
        hideLoading();
    } catch (error) {
        console.error('Error loading badges:', error);
        showErrorMessage('Không thể tải danh sách huy hiệu');
        hideLoading();
    }
}

async function loadUsers() {
    try {
        const token = sessionStorage.getItem('access_token');
        const response = await fetch('/supabase/users', {
            headers: {
                'Authorization': token
            }
        });

        if (response.ok) {
            users = await response.json() || [];
            populateUserSelect();
            renderUserDatalist(); // render datalist autocomplete
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

function populateUserSelect() {
    const select = document.getElementById('user-select');
    select.innerHTML = '<option value="">-- Chọn người dùng --</option>';
    
    users.forEach(user => {
        const option = document.createElement('option');
        option.value = user.id;
        option.textContent = `${user.username} (${user.email})`;
        select.appendChild(option);
    });
}

function renderBadges(badgesToRender) {
    const tbody = document.getElementById('badges-table');
    const totalElement = document.getElementById('total-badges');
    const emptyState = document.getElementById('empty-state');
    
    totalElement.textContent = badgesToRender.length;
    
    if (badgesToRender.length === 0) {
        tbody.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    tbody.innerHTML = badgesToRender.map(badge => `
        <tr class="border-b border-purple-400/20 hover:bg-purple-700/20 transition">
            <td class="py-4 px-4">
                <img src="${badge.image_path}" alt="${badge.name}" class="w-12 h-12 object-cover rounded-lg border border-purple-400/30">
            </td>
            <td class="py-4 px-4">
                <div class="font-semibold text-white">${badge.name}</div>
                <div class="text-xs text-purple-200">${badge.id}</div>
            </td>
            <td class="py-4 px-4">
                <div class="text-purple-100 max-w-xs truncate" title="${badge.description}">${badge.description}</div>
                <div class="text-xs text-purple-300">Điều kiện: ${badge.condition || 'Không có'}</div>
            </td>
            <td class="py-4 px-4">
                <div class="text-purple-100 text-sm">${formatDate(badge.created_at)}</div>
            </td>
            <td class="py-4 px-4">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editBadge('${badge.id}')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg transition text-sm" title="Chỉnh sửa">
                        <i class="fa-solid fa-edit"></i>
                    </button>
                    <button onclick="openAwardModal('${badge.id}')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg transition text-sm" title="Tặng huy hiệu">
                        <i class="fa-solid fa-medal"></i>
                    </button>
                    <button onclick="manageUserBadges('${badge.id}')" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-lg transition text-sm" title="Quản lý người dùng">
                        <i class="fa-solid fa-users"></i>
                    </button>
                    <button onclick="deleteBadge('${badge.id}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition text-sm" title="Xóa">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function filterBadges(query) {
    if (!query.trim()) {
        renderBadges(badges);
        return;
    }
    
    const filtered = badges.filter(badge => 
        badge.name.toLowerCase().includes(query.toLowerCase()) ||
        badge.description.toLowerCase().includes(query.toLowerCase())
    );
    
    renderBadges(filtered);
}

function openBadgeModal(badge = null) {
    isEditing = !!badge;
    const modal = document.getElementById('badge-modal');
    const title = document.getElementById('modal-title');
    const form = document.getElementById('badge-form');
    
    title.textContent = isEditing ? 'Chỉnh sửa Huy hiệu' : 'Thêm Huy hiệu';
    
    if (badge) {
        document.getElementById('badge-id').value = badge.id;
        document.getElementById('badge-name').value = badge.name;
        document.getElementById('badge-description').value = badge.description;
        document.getElementById('badge-condition').value = badge.condition || '';
        
        // Show current image
        if (badge.image_path) {
            document.getElementById('preview-img').src = badge.image_path;
            document.getElementById('image-preview').classList.remove('hidden');
        }
    } else {
        form.reset();
        document.getElementById('image-preview').classList.add('hidden');
    }
    
    modal.classList.remove('hidden');
}

function closeBadgeModal() {
    document.getElementById('badge-modal').classList.add('hidden');
    document.getElementById('badge-form').reset();
    document.getElementById('image-preview').classList.add('hidden');
    isEditing = false;
}

function openAwardModal(badgeId) {
    document.getElementById('award-badge-id').value = badgeId;
    renderUserDatalist(); // render lại mỗi lần mở modal
    document.getElementById('award-modal').classList.remove('hidden');
}

function closeAwardModal() {
    document.getElementById('award-modal').classList.add('hidden');
    document.getElementById('award-form').reset();
}

function closeUserBadgesModal() {
    document.getElementById('user-badges-modal').classList.add('hidden');
}

async function handleBadgeSubmit(e) {
    e.preventDefault();
    try {
        const token = sessionStorage.getItem('access_token');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const formData = new FormData(e.target);
        const url = isEditing ? `/supabase/badges/${document.getElementById('badge-id').value}` : '/supabase/badges';
        const method = isEditing ? 'PUT' : 'POST';
        if (isEditing) {
            if (formData.get('image') && formData.get('image').size > 0) {
                const fileFormData = new FormData();
                fileFormData.append('image', formData.get('image'));
                fileFormData.append('name', formData.get('name'));
                fileFormData.append('description', formData.get('description'));
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Authorization': token,
                        'X-HTTP-Method-Override': 'PUT',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: fileFormData
                });
                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.error || 'Failed to update badge');
                }
            } else {
                const data = {};
                for (let [key, value] of formData.entries()) {
                    if (key !== 'badge_id' && value) {
                        data[key] = value;
                    }
                }
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': token,
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });
                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.error || 'Failed to update badge');
                }
            }
        } else {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Authorization': token,
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Failed to create badge');
            }
        }
        showSuccessMessage(isEditing ? 'Cập nhật huy hiệu thành công!' : 'Tạo huy hiệu thành công!');
        closeBadgeModal();
        loadBadges();
    } catch (error) {
        console.error('Error submitting badge:', error);
        showErrorMessage(error.message);
    }
}

async function handleAwardSubmit(e) {
    e.preventDefault();
    try {
        const token = sessionStorage.getItem('access_token');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const formData = new FormData(e.target);
        const data = {
            username: formData.get('username'),
            badge_id: formData.get('badge_id')
        };

        const response = await fetch('/supabase/user-badges/award', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': token,
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to award badge');
        }

        showSuccessMessage('Tặng huy hiệu thành công!');
        closeAwardModal();
    } catch (error) {
        console.error('Error awarding badge:', error);
        showErrorMessage(error.message);
    }
}

function handleImagePreview(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

function removeImagePreview() {
    document.getElementById('badge-image').value = '';
    document.getElementById('image-preview').classList.add('hidden');
}

function editBadge(id) {
    const badge = badges.find(b => b.id === id);
    if (badge) {
        openBadgeModal(badge);
    }
}

async function deleteBadge(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa huy hiệu này? Thao tác này không thể hoàn tác.')) {
        return;
    }
    try {
        const token = sessionStorage.getItem('access_token');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch(`/supabase/badges/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': token,
                'X-CSRF-TOKEN': csrfToken
            }
        });

        if (!response.ok) {
            throw new Error('Failed to delete badge');
        }

        showSuccessMessage('Xóa huy hiệu thành công!');
        loadBadges();
    } catch (error) {
        console.error('Error deleting badge:', error);
        showErrorMessage('Không thể xóa huy hiệu');
    }
}

async function manageUserBadges(badgeId) {
    try {
        const token = sessionStorage.getItem('access_token');
        const response = await fetch('/supabase/users-with-badges', {
            headers: {
                'Authorization': token
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load users with badges');
        }

        const usersWithBadges = await response.json() || [];
        const badge = badges.find(b => b.id === badgeId);
        
        renderUserBadgesModal(badge, usersWithBadges);
        document.getElementById('user-badges-modal').classList.remove('hidden');
    } catch (error) {
        console.error('Error loading user badges:', error);
        showErrorMessage('Không thể tải thông tin người dùng');
    }
}

function renderUserBadgesModal(badge, usersWithBadges) {
    const content = document.getElementById('user-badges-content');
    
    // Filter users who have this badge
    const usersWithThisBadge = usersWithBadges.filter(user => 
        user.user_badges && user.user_badges.some(ub => ub.badge_id === badge.id)
    );
    
            content.innerHTML = `
        <div class="mb-6">
            <div class="flex items-center gap-4 p-4 bg-[#2d3250] rounded-xl">
                <img src="${badge.image_path}" alt="${badge.name}" class="w-16 h-16 object-cover rounded-lg border border-purple-400/30">
                <div>
                    <h4 class="text-lg font-semibold text-white">${badge.name}</h4>
                    <p class="text-purple-100">${badge.description}</p>
                    <p class="text-xs text-purple-300">ID: ${badge.id}</p>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <h4 class="text-lg font-semibold text-purple-200 mb-3">Người dùng có huy hiệu này (${usersWithThisBadge.length})</h4>
            ${usersWithThisBadge.length === 0 ? 
                '<p class="text-purple-100 text-center py-4">Chưa có người dùng nào được tặng huy hiệu này</p>' :
                `<div class="space-y-2 max-h-60 overflow-y-auto">
                    ${usersWithThisBadge.map(user => {
                        const userBadge = user.user_badges.find(ub => ub.badge_id === badge.id);
                        return `
                            <div class="flex items-center justify-between p-3 bg-[#2d3250] rounded-lg">
                                <div>
                                    <div class="font-semibold text-white">${user.username}</div>
                                    <div class="text-sm text-purple-100">${user.email}</div>
                                    <div class="text-xs text-purple-200">Tặng lúc: ${formatDate(userBadge.achieved_date)}</div>
                                </div>
                                <button onclick="revokeBadge('${user.username}', '${badge.id}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition text-sm">
                                    <i class="fa-solid fa-times mr-1"></i>Thu hồi
                                </button>
                            </div>
                        `;
                    }).join('')}
                </div>`
            }
        </div>
    `;
}

async function revokeBadge(username, badgeId) {
    if (!confirm('Bạn có chắc chắn muốn thu hồi huy hiệu này từ người dùng?')) {
        return;
    }
    
    try {
        const token = sessionStorage.getItem('access_token');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch('/supabase/user-badges/revoke', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': token,
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                username: username,
                badge_id: badgeId
            })
        });

        if (!response.ok) {
            throw new Error('Failed to revoke badge');
        }

        showSuccessMessage('Thu hồi huy hiệu thành công!');
        // Refresh the modal content
        manageUserBadges(badgeId);
    } catch (error) {
        console.error('Error revoking badge:', error);
        showErrorMessage('Không thể thu hồi huy hiệu');
    }
}

// Utility functions
function showLoading() {
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('badges-table').innerHTML = '';
    document.getElementById('empty-state').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loading').classList.add('hidden');
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Xóa hàm getStoredUser vì không còn sử dụng nữa
function showSuccessMessage(message) {
    // Simple alert for now - can be replaced with a toast notification
    alert(message);
}

function showErrorMessage(message) {
    // Simple alert for now - can be replaced with a toast notification
    alert('Lỗi: ' + message);
}

function renderUserDatalist() {
    const datalist = document.getElementById('user-list');
    if (!datalist) return;
    datalist.innerHTML = users.map(u => `<option value="${u.username}">${u.email}</option>`).join('');
}
</script>

@include('components.header-logout')
</body>
</html>