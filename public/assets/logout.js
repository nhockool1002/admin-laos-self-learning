// Function chung để xử lý logout
async function performLogout() {
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
}

// Khởi tạo xử lý logout khi trang load
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý logout cho nút trên header
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', performLogout);
    }
    
    // Xử lý logout cho menu sidebar
    const logoutMenu = document.getElementById('logout-menu');
    if (logoutMenu) {
        logoutMenu.addEventListener('click', function(e) {
            e.preventDefault();
            performLogout();
        });
    }
}); 