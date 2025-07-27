<!DOCTYPE html>
<html lang="vi">
@section('title', 'Cài đặt trò chơi theo bài học | Admin Panel')
@include('components.head')
<body class="bg-[#232946] text-gray-100 min-h-screen">
<div id="toast-alert" class="toast-alert"></div>
<div class="flex">
    <!-- Sidebar -->
    @include('components.sidebar')
    <div class="flex-1 flex flex-col min-h-screen lg:ml-0">
        <x-header title="Cài đặt trò chơi theo bài học">
        </x-header>
        <main class="flex-1 flex flex-col items-center justify-start py-8 px-2">
            <div class="w-full h-full">
                <!-- Tab Navigation -->
                <div class="mb-6">
                    <div class="flex space-x-1 bg-[#2d3250] rounded-lg p-1">
                        <button id="tab-groups" class="tab-button active px-4 py-2 rounded-md text-sm font-medium transition-all duration-200">
                            Nhóm trò chơi
                        </button>
                        <button id="tab-games" class="tab-button px-4 py-2 rounded-md text-sm font-medium transition-all duration-200">
                            Trò chơi
                        </button>
                    </div>
                </div>

                <!-- Groups Tab Content -->
                <div id="groups-content" class="tab-content">
                    <div class="table-card w-full h-full flex flex-col">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 w-full mb-4">
                            <div class="flex gap-2">
                                <button id="btn-add-group" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-6 py-2 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    Thêm nhóm trò chơi
                                </button>
                                <button id="btn-debug-groups" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                                    Debug Groups
                                </button>
                            </div>
                            <input id="search-groups" type="text" placeholder="Tìm kiếm nhóm..." class="ml-auto px-3 py-2 rounded bg-[#2d3250] text-white w-60 focus:outline-none focus:ring-2 focus:ring-purple-400" />
                        </div>
                        <div class="overflow-x-auto w-full">
                            <table class="table-admin">
                                <thead class="table-header">
                                    <tr>
                                        <th class="table-cell cursor-pointer" data-sort="name">Tên nhóm <span class="sort-icon"></span></th>
                                        <th class="table-cell cursor-pointer" data-sort="description">Mô tả <span class="sort-icon"></span></th>
                                        <th class="table-cell">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="groups-list"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Games Tab Content -->
                <div id="games-content" class="tab-content hidden">
                    <div class="table-card w-full h-full flex flex-col">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 w-full mb-4">
                            <button id="btn-add-game" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-6 py-2 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Thêm trò chơi
                            </button>
                            <input id="search-games" type="text" placeholder="Tìm kiếm trò chơi..." class="ml-auto px-3 py-2 rounded bg-[#2d3250] text-white w-60 focus:outline-none focus:ring-2 focus:ring-purple-400" />
                        </div>
                        <div class="overflow-x-auto w-full">
                            <table class="table-admin">
                                <thead class="table-header">
                                    <tr>
                                        <th class="table-cell cursor-pointer" data-sort="title">Tên trò chơi <span class="sort-icon"></span></th>
                                        <th class="table-cell cursor-pointer" data-sort="description">Mô tả <span class="sort-icon"></span></th>
                                        <th class="table-cell cursor-pointer" data-sort="group">Nhóm <span class="sort-icon"></span></th>
                                        <th class="table-cell">Link nhúng</th>
                                        <th class="table-cell">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="games-list"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal thêm/sửa nhóm -->
                <div id="group-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden">
                    <div class="bg-[#232946] p-6 rounded-xl w-full max-w-md">
                        <h2 id="group-modal-title" class="text-xl font-bold mb-4">Thêm nhóm trò chơi</h2>
                        <form id="group-form" class="space-y-3">
                            <input type="hidden" id="group-id">
                            <div>
                                <label class="block mb-1">Tên nhóm</label>
                                <input id="group-name" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white" required>
                            </div>
                            <div>
                                <label class="block mb-1">Mô tả</label>
                                <textarea id="group-desc" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white"></textarea>
                            </div>
                            <div class="flex gap-2 justify-end">
                                <button type="button" id="btn-cancel-group" class="px-4 py-2 rounded bg-gray-500 hover:bg-gray-600">Huỷ</button>
                                <button type="submit" class="px-4 py-2 rounded bg-purple-600 hover:bg-purple-700 text-white">Lưu</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal thêm/sửa trò chơi -->
                <div id="game-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden">
                    <div class="bg-[#232946] p-6 rounded-xl w-full max-w-md">
                        <h2 id="game-modal-title" class="text-xl font-bold mb-4">Thêm trò chơi</h2>
                        <form id="game-form" class="space-y-3">
                            <input type="hidden" id="game-id">
                            <div>
                                <label class="block mb-1">Tên trò chơi</label>
                                <input id="game-title" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white" required>
                            </div>
                            <div>
                                <label class="block mb-1">Mô tả</label>
                                <textarea id="game-desc" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white"></textarea>
                            </div>
                            <div>
                                <label class="block mb-1">Nhóm trò chơi</label>
                                <select id="game-group" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white"></select>
                            </div>
                            <div>
                                <label class="block mb-1">Link nhúng</label>
                                <input id="game-embed" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white" required>
                            </div>
                            <div class="flex gap-2 justify-end">
                                <button type="button" id="btn-cancel-game" class="px-4 py-2 rounded bg-gray-500 hover:bg-gray-600">Huỷ</button>
                                <button type="submit" class="px-4 py-2 rounded bg-purple-600 hover:bg-purple-700 text-white">Lưu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
const token = sessionStorage.getItem('access_token');
const user = JSON.parse(sessionStorage.getItem('user') || '{}');
if (!token || !user.is_admin) window.location.href = '/login';

const API_GAMES = '/supabase/lesson-games';
const API_GROUPS = '/supabase/lesson-game-groups';

let editingGroupId = null;
let editingGameId = null;
let groupsData = [];
let gamesData = [];
let currentGroupSort = { key: '', asc: true };
let currentGameSort = { key: '', asc: true };

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

// Tab switching
document.getElementById('tab-groups').onclick = () => switchTab('groups');
document.getElementById('tab-games').onclick = () => switchTab('games');

function switchTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`tab-${tab}`).classList.add('active');
    
    // Update content visibility
    document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
    document.getElementById(`${tab}-content`).classList.remove('hidden');
    
    // Load data based on active tab
    if (tab === 'groups') {
        fetchGroups();
    } else {
        fetchGroups(); // Load groups first for dropdown
        fetchGames();
    }
}

// Groups functionality
function fetchGroups() {
    console.log('Fetching lesson game groups (Type B) from:', API_GROUPS);
    
    // Add cache busting parameter
    const cacheBuster = '?t=' + Date.now();
    
    fetch(API_GROUPS + cacheBuster, { 
        headers: { 
            'Authorization': token, 
            'User': JSON.stringify(user),
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache'
        } 
    })
        .then(res => res.json())
        .then(data => {
            console.log('Received lesson game groups:', data);
            groupsData = data || [];
            
            // Verify that all groups are Type B
            const typeBGroups = groupsData.filter(g => !g.group_game_type || g.group_game_type === 'B');
            const typeAGroups = groupsData.filter(g => g.group_game_type === 'A');
            
            console.log(`Groups breakdown: Type B: ${typeBGroups.length}, Type A: ${typeAGroups.length}, Total: ${groupsData.length}`);
            
            if (typeAGroups.length > 0) {
                console.error('⚠️ WARNING: Found Type A groups in lesson game groups endpoint!', typeAGroups);
                showToast('Cảnh báo: Phát hiện nhóm Type A trong danh sách nhóm bài học!', 'failed');
            }
            
            if (typeBGroups.length !== groupsData.length) {
                console.warn('Warning: Some groups are not Type B!', groupsData);
            }
            
            renderGroupsTable(groupsData);
            updateGameGroupSelect();
        })
        .catch(error => {
            console.error('Error fetching lesson game groups:', error);
            showToast('Lỗi tải danh sách nhóm trò chơi!', 'failed');
        });
}

function renderGroupsTable(data) {
    const listEl = document.getElementById('groups-list');
    listEl.innerHTML = '';
    
    if (!data || data.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td colspan="3" class="table-cell text-center text-gray-400 py-8">
                <div class="flex flex-col items-center gap-2">
                    <i class="fa-solid fa-layer-group text-3xl mb-2"></i>
                    <p>Chưa có nhóm trò chơi theo bài học (Type B)</p>
                    <p class="text-sm">Hãy tạo nhóm trò chơi mới để bắt đầu</p>
                </div>
            </td>
        `;
        tr.classList.add('table-row');
        listEl.appendChild(tr);
        return;
    }
    
    (data || []).forEach(group => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="table-cell">${group.name} <span class="text-xs text-blue-400">(Type: ${group.group_game_type || 'B'})</span></td>
            <td class="table-cell">${group.description || ''}</td>
            <td class="table-cell">
                <button onclick="editGroup('${group.id}')" class="table-action-edit text-yellow-400 mr-2">Sửa</button>
                <button onclick="deleteGroup('${group.id}')" class="table-action-delete text-red-400">Xoá</button>
            </td>
        `;
        tr.classList.add('table-row');
        listEl.appendChild(tr);
    });
}

function updateGameGroupSelect() {
    const groupSelect = document.getElementById('game-group');
    groupSelect.innerHTML = '<option value="">-- Chọn nhóm --</option>';
    console.log('Updating game group dropdown with groups:', groupsData);
    
    // Filter to ensure only Type B groups
    const typeBGroups = groupsData.filter(g => !g.group_game_type || g.group_game_type === 'B');
    console.log('Filtered Type B groups for dropdown:', typeBGroups);
    
    typeBGroups.forEach(g => {
        const opt = document.createElement('option');
        opt.value = g.id;
        opt.textContent = `${g.name} (Type: ${g.group_game_type || 'B'})`;
        groupSelect.appendChild(opt);
    });
    
    if (typeBGroups.length === 0) {
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = '-- Chưa có nhóm trò chơi Type B --';
        opt.disabled = true;
        groupSelect.appendChild(opt);
    }
}

window.editGroup = function(id) {
    fetch(`${API_GROUPS}/${id}`, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(group => {
            editingGroupId = id;
            document.getElementById('group-modal-title').textContent = 'Sửa nhóm trò chơi';
            document.getElementById('group-id').value = group.id;
            document.getElementById('group-name').value = group.name;
            document.getElementById('group-desc').value = group.description || '';
            document.getElementById('group-modal').classList.remove('hidden');
        });
}

window.deleteGroup = function(id) {
    if (!confirm('Bạn chắc chắn muốn xoá nhóm này?')) return;
    fetch(`${API_GROUPS}/${id}`, { 
        method: 'DELETE', 
        headers: { 'Authorization': token, 'User': JSON.stringify(user), 'X-CSRF-TOKEN': getCsrfToken() } 
    })
    .then(() => {
        showToast('Xoá nhóm thành công!', 'success');
        fetchGroups();
    })
    .catch(() => {
        showToast('Xoá nhóm thất bại!', 'failed');
    });
}

// Games functionality
function fetchGames() {
    fetch(API_GAMES, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(data => {
            gamesData = data || [];
            renderGamesTable(gamesData);
        });
}

function renderGamesTable(data) {
    const listEl = document.getElementById('games-list');
    listEl.innerHTML = '';
    
    if (!data || data.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td colspan="5" class="table-cell text-center text-gray-400 py-8">
                <div class="flex flex-col items-center gap-2">
                    <i class="fa-solid fa-gamepad text-3xl mb-2"></i>
                    <p>Chưa có trò chơi theo bài học (Type B)</p>
                    <p class="text-sm">Hãy tạo nhóm trò chơi trước, sau đó thêm trò chơi</p>
                </div>
            </td>
        `;
        tr.classList.add('table-row');
        listEl.appendChild(tr);
        return;
    }
    
    (data || []).forEach(game => {
        const group = groupsData.find(g => g.id === game.group_id);
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="table-cell">${game.title} <span class="text-xs text-green-400">(Type: ${game.game_type || 'B'})</span></td>
            <td class="table-cell">${game.description || ''}</td>
            <td class="table-cell">${group ? group.name : ''}</td>
            <td class="table-cell"><a href="${game.embed_url}" target="_blank" class="text-blue-400 underline">Link</a></td>
            <td class="table-cell">
                <button onclick="editGame('${game.id}')" class="table-action-edit text-yellow-400 mr-2">Sửa</button>
                <button onclick="deleteGame('${game.id}')" class="table-action-delete text-red-400">Xoá</button>
            </td>
        `;
        tr.classList.add('table-row');
        listEl.appendChild(tr);
    });
}

window.editGame = function(id) {
    fetch(`${API_GAMES}/${id}`, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(game => {
            editingGameId = id;
            document.getElementById('game-modal-title').textContent = 'Sửa trò chơi';
            document.getElementById('game-id').value = game.id;
            document.getElementById('game-title').value = game.title;
            document.getElementById('game-desc').value = game.description || '';
            document.getElementById('game-group').value = game.group_id || '';
            document.getElementById('game-embed').value = game.embed_url || '';
            document.getElementById('game-modal').classList.remove('hidden');
        });
}

window.deleteGame = function(id) {
    if (!confirm('Bạn chắc chắn muốn xoá trò chơi này?')) return;
    fetch(`${API_GAMES}/${id}`, { 
        method: 'DELETE', 
        headers: { 'Authorization': token, 'User': JSON.stringify(user), 'X-CSRF-TOKEN': getCsrfToken() } 
    })
    .then(() => {
        showToast('Xoá trò chơi thành công!', 'success');
        fetchGames();
    })
    .catch(() => {
        showToast('Xoá trò chơi thất bại!', 'failed');
    });
}

// Modal handlers
document.getElementById('btn-add-group').onclick = () => {
    editingGroupId = null;
    document.getElementById('group-modal-title').textContent = 'Thêm nhóm trò chơi';
    document.getElementById('group-form').reset();
    document.getElementById('group-id').value = '';
    document.getElementById('group-modal').classList.remove('hidden');
};

document.getElementById('btn-cancel-group').onclick = () => {
    document.getElementById('group-modal').classList.add('hidden');
};

document.getElementById('btn-add-game').onclick = () => {
    editingGameId = null;
    document.getElementById('game-modal-title').textContent = 'Thêm trò chơi';
    document.getElementById('game-form').reset();
    document.getElementById('game-id').value = '';
    document.getElementById('game-modal').classList.remove('hidden');
};

document.getElementById('btn-cancel-game').onclick = () => {
    document.getElementById('game-modal').classList.add('hidden');
};

// Form submissions
document.getElementById('group-form').onsubmit = function(e) {
    e.preventDefault();
    const data = {
        name: document.getElementById('group-name').value,
        description: document.getElementById('group-desc').value
    };
    let method = editingGroupId ? 'PUT' : 'POST';
    let url = editingGroupId ? `${API_GROUPS}/${editingGroupId}` : API_GROUPS;
    
    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Authorization': token, 'User': JSON.stringify(user), 'X-CSRF-TOKEN': getCsrfToken() },
        body: JSON.stringify(data)
    }).then(() => {
        showToast('Lưu nhóm thành công!', 'success');
        document.getElementById('group-modal').classList.add('hidden');
        fetchGroups();
    }).catch(() => {
        showToast('Lưu nhóm thất bại!', 'failed');
    });
};

document.getElementById('game-form').onsubmit = function(e) {
    e.preventDefault();
    const data = {
        title: document.getElementById('game-title').value,
        description: document.getElementById('game-desc').value,
        group_id: document.getElementById('game-group').value,
        embed_url: document.getElementById('game-embed').value
    };
    let method = editingGameId ? 'PUT' : 'POST';
    let url = editingGameId ? `${API_GAMES}/${editingGameId}` : API_GAMES;
    
    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Authorization': token, 'User': JSON.stringify(user), 'X-CSRF-TOKEN': getCsrfToken() },
        body: JSON.stringify(data)
    }).then(() => {
        showToast('Lưu trò chơi thành công!', 'success');
        document.getElementById('game-modal').classList.add('hidden');
        fetchGames();
    }).catch(() => {
        showToast('Lưu trò chơi thất bại!', 'failed');
    });
};

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-alert');
    toast.textContent = message;
    toast.className = 'toast-alert ' + (type === 'success' ? 'toast-success' : 'toast-failed');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2500);
}

// Debug functionality
document.getElementById('btn-debug-groups').onclick = async () => {
    try {
        console.log('=== DEBUG: Fetching all game groups ===');
        
        // Test debug endpoint
        const debugResponse = await fetch('/debug/game-groups');
        const debugData = await debugResponse.json();
        
        console.log('Debug data:', debugData);
        console.log('Type A groups:', debugData.type_a_groups);
        console.log('Type B groups:', debugData.type_b_groups);
        console.log('All groups (raw):', debugData.all_groups_raw);
        
        // Test direct API calls
        console.log('=== Testing direct API calls ===');
        
        const typeAResponse = await fetch('/supabase/game-groups', { 
            headers: { 'Authorization': token, 'User': JSON.stringify(user) } 
        });
        const typeAData = await typeAResponse.json();
        console.log('Type A API response:', typeAData);
        
        const typeBResponse = await fetch('/supabase/lesson-game-groups', { 
            headers: { 'Authorization': token, 'User': JSON.stringify(user) } 
        });
        const typeBData = await typeBResponse.json();
        console.log('Type B API response:', typeBData);
        
        alert(`Debug completed! Check console.\nType A: ${typeAData?.length || 0} groups\nType B: ${typeBData?.length || 0} groups`);
        
    } catch (error) {
        console.error('Debug error:', error);
        alert('Debug error: ' + error.message);
    }
};

// Initialize
switchTab('groups');

// Add tab styling
document.head.insertAdjacentHTML('beforeend', `
<style>
.tab-button {
    color: #a78bfa;
    background: transparent;
}
.tab-button.active {
    background: linear-gradient(to right, #8b5cf6, #7c3aed);
    color: white;
    box-shadow: 0 4px 6px -1px rgba(139, 92, 246, 0.3);
}
.tab-button:hover:not(.active) {
    background: rgba(139, 92, 246, 0.1);
    color: #c4b5fd;
}
</style>
`);
</script>
</body>
</html>