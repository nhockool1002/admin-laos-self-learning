@php
    // Multi-level menu configuration with children - Updated per requirements
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
            ]
        ],
        [
            'id' => 'badges',
            'title' => 'Quản lý Huy hiệu',
            'icon' => 'fa-solid fa-award',
            'url' => '/admin/badges',
            'active' => request()->is('admin/badges'),
        ],
    ];
@endphp

<!-- Sidebar Container -->
<aside id="sidebar" class="fixed top-0 left-0 h-screen w-72 bg-gradient-to-b from-[#232946] to-[#1e1f37] flex flex-col transition-all duration-300 ease-in-out shadow-2xl border-r border-[#2d3250]/60 z-50 lg:relative lg:translate-x-0 transform -translate-x-full">
    
    <!-- Sidebar Header -->
    <div class="flex flex-col items-center gap-3 px-6 py-8 border-b border-[#2d3250]/40">
        <div class="relative">
            <img src="/assets/imgs/laos.png" alt="Logo" class="w-16 h-16 object-contain drop-shadow-lg transition-transform duration-500 hover:scale-110 hover:rotate-3">
            <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                <i class="fa-solid fa-crown text-xs text-white"></i>
            </div>
        </div>
        <div class="text-center">
            <h1 class="text-xl font-bold text-purple-200 tracking-wide transition-colors duration-300 hover:text-white">Tiếng Lào</h1>
            <p class="text-sm text-purple-300/70 mt-1 transition-colors duration-300 hover:text-purple-200">Admin Panel</p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 flex flex-col gap-2 px-4 py-4 overflow-y-auto scrollbar-thin scrollbar-track-transparent scrollbar-thumb-purple-500/30">
        @foreach($menuItems as $item)
            @if(isset($item['children']) && count($item['children']) > 0)
                <!-- Parent Menu Item with Children -->
                <div class="menu-group" data-menu-id="{{ $item['id'] }}">
                    <button type="button" 
                            class="menu-toggle w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-300 ease-in-out group text-sm relative overflow-hidden hover:shadow-lg transform hover:scale-[1.02] {{ $item['active'] ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-lg active-menu scale-[1.02]' : 'text-purple-100 hover:bg-gradient-to-r hover:from-purple-700/40 hover:to-indigo-700/40 hover:text-white' }}"
                            data-submenu="{{ $item['id'] }}-submenu">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-400/0 to-pink-400/0 group-hover:from-purple-400/10 group-hover:to-pink-400/10 transition-all duration-500 ease-out"></div>
                        <i class="{{ $item['icon'] }} text-lg relative z-10 transition-all duration-300 group-hover:scale-110 group-hover:rotate-3"></i>
                        <span class="relative z-10 flex-1 text-left transition-all duration-300">{{ $item['title'] }}</span>
                        <i class="fa-solid fa-chevron-down text-sm relative z-10 transition-all duration-400 ease-in-out chevron-icon group-hover:text-pink-300"></i>
                    </button>
                    
                    <!-- Submenu -->
                    <div id="{{ $item['id'] }}-submenu" class="submenu pl-6 mt-2 space-y-1 {{ $item['active'] ? '' : 'hidden' }}">
                        @foreach($item['children'] as $child)
                            <a href="{{ $child['url'] }}" 
                               class="flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium text-sm transition-all duration-300 ease-in-out group relative overflow-hidden transform hover:scale-[1.02] hover:shadow-md {{ $child['active'] ? 'bg-purple-600/80 text-white shadow-md active-submenu scale-[1.02]' : 'text-purple-200 hover:bg-gradient-to-r hover:from-purple-600/50 hover:to-indigo-600/50 hover:text-white' }}">
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-300/0 to-pink-300/0 group-hover:from-purple-300/5 group-hover:to-pink-300/5 transition-all duration-500"></div>
                                <i class="{{ $child['icon'] }} text-base relative z-10 transition-all duration-300 group-hover:scale-110 group-hover:text-pink-300"></i>
                                <span class="relative z-10 transition-all duration-300">{{ $child['title'] }}</span>
                                @if($child['active'])
                                    <div class="absolute right-2 top-1/2 transform -translate-y-1/2 w-2 h-2 bg-pink-400 rounded-full animate-pulse"></div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Single Menu Item -->
                <a href="{{ $item['url'] }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-300 ease-in-out group text-sm relative overflow-hidden hover:shadow-lg transform hover:scale-[1.02] {{ $item['active'] ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-lg active-menu scale-[1.02]' : 'text-purple-100 hover:bg-gradient-to-r hover:from-purple-700/40 hover:to-indigo-700/40 hover:text-white' }}">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-400/0 to-pink-400/0 group-hover:from-purple-400/10 group-hover:to-pink-400/10 transition-all duration-500 ease-out"></div>
                    <i class="{{ $item['icon'] }} text-lg relative z-10 transition-all duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:text-pink-300"></i>
                    <span class="relative z-10 transition-all duration-300">{{ $item['title'] }}</span>
                    @if($item['active'])
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 w-2 h-2 bg-pink-400 rounded-full animate-pulse"></div>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>

    <!-- Sidebar Footer -->
    <div class="px-4 py-4 border-t border-[#2d3250]/40 mt-auto">
        <!-- Logout Button -->
        <button id="logout-menu" 
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-300 ease-in-out group text-sm relative overflow-hidden text-purple-100 hover:bg-gradient-to-r hover:from-red-500/30 hover:to-pink-500/30 hover:text-red-200 hover:shadow-lg transform hover:scale-[1.02]">
            <div class="absolute inset-0 bg-gradient-to-r from-red-400/0 to-pink-400/0 group-hover:from-red-400/10 group-hover:to-pink-400/10 transition-all duration-500"></div>
            <i class="fa-solid fa-arrow-right-from-bracket text-lg relative z-10 transition-all duration-300 group-hover:scale-110 group-hover:text-red-300"></i>
            <span class="relative z-10 transition-all duration-300">Đăng xuất</span>
        </button>
        
        <!-- Copyright Footer -->
        <div class="mt-4 pt-3 border-t border-[#2d3250]/30">
            <div class="text-center">
                <p class="text-xs text-purple-300/60 transition-colors duration-300 hover:text-purple-200">
                    © 2024 Tiếng Lào
                </p>
                <p class="text-xs text-purple-400/50 mt-1 transition-colors duration-300 hover:text-purple-300">
                    Admin Panel v1.0
                </p>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden transition-all duration-300 ease-in-out backdrop-blur-sm"></div>

<!-- Mobile Sidebar Toggle Button -->
<button id="sidebar-toggle" 
        class="fixed top-4 left-4 z-50 lg:hidden bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 ease-in-out transform hover:scale-110 hover:from-purple-700 hover:to-indigo-700">
    <i class="fa-solid fa-bars text-lg transition-transform duration-300"></i>
</button>

<!-- Enhanced Custom Styles for Sidebar -->
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
    transition: background 0.3s ease;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(147, 51, 234, 0.6);
}

/* Enhanced menu item animations with smoother transitions */
.menu-toggle, nav a {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform, box-shadow, background-color;
}

.menu-toggle:hover, nav a:hover {
    transform: translateX(4px) scale(1.02);
    box-shadow: 0 8px 25px rgba(147, 51, 234, 0.3);
}

/* Enhanced active states with better visual feedback */
.active-menu {
    box-shadow: 0 6px 20px rgba(147, 51, 234, 0.4);
    border: 1px solid rgba(147, 51, 234, 0.3);
    transform: scale(1.02);
}

.active-submenu {
    box-shadow: 0 4px 15px rgba(147, 51, 234, 0.3);
    border-left: 3px solid #ec4899;
    transform: scale(1.02);
}

/* Responsive design improvements */
@media (max-width: 1023px) {
    #sidebar {
        width: 280px;
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
    }
}

/* Submenu animation with enhanced easing */
.submenu {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease-in-out;
    opacity: 0;
}

.submenu.show {
    max-height: 500px;
    opacity: 1;
}

/* Enhanced chevron rotation with spring effect */
.chevron-rotated {
    transform: rotate(180deg) scale(1.1);
    color: #ec4899;
}

/* Hover ripple effect */
@keyframes ripple {
    0% {
        transform: scale(0);
        opacity: 1;
    }
    100% {
        transform: scale(4);
        opacity: 0;
    }
}

.menu-ripple::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    pointer-events: none;
    animation: ripple 0.6s linear;
}

/* Enhanced focus states for better accessibility */
.menu-toggle:focus,
nav a:focus {
    outline: 2px solid #8b5cf6;
    outline-offset: 2px;
    transform: scale(1.02);
}

/* Loading state enhancements */
.sidebar-loading {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Copyright footer styling */
.sidebar-footer-copyright {
    background: linear-gradient(135deg, rgba(45, 50, 80, 0.1), rgba(35, 41, 70, 0.2));
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
</style>

<!-- jQuery CDN and Enhanced Sidebar Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize sidebar functionality with enhanced animations
    initializeSidebar();
    
    function initializeSidebar() {
        // Enhanced submenu toggles with smoother animations
        $('.menu-toggle').on('click', function() {
            const $button = $(this);
            const submenuId = $button.data('submenu');
            const $submenu = $('#' + submenuId);
            const $chevron = $button.find('.chevron-icon');
            
            // Add ripple effect
            addRippleEffect($button);
            
            // Close other open submenus with stagger animation
            $('.submenu').not($submenu).each(function(index) {
                const $this = $(this);
                setTimeout(() => {
                    $this.removeClass('show').slideUp(250);
                }, index * 50);
            });
            $('.chevron-icon').not($chevron).removeClass('chevron-rotated');
            
            // Toggle current submenu with enhanced animation
            if ($submenu.hasClass('show')) {
                $submenu.removeClass('show');
                $submenu.slideUp(300, function() {
                    $submenu.css('opacity', '');
                });
                $chevron.removeClass('chevron-rotated');
            } else {
                $submenu.addClass('show');
                $submenu.slideDown(350, function() {
                    $submenu.css('opacity', '1');
                });
                $chevron.addClass('chevron-rotated');
            }
        });
        
        // Enhanced mobile sidebar toggle
        $('#sidebar-toggle').on('click', function() {
            $(this).addClass('animate-pulse');
            setTimeout(() => $(this).removeClass('animate-pulse'), 200);
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
        
        // Handle window resize with debouncing
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth >= 1024) {
                    closeMobileSidebar();
                }
            }, 100);
        });
        
        // Initialize active submenu states
        $('.active-menu').each(function() {
            const $button = $(this);
            if ($button.hasClass('menu-toggle')) {
                const submenuId = $button.data('submenu');
                const $submenu = $('#' + submenuId);
                const $chevron = $button.find('.chevron-icon');
                
                $submenu.addClass('show').show().css('opacity', '1');
                $chevron.addClass('chevron-rotated');
            }
        });
        
        // Add enhanced hover effects
        addEnhancedHoverEffects();
    }
    
    function addRippleEffect($element) {
        $element.addClass('menu-ripple');
        setTimeout(() => $element.removeClass('menu-ripple'), 600);
    }
    
    function toggleMobileSidebar() {
        const $sidebar = $('#sidebar');
        const $overlay = $('#sidebar-overlay');
        
        if ($sidebar.hasClass('-translate-x-full')) {
            // Show sidebar with enhanced animation
            $sidebar.removeClass('-translate-x-full');
            $overlay.removeClass('hidden').fadeIn(400);
            $sidebar.css('transform', 'translateX(0)');
        } else {
            // Hide sidebar with enhanced animation
            $sidebar.addClass('-translate-x-full');
            $overlay.fadeOut(400, function() {
                $(this).addClass('hidden');
            });
        }
    }
    
    function closeMobileSidebar() {
        const $sidebar = $('#sidebar');
        const $overlay = $('#sidebar-overlay');
        
        $sidebar.addClass('-translate-x-full');
        $overlay.fadeOut(400, function() {
            $(this).addClass('hidden');
        });
    }
    
    function addEnhancedHoverEffects() {
        // Enhanced hover animations for menu items
        $('.menu-toggle, nav a').on('mouseenter', function() {
            $(this).addClass('animate-pulse');
            $(this).find('i').addClass('animate-bounce');
        }).on('mouseleave', function() {
            $(this).removeClass('animate-pulse');
            $(this).find('i').removeClass('animate-bounce');
        });
        
        // Smooth scroll behavior for sidebar
        $('#sidebar nav').on('wheel', function(e) {
            const delta = e.originalEvent.deltaY;
            const scrollTop = this.scrollTop;
            const scrollHeight = this.scrollHeight;
            const height = this.clientHeight;
            
            if ((delta < 0 && scrollTop === 0) || (delta > 0 && scrollTop === scrollHeight - height)) {
                e.preventDefault();
            }
            
            this.scrollTop += delta * 0.8; // Smooth scrolling
            e.preventDefault();
        });
    }
    
    // Enhanced menu highlighting based on current URL
    function updateActiveStates() {
        const currentPath = window.location.pathname;
        
        // Remove all active states
        $('.active-menu, .active-submenu').removeClass('active-menu active-submenu');
        
        // Add active state to matching menu items with animation
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
    
    // Add loading states for better UX
    $(document).on('beforeunload', function() {
        $('#sidebar').addClass('sidebar-loading');
    });
});
</script> 