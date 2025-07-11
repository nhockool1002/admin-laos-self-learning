# Hướng dẫn Deploy nhanh lên aaPanel

## Tổng quan
Đây là ứng dụng Laravel 12 Admin Panel cho hệ thống học tiếng Lào, sử dụng Supabase làm database.

## Yêu cầu hệ thống
- PHP 8.2+
- Composer
- Node.js 18+
- aaPanel

## Deploy nhanh

### 1. Chuẩn bị aaPanel
```bash
# Cài đặt PHP 8.2 và các extension cần thiết
# Cài đặt Composer và Node.js
```

### 2. Tạo website trên aaPanel
- Domain: yourdomain.com
- PHP: 8.2
- Database: Không cần (dùng Supabase)

### 3. Upload và deploy
```bash
# SSH vào server
cd /www/wwwroot/yourdomain.com

# Upload code (git clone hoặc upload qua File Manager)

# Chạy script deploy tự động
chmod +x deploy-aapanel.sh
./deploy-aapanel.sh yourdomain.com
```

### 4. Cấu hình .env
```env
APP_NAME="Laos Learning Admin"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Supabase Configuration
APP_SUPABASE_URL=https://your-project.supabase.co
APP_SUPABASE_ANON_KEY=your-supabase-anon-key

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/www/wwwroot/yourdomain.com/database/database.sqlite
```

### 5. Cấu hình Nginx
Copy nội dung từ `/tmp/nginx_config.txt` vào Website Settings > Rewrite

### 6. Cấu hình SSL
Vào SSL > Let's Encrypt để cài đặt certificate

## Kiểm tra
- Truy cập https://yourdomain.com
- Test đăng nhập admin
- Kiểm tra các chức năng CRUD

## Troubleshooting
- Kiểm tra logs: `/www/wwwroot/yourdomain.com/storage/logs/laravel.log`
- Kiểm tra permissions: `chmod -R 777 storage bootstrap/cache`
- Restart services: `systemctl restart nginx php-fpm`

## Liên hệ
Nếu gặp vấn đề, xem file `DEPLOY_AAPANEL.md` để có hướng dẫn chi tiết. 