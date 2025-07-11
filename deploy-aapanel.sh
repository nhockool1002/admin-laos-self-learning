#!/bin/bash

# Script tự động deploy Laravel app lên aaPanel
# Sử dụng: ./deploy-aapanel.sh yourdomain.com

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if domain is provided
if [ -z "$1" ]; then
    echo -e "${RED}Error: Vui lòng cung cấp domain name${NC}"
    echo "Sử dụng: ./deploy-aapanel.sh yourdomain.com"
    exit 1
fi

DOMAIN=$1
PROJECT_PATH="/www/wwwroot/$DOMAIN"

echo -e "${GREEN}=== Bắt đầu deploy Laravel app lên aaPanel ===${NC}"
echo -e "${YELLOW}Domain: $DOMAIN${NC}"
echo -e "${YELLOW}Path: $PROJECT_PATH${NC}"

# Check if project directory exists
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}Error: Thư mục $PROJECT_PATH không tồn tại${NC}"
    echo "Vui lòng tạo website trên aaPanel trước"
    exit 1
fi

# Backup current files (if any)
if [ -f "$PROJECT_PATH/index.php" ]; then
    echo -e "${YELLOW}Backup files hiện tại...${NC}"
    cp -r "$PROJECT_PATH" "${PROJECT_PATH}_backup_$(date +%Y%m%d_%H%M%S)"
fi

# Clean project directory
echo -e "${YELLOW}Dọn dẹp thư mục project...${NC}"
cd "$PROJECT_PATH"
rm -rf *

# Copy current project files
echo -e "${YELLOW}Copy project files...${NC}"
# Assuming we're running this script from the project root
cp -r . "$PROJECT_PATH/"

# Set proper permissions
echo -e "${YELLOW}Set permissions...${NC}"
chown -R www:www "$PROJECT_PATH"
chmod -R 755 "$PROJECT_PATH"
chmod -R 777 "$PROJECT_PATH/storage"
chmod -R 777 "$PROJECT_PATH/bootstrap/cache"

# Install PHP dependencies
echo -e "${YELLOW}Cài đặt PHP dependencies...${NC}"
cd "$PROJECT_PATH"
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies and build assets
echo -e "${YELLOW}Cài đặt Node.js dependencies...${NC}"
npm install
npm run build

# Create .env file if not exists
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}Tạo file .env...${NC}"
    cp .env.example .env
    php artisan key:generate
fi

# Create SQLite database if needed
if [ ! -f "database/database.sqlite" ]; then
    echo -e "${YELLOW}Tạo SQLite database...${NC}"
    touch database/database.sqlite
    chmod 777 database/database.sqlite
fi

# Run migrations
echo -e "${YELLOW}Chạy migrations...${NC}"
php artisan migrate --force

# Cache Laravel
echo -e "${YELLOW}Cache Laravel...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create Nginx configuration
echo -e "${YELLOW}Tạo cấu hình Nginx...${NC}"
cat > /tmp/nginx_config.txt << EOF
# Copy nội dung này vào Website Settings > Rewrite trong aaPanel

location / {
    try_files \$uri \$uri/ /index.php?\$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/tmp/php-cgi-82.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    include fastcgi_params;
}

# Cache static files
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
EOF

echo -e "${GREEN}=== Deploy hoàn tất! ===${NC}"
echo -e "${YELLOW}Các bước tiếp theo:${NC}"
echo "1. Cấu hình .env file với Supabase credentials"
echo "2. Copy nội dung từ /tmp/nginx_config.txt vào Website Settings > Rewrite"
echo "3. Cấu hình SSL certificate"
echo "4. Test ứng dụng tại https://$DOMAIN"

# Check if everything is working
echo -e "${YELLOW}Kiểm tra trạng thái...${NC}"
if [ -f "index.php" ]; then
    echo -e "${GREEN}✓ Laravel app đã được deploy thành công${NC}"
else
    echo -e "${RED}✗ Có lỗi trong quá trình deploy${NC}"
fi

# Show current directory structure
echo -e "${YELLOW}Thư mục hiện tại:${NC}"
ls -la "$PROJECT_PATH"

echo -e "${GREEN}Deploy script hoàn tất!${NC}" 