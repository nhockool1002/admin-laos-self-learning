<!DOCTYPE html>
<html lang="vi">
@section('title', 'Quản lý Nhóm trò chơi | Admin Panel')
@include('components.head')
<body class="bg-[#232946] text-gray-100 min-h-screen">
<div id="toast-alert" class="toast-alert"></div>
<div class="flex">
    <!-- Sidebar -->
    @include('components.sidebar')
    <div class="flex-1 flex flex-col min-h-screen">
        <x-header title="Quản lý nhóm trò chơi">
            <x-slot name="right">
                <button id="logout-btn" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-8 py-3 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition text-lg">Đăng xuất</button>
            </x-slot>
        </x-header>
        <main class="flex-1 flex flex-col items-center justify-start py-8 px-2">
            <div class="w-full h-full">
                <div class="table-card w-full h-full flex flex-col">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 w-full mb-4">
                        <button id="btn-add" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-6 py-2 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Thêm nhóm trò chơi
                        </button>
                        <input id="search-input" type="text" placeholder="Tìm kiếm..." class="ml-auto px-3 py-2 rounded bg-[#2d3250] text-white w-60 focus:outline-none focus:ring-2 focus:ring-purple-400" />
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
            <!-- Modal thêm/sửa nhóm -->
            <div id="modal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden">
                <div class="bg-[#232946] p-6 rounded-xl w-full max-w-md">
                    <h2 id="modal-title" class="text-xl font-bold mb-4">Thêm nhóm trò chơi</h2>
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
                            <button type="button" id="btn-cancel" class="px-4 py-2 rounded bg-gray-500 hover:bg-gray-600">Huỷ</button>
                            <button type="submit" class="px-4 py-2 rounded bg-purple-600 hover:bg-purple-700 text-white">Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
const token = sessionStorage.getItem('access_token');
const user = JSON.parse(sessionStorage.getItem('user') || '{}');
if (!token || !user.is_admin) window.location.href = '/login';

const API = '/supabase/game-groups';
const listEl = document.getElementById('groups-list');
const modal = document.getElementById('modal');
const form = document.getElementById('group-form');
const btnAdd = document.getElementById('btn-add');
const btnCancel = document.getElementById('btn-cancel');
const modalTitle = document.getElementById('modal-title');

let editingId = null;
let groupsData = [];
let currentSort = { key: '', asc: true };

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function renderGroupsTable(data) {
    const listEl = document.getElementById('groups-list');
    listEl.innerHTML = '';
    (data || []).forEach(group => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="table-cell">${group.name}</td>
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

function fetchGroups() {
    fetch(API, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(data => {
            groupsData = data || [];
            applySearchAndSort();
        });
}

function applySearchAndSort() {
    let filtered = groupsData;
    const search = document.getElementById('search-input').value.trim().toLowerCase();
    if (search) {
        filtered = filtered.filter(g =>
            (g.name && g.name.toLowerCase().includes(search)) ||
            (g.description && g.description.toLowerCase().includes(search))
        );
    }
    if (currentSort.key) {
        filtered = filtered.slice().sort((a, b) => {
            let v1 = a[currentSort.key] || '';
            let v2 = b[currentSort.key] || '';
            v1 = v1.toLowerCase();
            v2 = v2.toLowerCase();
            if (v1 < v2) return currentSort.asc ? -1 : 1;
            if (v1 > v2) return currentSort.asc ? 1 : -1;
            return 0;
        });
    }
    renderGroupsTable(filtered);
}

window.editGroup = function(id) {
    fetch(`${API}/${id}`, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(group => {
            editingId = id;
            modalTitle.textContent = 'Sửa nhóm trò chơi';
            document.getElementById('group-id').value = group.id;
            document.getElementById('group-name').value = group.name;
            document.getElementById('group-desc').value = group.description || '';
            modal.classList.remove('hidden');
        });
}

window.deleteGroup = function(id) {
    if (!confirm('Bạn chắc chắn muốn xoá?')) return;
    fetch(`${API}/${id}`, { method: 'DELETE', headers: { 'Authorization': token, 'User': JSON.stringify(user), 'X-CSRF-TOKEN': getCsrfToken() } })
        .then(() => {
            showToast('Thành công!', 'success');
            fetchGroups();
        })
        .catch(() => {
            showToast('Thao tác thất bại!', 'failed');
        });
}

btnAdd.onclick = () => {
    editingId = null;
    modalTitle.textContent = 'Thêm nhóm trò chơi';
    form.reset();
    document.getElementById('group-id').value = '';
    modal.classList.remove('hidden');
};

btnCancel.onclick = () => {
    modal.classList.add('hidden');
};

document.getElementById('group-id').onblur = function() {
    const id = this.value.trim();
    if (!id) return;
    if (editingId) return; // Đang sửa thì không check
    fetch(`${API}/${id}`, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(group => {
            if (group && group.id) {
                document.getElementById('group-id-error').classList.remove('hidden');
            } else {
                document.getElementById('group-id-error').classList.add('hidden');
            }
        });
};

form.onsubmit = function(e) {
    e.preventDefault();
    const data = {
        name: document.getElementById('group-name').value,
        description: document.getElementById('group-desc').value
    };
    let method = editingId ? 'PUT' : 'POST';
    let url = editingId ? `${API}/${editingId}` : API;
    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Authorization': token, 'User': JSON.stringify(user), 'X-CSRF-TOKEN': getCsrfToken() },
        body: JSON.stringify(data)
    }).then(() => {
        showToast('Thành công!', 'success');
        modal.classList.add('hidden');
        fetchGroups();
    }).catch(() => {
        showToast('Thao tác thất bại!', 'failed');
    });
};

document.getElementById('search-input').oninput = applySearchAndSort;

document.querySelectorAll('.table-header th[data-sort]').forEach(th => {
    th.onclick = function() {
        const key = th.getAttribute('data-sort');
        if (currentSort.key === key) {
            currentSort.asc = !currentSort.asc;
        } else {
            currentSort.key = key;
            currentSort.asc = true;
        }
        applySearchAndSort();
        // Update sort icon
        document.querySelectorAll('.sort-icon').forEach(icon => icon.textContent = '');
        th.querySelector('.sort-icon').textContent = currentSort.asc ? '▲' : '▼';
    };
});

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-alert');
    toast.textContent = message;
    toast.className = 'toast-alert ' + (type === 'success' ? 'toast-success' : 'toast-failed');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2500);
}

fetchGroups();
// Logout được xử lý bởi file logout.js
</script>
</body>
</html> 