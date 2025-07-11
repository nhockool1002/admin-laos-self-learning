# Checklist Deploy Laravel App lên aaPanel

## ✅ Pre-deployment
- [ ] Cài đặt aaPanel
- [ ] Cài đặt PHP 8.2
- [ ] Cài đặt Composer
- [ ] Cài đặt Node.js 18+
- [ ] Cài đặt các PHP extensions cần thiết
- [ ] Tạo website trên aaPanel
- [ ] Cấu hình domain và SSL

## ✅ Upload và cài đặt
- [ ] Upload code lên server
- [ ] Chạy `composer install --no-dev --optimize-autoloader`
- [ ] Chạy `npm install`
- [ ] Chạy `npm run build`
- [ ] Set permissions cho storage và bootstrap/cache
- [ ] Tạo file .env từ .env.example
- [ ] Generate APP_KEY với `php artisan key:generate`

## ✅ Cấu hình Environment
- [ ] Cấu hình APP_URL
- [ ] Cấu hình Supabase URL và API key
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Cấu hình database SQLite
- [ ] Cấu hình cache và session

## ✅ Database
- [ ] Tạo file database/database.sqlite
- [ ] Set permissions cho database file
- [ ] Chạy migrations: `php artisan migrate --force`
- [ ] Kiểm tra kết nối database

## ✅ Web Server Configuration
- [ ] Cấu hình Nginx rewrite rules
- [ ] Cấu hình PHP-FPM
- [ ] Tăng memory_limit và max_execution_time
- [ ] Cấu hình SSL certificate
- [ ] Test Nginx configuration

## ✅ Laravel Optimization
- [ ] Cache config: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`
- [ ] Optimize autoloader
- [ ] Set proper file permissions

## ✅ Testing
- [ ] Test trang chủ
- [ ] Test trang login
- [ ] Test đăng nhập admin
- [ ] Test CRUD operations
- [ ] Test API endpoints
- [ ] Kiểm tra logs

## ✅ Security
- [ ] Cấu hình firewall
- [ ] Set up backup
- [ ] Cấu hình rate limiting
- [ ] Kiểm tra file permissions
- [ ] Disable debug mode

## ✅ Monitoring
- [ ] Set up log monitoring
- [ ] Cấu hình error reporting
- [ ] Set up performance monitoring
- [ ] Cấu hình cron jobs (nếu cần)

## ✅ Post-deployment
- [ ] Test tất cả chức năng
- [ ] Kiểm tra performance
- [ ] Backup database
- [ ] Document deployment
- [ ] Set up monitoring alerts

## 🔧 Troubleshooting Commands

### Kiểm tra trạng thái
```bash
# PHP version
php -v

# Composer
composer --version

# Node.js
node -v
npm -v

# Nginx
nginx -t

# Services
systemctl status nginx
systemctl status php-fpm
```

### Logs
```bash
# Laravel logs
tail -f /www/wwwroot/yourdomain.com/storage/logs/laravel.log

# Nginx logs
tail -f /www/wwwlogs/yourdomain.com.log

# PHP error logs
tail -f /www/server/php/82/var/log/php-fpm.log
```

### Permissions
```bash
# Fix permissions
chown -R www:www /www/wwwroot/yourdomain.com
chmod -R 755 /www/wwwroot/yourdomain.com
chmod -R 777 /www/wwwroot/yourdomain.com/storage
chmod -R 777 /www/wwwroot/yourdomain.com/bootstrap/cache
```

### Cache
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📞 Support
Nếu gặp vấn đề:
1. Kiểm tra logs
2. Xem file `DEPLOY_AAPANEL.md` để có hướng dẫn chi tiết
3. Kiểm tra permissions và file ownership
4. Restart services nếu cần 