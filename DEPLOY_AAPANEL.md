# Hướng dẫn Deploy Laravel App lên aaPanel

## Tổng quan dự án
- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: Supabase (PostgreSQL)
- **Frontend**: Tailwind CSS, Vite, Alpine.js
- **Chức năng**: Admin panel quản lý hệ thống học tiếng Lào

## Yêu cầu hệ thống
- PHP 8.2 hoặc cao hơn
- Composer
- Node.js 18+ và npm
- Nginx hoặc Apache
- SSL certificate (khuyến nghị)

## Bước 1: Chuẩn bị aaPanel

### 1.1 Cài đặt aaPanel
```bash
# Cài đặt aaPanel trên CentOS
yum install -y wget && wget -O install.sh http://www.aapanel.com/script/install_6.0.sh && sh install.sh

# Cài đặt aaPanel trên Ubuntu/Debian
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0.sh && sudo bash install.sh
```

### 1.2 Cài đặt PHP 8.2
1. Đăng nhập vào aaPanel
2. Vào **Software Store** → **PHP**
3. Cài đặt PHP 8.2
4. Bật các extension cần thiết:
   - `fileinfo`
   - `openssl`
   - `pdo`
   - `mbstring`
   - `tokenizer`
   - `xml`
   - `ctype`
   - `json`
   - `bcmath`
   - `curl`

### 1.3 Cài đặt Composer
```bash
# SSH vào server và chạy
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 1.4 Cài đặt Node.js
```bash
# Cài đặt Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
apt-get install -y nodejs

# Hoặc sử dụng nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 18
nvm use 18
```

## Bước 2: Tạo Website trên aaPanel

### 2.1 Tạo website
1. Vào **Website** → **Add Site**
2. Điền thông tin:
   - **Domain**: yourdomain.com
   - **PHP Version**: 8.2
   - **Database**: Không cần (sử dụng Supabase)
3. Tạo website

### 2.2 Cấu hình SSL
1. Vào **SSL** → **Let's Encrypt**
2. Chọn domain và cài đặt SSL certificate

## Bước 3: Upload và cài đặt code

### 3.1 Upload code
```bash
# SSH vào server
cd /www/wwwroot/yourdomain.com

# Xóa file mặc định
rm -rf *

# Upload code từ local (sử dụng git hoặc scp)
git clone https://github.com/your-repo/laos-learning-admin.git .
# Hoặc upload qua File Manager của aaPanel
```

### 3.2 Cài đặt dependencies
```bash
# Cài đặt PHP dependencies
composer install --no-dev --optimize-autoloader

# Cài đặt Node.js dependencies
npm install

# Build assets
npm run build
```

### 3.3 Cấu hình permissions
```bash
# Set permissions
chown -R www:www /www/wwwroot/yourdomain.com
chmod -R 755 /www/wwwroot/yourdomain.com
chmod -R 777 /www/wwwroot/yourdomain.com/storage
chmod -R 777 /www/wwwroot/yourdomain.com/bootstrap/cache
```

## Bước 4: Cấu hình Environment

### 4.1 Tạo file .env
```bash
# Copy file .env.example
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4.2 Cấu hình .env
```env
APP_NAME="Laos Learning Admin"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Supabase Configuration
APP_SUPABASE_URL=https://your-project.supabase.co
APP_SUPABASE_ANON_KEY=your-supabase-anon-key

# Database (không cần thiết vì dùng Supabase)
DB_CONNECTION=sqlite
DB_DATABASE=/www/wwwroot/yourdomain.com/database/database.sqlite

# Cache và Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Log
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
```

### 4.3 Tạo database SQLite (nếu cần)
```bash
# Tạo file database SQLite
touch database/database.sqlite

# Chạy migrations (nếu có)
php artisan migrate --force
```

## Bước 5: Cấu hình Nginx

### 5.1 Cấu hình Nginx
Vào **Website** → **Settings** → **Rewrite** và thêm:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/tmp/php-cgi-82.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Cache static files
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### 5.2 Cấu hình PHP
1. Vào **PHP** → **PHP 8.2** → **Config**
2. Tăng các giá trị:
   - `memory_limit = 512M`
   - `max_execution_time = 300`
   - `upload_max_filesize = 64M`
   - `post_max_size = 64M`

## Bước 6: Tối ưu hóa

### 6.1 Cache Laravel
```bash
# Cache config, routes, views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache nếu cần
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 6.2 Cấu hình cron job (nếu cần)
```bash
# Thêm vào crontab
crontab -e

# Thêm dòng sau
* * * * * cd /www/wwwroot/yourdomain.com && php artisan schedule:run >> /dev/null 2>&1
```

## Bước 7: Kiểm tra và test

### 7.1 Kiểm tra logs
```bash
# Kiểm tra Laravel logs
tail -f /www/wwwroot/yourdomain.com/storage/logs/laravel.log

# Kiểm tra Nginx logs
tail -f /www/wwwlogs/yourdomain.com.log
```

### 7.2 Test ứng dụng
1. Truy cập https://yourdomain.com
2. Kiểm tra trang login
3. Test đăng nhập admin
4. Kiểm tra các chức năng CRUD

## Bước 8: Bảo mật

### 8.1 Cấu hình firewall
```bash
# Mở port cần thiết
firewall-cmd --permanent --add-port=80/tcp
firewall-cmd --permanent --add-port=443/tcp
firewall-cmd --reload
```

### 8.2 Backup tự động
1. Vào **Backup** → **Add Backup**
2. Cấu hình backup tự động cho website

## Troubleshooting

### Lỗi thường gặp:

1. **500 Internal Server Error**
   - Kiểm tra permissions: `chmod -R 755 /www/wwwroot/yourdomain.com`
   - Kiểm tra logs: `tail -f /www/wwwlogs/yourdomain.com.error.log`

2. **Permission denied**
   ```bash
   chown -R www:www /www/wwwroot/yourdomain.com
   chmod -R 755 /www/wwwroot/yourdomain.com
   chmod -R 777 /www/wwwroot/yourdomain.com/storage
   ```

3. **Composer memory limit**
   ```bash
   COMPOSER_MEMORY_LIMIT=-1 composer install
   ```

4. **Node.js build fail**
   ```bash
   npm cache clean --force
   rm -rf node_modules package-lock.json
   npm install
   npm run build
   ```

### Kiểm tra trạng thái:
```bash
# Kiểm tra PHP version
php -v

# Kiểm tra Composer
composer --version

# Kiểm tra Node.js
node -v
npm -v

# Kiểm tra Nginx
nginx -t

# Kiểm tra services
systemctl status nginx
systemctl status php-fpm
```

## Lưu ý quan trọng

1. **Environment Variables**: Đảm bảo cấu hình đúng Supabase URL và API key
2. **SSL**: Luôn sử dụng HTTPS trong production
3. **Backup**: Thiết lập backup tự động
4. **Monitoring**: Theo dõi logs và performance
5. **Updates**: Cập nhật Laravel và dependencies thường xuyên

## Liên hệ hỗ trợ

Nếu gặp vấn đề, kiểm tra:
- Laravel logs: `/www/wwwroot/yourdomain.com/storage/logs/laravel.log`
- Nginx logs: `/www/wwwlogs/yourdomain.com.log`
- PHP error logs: `/www/server/php/82/var/log/php-fpm.log` 