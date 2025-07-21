@php
    $isCourseSub = request()->is('admin/courses') || request()->is('admin/lessons');
    $isGameSub = request()->is('admin/games') || request()->is('admin/game-groups');
    $isBadgesSub = request()->is('admin/badges');
@endphp
<aside class="h-screen w-60 shrink-0 bg-[#232946] flex flex-col transition-all duration-300 shadow-2xl rounded-r-3xl border-r border-[#2d3250]/60">
    <div class="flex flex-col items-center gap-2 px-0 py-8">
        <img src="/assets/imgs/laos.png" alt="Logo" class="w-14 h-14 object-contain mb-1 drop-shadow-lg">
        <span class="text-xl font-bold text-purple-200 tracking-wide">Tiếng Lào</span>
    </div>
    <nav class="flex-1 flex flex-col gap-2 px-3">
        <a href="/" class="flex items-center gap-3 px-3 py-2 rounded-2xl font-medium transition-all duration-200 group text-[0.98rem] {{ request()->is('/') ? 'bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-lg' : 'text-purple-100 hover:bg-purple-700/30 hover:shadow' }}">
            <i class="fa-solid fa-house text-lg"></i>
            <span>Trang chủ</span>
        </a>
        <a href="/admin/users" class="flex items-center gap-3 px-3 py-2 rounded-2xl font-medium transition-all duration-200 group text-[0.98rem] {{ request()->is('admin/users') ? 'bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-lg' : 'text-purple-100 hover:bg-purple-700/30 hover:shadow' }}">
            <i class="fa-solid fa-user text-lg"></i>
            <span>Quản lý Users</span>
        </a>
        <div 
            x-data="{
                open: {{ $isCourseSub ? 'true' : 'false' }},
                isCourseSub: {{ $isCourseSub ? 'true' : 'false' }},
                toggle() { this.open = !this.open; }
            }" 
            x-effect="if (isCourseSub) open = true"
            class="relative"
        >
            <button type="button"
                @click.prevent="toggle()"
                :class="open ? 'bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-lg' : 'text-purple-100 hover:bg-purple-700/30 hover:shadow'"
                class="flex items-center w-full gap-3 px-3 py-2 rounded-2xl font-medium transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-purple-400 text-[0.98rem] {{ $isCourseSub ? 'bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-lg' : '' }}">
                <i class="fa-solid fa-book text-lg"></i>
                <span>Quản lý Khoá học</span>
                <i :class="open ? 'rotate-90' : ''" class="fa-solid fa-chevron-right w-4 h-4 ml-auto transition-transform duration-300"></i>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="pl-8 flex flex-col gap-1 mt-1">
                <a href="/admin/courses" class="flex items-center gap-2 px-2 py-2 rounded-xl font-medium text-sm transition-all duration-200 text-[0.97rem] {{ request()->is('admin/courses') ? 'bg-purple-700/80 text-white shadow' : 'text-purple-100 hover:bg-purple-700/40' }}">
                    <i class="fa-solid fa-book-open text-base"></i>
                    <span>Quản lý khoá học</span>
                </a>
                <a href="/admin/lessons" class="flex items-center gap-2 px-2 py-2 rounded-xl font-medium text-sm transition-all duration-200 text-[0.97rem] {{ request()->is('admin/lessons') ? 'bg-purple-700/80 text-white shadow' : 'text-purple-100 hover:bg-purple-700/40' }}">
                    <i class="fa-solid fa-pen-to-square text-base"></i>
                    <span>Quản lý nội dung</span>
                </a>
            </div>
        </div>
        <!-- Menu Quản lý Trò chơi -->
        <div 
            x-data="{
                open: {{ $isGameSub ? 'true' : 'false' }},
                isGameSub: {{ $isGameSub ? 'true' : 'false' }},
                toggle() { this.open = !this.open; }
            }" 
            x-effect="if (isGameSub) open = true"
            class="relative"
        >
            <button type="button"
                @click.prevent="toggle()"
                :class="open ? 'bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-lg' : 'text-purple-100 hover:bg-purple-700/30 hover:shadow'"
                class="flex items-center w-full gap-3 px-3 py-2 rounded-2xl font-medium transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-purple-400 text-[0.98rem] {{ $isGameSub ? 'bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-lg' : '' }}">
                <i class="fa-solid fa-gamepad text-lg"></i>
                <span>Quản lý Trò chơi</span>
                <i :class="open ? 'rotate-90' : ''" class="fa-solid fa-chevron-right w-4 h-4 ml-auto transition-transform duration-300"></i>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="pl-8 flex flex-col gap-1 mt-1">
                <a href="/admin/game-groups" class="flex items-center gap-2 px-2 py-2 rounded-xl font-medium text-sm transition-all duration-200 text-[0.97rem] {{ request()->is('admin/game-groups') ? 'bg-purple-700/80 text-white shadow' : 'text-purple-100 hover:bg-purple-700/40' }}">
                    <i class="fa-solid fa-layer-group text-base"></i>
                    <span>Quản lý nhóm trò chơi</span>
                </a>
                <a href="/admin/games" class="flex items-center gap-2 px-2 py-2 rounded-xl font-medium text-sm transition-all duration-200 text-[0.97rem] {{ request()->is('admin/games') ? 'bg-purple-700/80 text-white shadow' : 'text-purple-100 hover:bg-purple-700/40' }}">
                    <i class="fa-solid fa-puzzle-piece text-base"></i>
                    <span>Quản lý trò chơi</span>
                </a>
            </div>
        </div>
        <a href="/admin/badges" class="flex items-center gap-3 px-3 py-2 rounded-2xl font-medium transition-all duration-200 group text-[0.98rem] {{ request()->is('admin/badges') ? 'bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-lg' : 'text-purple-100 hover:bg-purple-700/30 hover:shadow' }}">
            <i class="fa-solid fa-award text-lg"></i>
            <span>Quản lý Huy hiệu</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-2xl font-medium transition-all duration-200 group text-[0.98rem] text-purple-100 hover:bg-purple-700/30 hover:shadow">
            <i class="fa-solid fa-font text-lg"></i>
            <span>Quản lý Bảng chữ cái</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-2xl font-medium transition-all duration-200 group text-[0.98rem] text-purple-100 hover:bg-purple-700/30 hover:shadow">
            <i class="fa-solid fa-bullseye text-lg"></i>
            <span>Luyện tập</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-2xl font-medium transition-all duration-200 group text-[0.98rem] text-purple-100 hover:bg-purple-700/30 hover:shadow">
            <i class="fa-solid fa-clipboard-check text-lg"></i>
            <span>Kiểm tra</span>
        </a>
        <a href="#" id="logout-menu" class="flex items-center gap-3 px-3 py-2 rounded-2xl font-medium transition-all duration-200 group text-[0.98rem] text-purple-100 hover:bg-purple-700/30 hover:shadow">
            <i class="fa-solid fa-arrow-right-from-bracket text-lg"></i>
            <span>Đăng xuất</span>
        </a>
    </nav>
</aside> 