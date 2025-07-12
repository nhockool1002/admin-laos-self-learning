<!DOCTYPE html>
<html lang="vi">
@section('title', 'Quản lý Nội dung | Admin Panel')
@include('components.head')
<body class="bg-[#232946] text-gray-100 min-h-screen">
<div id="toast-alert" class="toast-alert"></div>
<div class="flex">
    <!-- Sidebar -->
    @include('components.sidebar')
    <div class="flex-1 flex flex-col min-h-screen">
        <x-header title="Quản lý nội dung">
            <x-slot name="right">
                <button id="logout-btn" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-8 py-3 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition text-lg">Đăng xuất</button>
            </x-slot>
        </x-header>
        <main class="flex-1 flex flex-col items-center justify-start py-8 px-2">
            <div class="w-full h-full">
                <section class="h-full flex flex-col justify-start">
                    <div class="table-card w-full h-full flex flex-col">
                        <div class="mb-4 flex gap-2 items-center">
                            <label>Chọn khoá học:</label>
                            <select id="course-select" class="bg-[#2d3250] text-white px-3 py-2 rounded"></select>
                            <button id="btn-add" class="bg-gradient-to-r from-purple-400 to-pink-400 text-[#232946] font-bold px-6 py-2 rounded-xl shadow hover:from-pink-400 hover:to-purple-400 transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Thêm bài học
                            </button>
                        </div>
                        <div class="overflow-x-auto w-full">
                            <table class="table-admin">
                                <thead class="table-header">
                                    <tr>
                                        <th class="table-cell">Tiêu đề</th>
                                        <th class="table-cell">Mô tả</th>
                                        <th class="table-cell">Thời lượng</th>
                                        <th class="table-cell">Youtube URL</th>
                                        <th class="table-cell">Thứ tự</th>
                                        <th class="table-cell">Ngày tạo</th>
                                        <th class="table-cell">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="lessons-list"></tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
            <!-- Modal thêm/sửa bài học -->
            <div id="modal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden">
                <div class="bg-[#232946] p-6 rounded-xl w-full max-w-md">
                    <h2 id="modal-title" class="text-xl font-bold mb-4">Thêm bài học</h2>
                    <form id="lesson-form" class="space-y-3">
                        <input type="hidden" id="lesson-id">
                        <div>
                            <label class="block mb-1">Tiêu đề</label>
                            <input id="lesson-title" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white" required>
                        </div>
                        <div>
                            <label class="block mb-1">Mô tả</label>
                            <textarea id="lesson-desc" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white"></textarea>
                        </div>
                        <div>
                            <label class="block mb-1">Thời lượng (phút)</label>
                            <input id="lesson-duration" type="number" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white">
                        </div>
                        <div>
                            <label class="block mb-1">Youtube URL</label>
                            <input id="lesson-youtube" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white">
                        </div>
                        <div>
                            <label class="block mb-1">Thứ tự hiển thị</label>
                            <input id="lesson-order" type="number" class="w-full px-3 py-2 rounded bg-[#2d3250] text-white">
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

const API_COURSES = '/supabase/courses';
const API_LESSONS = '/supabase/courses';
const courseSelect = document.getElementById('course-select');
const listEl = document.getElementById('lessons-list');
const modal = document.getElementById('modal');
const form = document.getElementById('lesson-form');
const btnAdd = document.getElementById('btn-add');
const btnCancel = document.getElementById('btn-cancel');
const modalTitle = document.getElementById('modal-title');

let editingId = null;
let currentCourse = null;

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function fetchCourses() {
    fetch(API_COURSES, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(data => {
            courseSelect.innerHTML = '';
            (data || []).forEach(course => {
                const opt = document.createElement('option');
                opt.value = course.id;
                opt.textContent = course.title;
                courseSelect.appendChild(opt);
            });
            if (data && data.length > 0) {
                currentCourse = data[0].id;
                courseSelect.value = currentCourse;
                fetchLessons();
            }
        });
}

function fetchLessons() {
    if (!currentCourse) return;
    fetch(`${API_LESSONS}/${currentCourse}/lessons`, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(data => {
            listEl.innerHTML = '';
            (data || []).forEach(lesson => {
                const createdAt = lesson.created_at ? formatDateTime(lesson.created_at) : '';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="table-cell">${lesson.title}</td>
                    <td class="table-cell">${lesson.description || ''}</td>
                    <td class="table-cell">${lesson.duration || ''}</td>
                    <td class="table-cell">${lesson.youtube_url || ''}</td>
                    <td class="table-cell">${lesson.order_index || ''}</td>
                    <td class="table-cell">${createdAt}</td>
                    <td class="table-cell">
                        <button onclick="editLesson('${lesson.id}')" class="table-action-edit text-yellow-400 mr-2">Sửa</button>
                        <button onclick="deleteLesson('${lesson.id}')" class="table-action-delete text-red-400">Xoá</button>
                    </td>
                `;
                tr.className = 'table-row';
                listEl.appendChild(tr);
            });
        });
}

courseSelect.onchange = function() {
    currentCourse = courseSelect.value;
    fetchLessons();
};

window.editLesson = function(id) {
    fetch(`/supabase/lessons/${id}`, { headers: { 'Authorization': token, 'User': JSON.stringify(user) } })
        .then(res => res.json())
        .then(lesson => {
            editingId = id;
            modalTitle.textContent = 'Sửa bài học';
            document.getElementById('lesson-id').value = lesson.id;
            document.getElementById('lesson-title').value = lesson.title;
            document.getElementById('lesson-desc').value = lesson.description || '';
            document.getElementById('lesson-duration').value = lesson.duration || '';
            document.getElementById('lesson-youtube').value = lesson.youtube_url || '';
            document.getElementById('lesson-order').value = lesson.order_index || '';
            modal.classList.remove('hidden');
        });
}

window.deleteLesson = function(id) {
    if (!confirm('Bạn chắc chắn muốn xoá?')) return;
    fetch(`/supabase/lessons/${id}`, { method: 'DELETE', headers: { 'Authorization': token, 'User': JSON.stringify(user), 'X-CSRF-TOKEN': getCsrfToken() } })
        .then(() => {
            showToast('Bài học đã được xoá thành công!');
            fetchLessons();
        })
        .catch(() => {
            showToast('Có lỗi xảy ra khi xoá bài học. Vui lòng thử lại sau.', 'failed');
        });
}

btnAdd.onclick = () => {
    if (!currentCourse) return;
    editingId = null;
    modalTitle.textContent = 'Thêm bài học';
    form.reset();
    modal.classList.remove('hidden');
};

btnCancel.onclick = () => {
    modal.classList.add('hidden');
};

form.onsubmit = function(e) {
    e.preventDefault();
    const data = {
        title: document.getElementById('lesson-title').value,
        description: document.getElementById('lesson-desc').value,
        duration: document.getElementById('lesson-duration').value,
        youtube_url: document.getElementById('lesson-youtube').value,
        order_index: document.getElementById('lesson-order').value
    };
    let method = editingId ? 'PUT' : 'POST';
    let url = editingId ? `/supabase/lessons/${editingId}` : `${API_LESSONS}/${currentCourse}/lessons`;
    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'Authorization': token, 'User': JSON.stringify(user), 'X-CSRF-TOKEN': getCsrfToken() },
        body: JSON.stringify(data)
    }).then(() => {
        showToast('Thành công!');
        modal.classList.add('hidden');
        fetchLessons();
    }).catch(() => {
        showToast('Có lỗi xảy ra khi thực hiện thao tác. Vui lòng thử lại sau.', 'failed');
    });
};

function formatDateTime(dt) {
    const d = new Date(dt);
    const pad = n => n < 10 ? '0' + n : n;
    return `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-alert');
    toast.textContent = message;
    toast.className = 'toast-alert ' + (type === 'success' ? 'toast-success' : 'toast-failed');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2500);
}

fetchCourses();

// Logout được xử lý bởi file logout.js
</script>
</body>
</html> 