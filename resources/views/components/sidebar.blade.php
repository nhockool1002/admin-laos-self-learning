@php
    // Multi-level menu configuration with children
    $menuItems = [
        [
            'id' => 'home',
            'title' => 'Trang chủ',
            'icon' => 'fa-solid fa-house',
            'url' => '/',
            'active' => request()->is('/'),
        ],
        [
            'id' => 'users',
            'title' => 'Quản lý Users',
            'icon' => 'fa-solid fa-user',
            'url' => '/admin/users',
            'active' => request()->is('admin/users'),
        ],
        [
            'id' => 'courses',
            'title' => 'Quản lý Khoá học',
            'icon' => 'fa-solid fa-book',
            'url' => '#',
            'active' => request()->is('admin/courses*') || request()->is('admin/lessons*'),
            'children' => [
                [
                    'title' => 'Quản lý khoá học',
                    'icon' => 'fa-solid fa-book-open',
                    'url' => '/admin/courses',
                    'active' => request()->is('admin/courses'),
                ],
                [
                    'title' => 'Quản lý nội dung',
                    'icon' => 'fa-solid fa-pen-to-square',
                    'url' => '/admin/lessons',
                    'active' => request()->is('admin/lessons'),
                ],
                [
                    'title' => 'Thống kê học tập',
                    'icon' => 'fa-solid fa-chart-line',
                    'url' => '/admin/course-stats',
                    'active' => request()->is('admin/course-stats'),
                ],
            ]
        ],
        [
            'id' => 'games',
            'title' => 'Quản lý Trò chơi',
            'icon' => 'fa-solid fa-gamepad',
            'url' => '#',
            'active' => request()->is('admin/games*') || request()->is('admin/game-groups*'),
            'children' => [
                [
                    'title' => 'Quản lý nhóm trò chơi',
                    'icon' => 'fa-solid fa-layer-group',
                    'url' => '/admin/game-groups',
                    'active' => request()->is('admin/game-groups'),
                ],
                [
                    'title' => 'Quản lý trò chơi',
                    'icon' => 'fa-solid fa-puzzle-piece',
                    'url' => '/admin/games',
                    'active' => request()->is('admin/games'),
                ],
                [
                    'title' => 'Cấu hình trò chơi',
                    'icon' => 'fa-solid fa-gear',
                    'url' => '/admin/game-settings',
                    'active' => request()->is('admin/game-settings'),
                ],
            ]
        ],
        [
            'id' => 'badges',
            'title' => 'Quản lý Huy hiệu',
            'icon' => 'fa-solid fa-award',
            'url' => '/admin/badges',
            'active' => request()->is('admin/badges'),
        ],
        [
            'id' => 'alphabet',
            'title' => 'Bảng chữ cái',
            'icon' => 'fa-solid fa-font',
            'url' => '#',
            'active' => request()->is('admin/alphabet*'),
            'children' => [
                [
                    'title' => 'Quản lý chữ cái',
                    'icon' => 'fa-solid fa-spell-check',
                    'url' => '/admin/alphabet',
                    'active' => request()->is('admin/alphabet'),
                ],
                [
                    'title' => 'Phát âm',
                    'icon' => 'fa-solid fa-volume-high',
                    'url' => '/admin/alphabet/pronunciation',
                    'active' => request()->is('admin/alphabet/pronunciation'),
                ],
            ]
        ],
        [
            'id' => 'practice',
            'title' => 'Luyện tập',
            'icon' => 'fa-solid fa-bullseye',
            'url' => '#',
            'active' => request()->is('admin/practice*'),
            'children' => [
                [
                    'title' => 'Bài luyện tập',
                    'icon' => 'fa-solid fa-dumbbell',
                    'url' => '/admin/practice',
                    'active' => request()->is('admin/practice'),
                ],
                [
                    'title' => 'Kết quả luyện tập',
                    'icon' => 'fa-solid fa-chart-bar',
                    'url' => '/admin/practice/results',
                    'active' => request()->is('admin/practice/results'),
                ],
            ]
        ],
        [
            'id' => 'test',
            'title' => 'Kiểm tra',
            'icon' => 'fa-solid fa-clipboard-check',
            'url' => '#',
            'active' => request()->is('admin/test*'),
            'children' => [
                [
                    'title' => 'Đề kiểm tra',
                    'icon' => 'fa-solid fa-file-circle-question',
                    'url' => '/admin/test',
                    'active' => request()->is('admin/test'),
                ],
                [
                    'title' => 'Kết quả kiểm tra',
                    'icon' => 'fa-solid fa-chart-simple',
                    'url' => '/admin/test/results',
                    'active' => request()->is('admin/test/results'),
                ],
                [
                    'title' => 'Báo cáo chi tiết',
                    'icon' => 'fa-solid fa-file-contract',
                    'url' => '/admin/test/reports',
                    'active' => request()->is('admin/test/reports'),
                ],
            ]
        ],
    ];
@endphp

<!-- Sidebar Container -->
<aside id="sidebar" class="fixed top-0 left-0 h-screen w-72 bg-gradient-to-b from-[#232946] to-[#1e1f37] flex flex-col transition-all duration-300 ease-in-out shadow-2xl border-r border-[#2d3250]/60 z-50 lg:relative lg:translate-x-0 transform -translate-x-full">
    
    <!-- Sidebar Header -->
    <div class="flex flex-col items-center gap-3 px-6 py-8 border-b border-[#2d3250]/40">
        <div class="relative">
            <img src="/assets/imgs/laos.png" alt="Logo" class="w-16 h-16 object-contain drop-shadow-lg transition-transform duration-300 hover:scale-110">
            <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-crown text-xs text-white"></i>
            </div>
        </div>
        <div class="text-center">
            <h1 class="text-xl font-bold text-purple-200 tracking-wide">Tiếng Lào</h1>
            <p class="text-sm text-purple-300/70 mt-1">Admin Panel</p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 flex flex-col gap-1 px-4 py-4 overflow-y-auto scrollbar-thin scrollbar-track-transparent scrollbar-thumb-purple-500/30">
        @foreach($menuItems as $item)
            @if(isset($item['children']) && count($item['children']) > 0)
                <!-- Parent Menu Item with Children -->
                <div class="menu-group" data-menu-id="{{ $item['id'] }}">
                    <button type="button" 
                            class="menu-toggle w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200 group text-sm relative overflow-hidden {{ $item['active'] ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-lg active-menu' : 'text-purple-100 hover:bg-purple-700/30 hover:shadow-md' }}"
                            data-submenu="{{ $item['id'] }}-submenu">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-400/20 to-pink-400/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                        <i class="{{ $item['icon'] }} text-lg relative z-10"></i>
                        <span class="relative z-10 flex-1 text-left">{{ $item['title'] }}</span>
                        <i class="fa-solid fa-chevron-down text-sm relative z-10 transition-transform duration-300 chevron-icon"></i>
                    </button>
                    
                    <!-- Submenu -->
                    <div id="{{ $item['id'] }}-submenu" class="submenu pl-6 mt-1 space-y-1 {{ $item['active'] ? '' : 'hidden' }}">
                        @foreach($item['children'] as $child)
                            <a href="{{ $child['url'] }}" 
                               class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all duration-200 group relative overflow-hidden {{ $child['active'] ? 'bg-purple-600/80 text-white shadow-md active-submenu' : 'text-purple-200 hover:bg-purple-600/40 hover:text-white' }}">
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-300/10 to-pink-300/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                <i class="{{ $child['icon'] }} text-base relative z-10"></i>
                                <span class="relative z-10">{{ $child['title'] }}</span>
                                @if($child['active'])
                                    <div class="absolute right-2 top-1/2 transform -translate-y-1/2 w-2 h-2 bg-pink-400 rounded-full"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Single Menu Item -->
                <a href="{{ $item['url'] }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200 group text-sm relative overflow-hidden {{ $item['active'] ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-lg active-menu' : 'text-purple-100 hover:bg-purple-700/30 hover:shadow-md' }}">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-400/20 to-pink-400/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                    <i class="{{ $item['icon'] }} text-lg relative z-10"></i>
                    <span class="relative z-10">{{ $item['title'] }}</span>
                    @if($item['active'])
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 w-2 h-2 bg-pink-400 rounded-full"></div>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>

    <!-- Sidebar Footer -->
    <div class="px-4 py-4 border-t border-[#2d3250]/40 mt-auto">
        <button id="logout-menu" 
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200 group text-sm relative overflow-hidden text-purple-100 hover:bg-red-500/30 hover:text-red-200">
            <div class="absolute inset-0 bg-gradient-to-r from-red-400/20 to-pink-400/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            <i class="fa-solid fa-arrow-right-from-bracket text-lg relative z-10"></i>
            <span class="relative z-10">Đăng xuất</span>
        </button>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden transition-opacity duration-300"></div>

<!-- Mobile Sidebar Toggle Button -->
<button id="sidebar-toggle" 
        class="fixed top-4 left-4 z-50 lg:hidden bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
    <i class="fa-solid fa-bars text-lg"></i>
</button>

<!-- Custom Styles for Sidebar -->
<style>
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(147, 51, 234, 0.3);
    border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(147, 51, 234, 0.5);
}

/* Smooth animations for menu items */
.menu-toggle, nav a {
    transform: translateX(0);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.menu-toggle:hover, nav a:hover {
    transform: translateX(4px);
}

/* Active states with enhanced visuals */
.active-menu {
    box-shadow: 0 4px 15px rgba(147, 51, 234, 0.4);
    border: 1px solid rgba(147, 51, 234, 0.3);
}

.active-submenu {
    box-shadow: 0 2px 10px rgba(147, 51, 234, 0.3);
    border-left: 3px solid #ec4899;
}

/* Responsive design */
@media (max-width: 1023px) {
    #sidebar {
        width: 280px;
    }
}

/* Submenu animation */
.submenu {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.submenu.show {
    max-height: 500px;
}

/* Chevron rotation */
.chevron-rotated {
    transform: rotate(180deg);
}
</style>

<!-- jQuery CDN and Sidebar Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize sidebar functionality
    initializeSidebar();
    
    function initializeSidebar() {
        // Handle submenu toggles with smooth animations
        $('.menu-toggle').on('click', function() {
            const $button = $(this);
            const submenuId = $button.data('submenu');
            const $submenu = $('#' + submenuId);
            const $chevron = $button.find('.chevron-icon');
            
            // Close other open submenus
            $('.submenu').not($submenu).removeClass('show').slideUp(300);
            $('.chevron-icon').not($chevron).removeClass('chevron-rotated');
            
            // Toggle current submenu
            if ($submenu.hasClass('show')) {
                $submenu.removeClass('show').slideUp(300);
                $chevron.removeClass('chevron-rotated');
            } else {
                $submenu.addClass('show').slideDown(300);
                $chevron.addClass('chevron-rotated');
            }
        });
        
        // Mobile sidebar toggle
        $('#sidebar-toggle').on('click', function() {
            toggleMobileSidebar();
        });
        
        // Close sidebar when clicking overlay
        $('#sidebar-overlay').on('click', function() {
            closeMobileSidebar();
        });
        
        // Close sidebar on mobile when clicking a menu item
        $('#sidebar a[href!="#"]').on('click', function() {
            if (window.innerWidth < 1024) {
                closeMobileSidebar();
            }
        });
        
        // Handle window resize
        $(window).on('resize', function() {
            if (window.innerWidth >= 1024) {
                closeMobileSidebar();
            }
        });
        
        // Initialize active submenu states
        $('.active-menu').each(function() {
            const $button = $(this);
            if ($button.hasClass('menu-toggle')) {
                const submenuId = $button.data('submenu');
                const $submenu = $('#' + submenuId);
                const $chevron = $button.find('.chevron-icon');
                
                $submenu.addClass('show').show();
                $chevron.addClass('chevron-rotated');
            }
        });
        
        // Add hover effects
        addHoverEffects();
    }
    
    function toggleMobileSidebar() {
        const $sidebar = $('#sidebar');
        const $overlay = $('#sidebar-overlay');
        
        if ($sidebar.hasClass('-translate-x-full')) {
            // Show sidebar
            $sidebar.removeClass('-translate-x-full');
            $overlay.removeClass('hidden').fadeIn(300);
        } else {
            // Hide sidebar
            $sidebar.addClass('-translate-x-full');
            $overlay.fadeOut(300, function() {
                $(this).addClass('hidden');
            });
        }
    }
    
    function closeMobileSidebar() {
        const $sidebar = $('#sidebar');
        const $overlay = $('#sidebar-overlay');
        
        $sidebar.addClass('-translate-x-full');
        $overlay.fadeOut(300, function() {
            $(this).addClass('hidden');
        });
    }
    
    function addHoverEffects() {
        // Add ripple effect on menu items
        $('.menu-toggle, nav a').on('mouseenter', function() {
            $(this).addClass('animate-pulse');
        }).on('mouseleave', function() {
            $(this).removeClass('animate-pulse');
        });
        
        // Add smooth scroll behavior for long menus
        $('#sidebar nav').on('wheel', function(e) {
            const delta = e.originalEvent.deltaY;
            this.scrollTop += delta;
            e.preventDefault();
        });
    }
    
    // Enhanced menu highlighting based on current URL
    function updateActiveStates() {
        const currentPath = window.location.pathname;
        
        // Remove all active states
        $('.active-menu, .active-submenu').removeClass('active-menu active-submenu');
        
        // Add active state to matching menu items
        $('nav a').each(function() {
            const $link = $(this);
            const href = $link.attr('href');
            
            if (href === currentPath || (href !== '#' && currentPath.startsWith(href))) {
                $link.addClass('active-submenu');
                
                // If it's in a submenu, also mark parent as active
                const $parentGroup = $link.closest('.menu-group');
                if ($parentGroup.length) {
                    $parentGroup.find('.menu-toggle').addClass('active-menu');
                }
            }
        });
    }
    
    // Update active states on navigation
    updateActiveStates();
});
</script> 