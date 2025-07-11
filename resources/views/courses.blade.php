<!DOCTYPE html>
<html lang="vi">
@section('title', 'Quản lý Khoá học | Admin Panel')
@include('components.head')
<body class="bg-[#232946] text-gray-100 min-h-screen">
<div id="toast-alert" class="toast-alert"></div>
<div class="flex">
    <!-- Sidebar -->
    @include('components.sidebar')
    <div class="flex-1 flex flex-col min-h-screen">
        <x-header title="Quản lý khoá học" />
        <main class="flex-1 flex flex-col items-center justify-start py-8 px-2">
            <div class="w-full h-full">
                <section class="h-full flex flex-col justify-start">
                    <div class="table-card w-full h-full flex flex-col">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 w-full mb-4">
                            <button id="btn-add" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-6 py-2 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Thêm khoá học
                            </button>
                        </div>
                        <div class="overflow-x-auto w-full">
                            <table class="table-admin">
                                <thead class="table-header">
                                    <tr>
                                        <th class="table-cell">ID</th>
                                        <th class="table-cell">Tên khoá học</th>
                                        <th class="table-cell">Mô tả</th>
                                        <th class="table-cell">Level</th>
                                        <th class="table-cell">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="courses-list"></tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
            <!-- Modal thêm/sửa khoá học -->
            <div id="modal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden">
                <div class="bg-[#232946] p-6 rounded-xl w-full max-w-md">
                    <h2 id="modal-title" class="text-xl font-bold mb-4">Thêm khoá học</h2>
                    <form id="course-form" class="space-y-3">
                        <div>
                            <label class="block mb-1">ID khoá học</label>
                            <input id="course-id" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white" required>
                            <span id="course-id-error" class="text-red-400 text-sm hidden">ID đã tồn tại, vui lòng chọn ID khác.</span>
                        </div>
                        <div>
                            <label class="block mb-1">Tên khoá học</label>
                            <input id="course-title" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white" required>
                        </div>
                        <div>
                            <label class="block mb-1">Mô tả</label>
                            <textarea id="course-desc" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white"></textarea>
                        </div>
                        <div>
                            <label class="block mb-1">Level</label>
                            <select id="course-level" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
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

const API = '/supabase/courses';
const listEl = document.getElementById('courses-list');
const modal = document.getElementById('modal');
const form = document.getElementById('course-form');
const btnAdd = document.getElementById('btn-add');
const btnCancel = document.getElementById('btn-cancel');
const modalTitle = document.getElementById('modal-title');

let editingId = null;

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function fetchCourses() {
    fetch(API, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(data => {
            listEl.innerHTML = '';
            (data || []).forEach(course => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="table-cell">${course.id}</td>
                    <td class="table-cell">${course.title}</td>
                    <td class="table-cell">${course.description || ''}</td>
                    <td class="table-cell">${course.level || ''}</td>
                    <td class="table-cell">
                        <button onclick="editCourse('${course.id}')" class="table-action-edit text-yellow-400 mr-2">Sửa</button>
                        <button onclick="deleteCourse('${course.id}')" class="table-action-delete text-red-400">Xoá</button>
                    </td>
                `;
                tr.classList.add('table-row');
                listEl.appendChild(tr);
            });
        });
}

window.editCourse = function(id) {
    fetch(`${API}/${id}`, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(course => {
            editingId = id;
            modalTitle.textContent = 'Sửa khoá học';
            document.getElementById('course-id').value = course.id;
            document.getElementById('course-id').disabled = true;
            document.getElementById('course-id-error').classList.add('hidden');
            document.getElementById('course-title').value = course.title;
            document.getElementById('course-desc').value = course.description || '';
            document.getElementById('course-level').value = course.level || 'beginner';
            modal.classList.remove('hidden');
        });
}

window.deleteCourse = function(id) {
    if (!confirm('Bạn chắc chắn muốn xoá?')) return;
    fetch(`${API}/${id}`, { method: 'DELETE', headers: { 'Authorization': token, 'User': JSON.stringify(user), 'X-CSRF-TOKEN': getCsrfToken() } })
        .then(() => {
            showToast('Thành công!', 'success');
            fetchCourses();
        })
        .catch(() => {
            showToast('Thao tác thất bại!', 'failed');
        });
}

btnAdd.onclick = () => {
    editingId = null;
    modalTitle.textContent = 'Thêm khoá học';
    form.reset();
    document.getElementById('course-id').disabled = false;
    document.getElementById('course-id-error').classList.add('hidden');
    modal.classList.remove('hidden');
};

btnCancel.onclick = () => {
    modal.classList.add('hidden');
};

document.getElementById('course-id').onblur = function() {
    const id = this.value.trim();
    if (!id) return;
    if (editingId) return; // Đang sửa thì không check
    fetch(`${API}/${id}`, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(course => {
            if (course && course.id) {
                document.getElementById('course-id-error').classList.remove('hidden');
            } else {
                document.getElementById('course-id-error').classList.add('hidden');
            }
        });
};

form.onsubmit = function(e) {
    e.preventDefault();
    if (!editingId && !document.getElementById('course-id').value.trim()) return;
    if (!editingId && !document.getElementById('course-id-error').classList.contains('hidden')) return;
    const data = {
        id: document.getElementById('course-id').value.trim(),
        title: document.getElementById('course-title').value,
        description: document.getElementById('course-desc').value,
        level: document.getElementById('course-level').value
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
        fetchCourses();
    }).catch(() => {
        showToast('Thao tác thất bại!', 'failed');
    });
};

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-alert');
    toast.textContent = message;
    toast.className = 'toast-alert ' + (type === 'success' ? 'toast-success' : 'toast-failed');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2500);
}

fetchCourses();
</script>
</body>
</html> 