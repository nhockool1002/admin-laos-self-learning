# Badge Management System

Hệ thống Quản lý Huy hiệu đầy đủ tính năng cho ứng dụng Laravel với tích hợp Supabase.

## 🚀 Tính năng

### Quản lý Huy hiệu
- ✅ **CRUD đầy đủ**: Tạo, đọc, cập nhật, xóa huy hiệu
- ✅ **Upload hình ảnh**: Hỗ trợ PNG, JPG, GIF, SVG (max 2MB)
- ✅ **Tìm kiếm**: Tìm kiếm theo tên và mô tả
- ✅ **Giao diện đẹp**: UI hiện đại với Tailwind CSS

### Quản lý Huy hiệu Người dùng
- ✅ **Tặng huy hiệu**: Tặng huy hiệu cho người dùng
- ✅ **Thu hồi huy hiệu**: Thu hồi huy hiệu từ người dùng
- ✅ **Kiểm tra trùng lặp**: Ngăn chặn tặng cùng huy hiệu 2 lần
- ✅ **Quản lý người dùng**: Xem danh sách người dùng có huy hiệu

### API cho bên thứ 3
- ✅ **Public API**: Endpoints công khai cho truy vấn
- ✅ **RESTful**: Tuân thủ chuẩn REST API
- ✅ **JSON Response**: Định dạng phản hồi chuẩn
- ✅ **Error Handling**: Xử lý lỗi chi tiết

## 📁 Cấu trúc Files

### Backend (Laravel)
```
app/
├── Http/Controllers/
│   └── BadgeController.php          # Controller chính cho badge management
├── Services/
│   └── SupabaseService.php          # Service tích hợp Supabase (đã cập nhật)

routes/
└── web.php                          # Routes cho badge management

resources/views/
├── badges.blade.php                 # Giao diện quản lý huy hiệu
└── components/
    └── sidebar.blade.php            # Sidebar đã thêm menu huy hiệu
```

### Database
```
database-schema.sql                  # Schema cơ sở dữ liệu
```

### Assets
```
public/
├── assets/
│   └── images/                      # Thư mục lưu hình ảnh huy hiệu
└── api-documentation.md             # Tài liệu API
```

## 🛠️ Cài đặt

### 1. Database Setup
```sql
-- Chạy script SQL để tạo bảng
psql -h your-supabase-host -U postgres -d your-database -f database-schema.sql
```

### 2. Permissions Setup
Đảm bảo thư mục `public/assets/images` có quyền ghi:
```bash
chmod 755 public/assets/images
```

### 3. Dependencies
Các dependency đã có sẵn trong Laravel project:
- `illuminate/http` - HTTP client
- `illuminate/validation` - Validation
- `illuminate/support` - Support helpers

## 📖 Sử dụng

### Giao diện Admin
1. Truy cập `/admin/badges` để quản lý huy hiệu
2. Sử dụng nút "Thêm Huy hiệu" để tạo huy hiệu mới
3. Click vào các nút thao tác để:
   - ✏️ Chỉnh sửa huy hiệu
   - 🎁 Tặng huy hiệu cho người dùng
   - 👥 Quản lý người dùng có huy hiệu
   - 🗑️ Xóa huy hiệu

### API Endpoints

#### Admin Endpoints (Cần xác thực admin)
```
GET    /supabase/badges                    # Lấy danh sách huy hiệu
POST   /supabase/badges                    # Tạo huy hiệu mới
GET    /supabase/badges/{id}               # Chi tiết huy hiệu
PUT    /supabase/badges/{id}               # Cập nhật huy hiệu
DELETE /supabase/badges/{id}               # Xóa huy hiệu

POST   /supabase/user-badges/award         # Tặng huy hiệu
POST   /supabase/user-badges/revoke        # Thu hồi huy hiệu
GET    /supabase/users-with-badges         # Người dùng với huy hiệu
```

#### Public API Endpoints
```
GET    /api/v1/badges                      # Danh sách huy hiệu
GET    /api/v1/badges/{id}                 # Chi tiết huy hiệu
GET    /api/v1/users/{userId}/badges       # Huy hiệu của người dùng
POST   /api/v1/badges/award                # Tặng huy hiệu
POST   /api/v1/badges/revoke               # Thu hồi huy hiệu
```

## 🔧 Cấu hình

### Supabase Configuration
Đảm bảo file `.env` có cấu hình Supabase:
```env
SUPABASE_URL=your-supabase-url
SUPABASE_ANON_KEY=your-anon-key
```

### File Upload Configuration
Cấu hình upload trong `config/filesystems.php` nếu cần tùy chỉnh.

## 📊 Database Schema

### Bảng `badges`
- `id` - Primary key
- `name` - Tên huy hiệu (unique)
- `description` - Mô tả huy hiệu
- `image_url` - Đường dẫn hình ảnh
- `created_at` - Thời gian tạo
- `updated_at` - Thời gian cập nhật

### Bảng `user_badges`
- `id` - Primary key
- `user_id` - ID người dùng
- `badge_id` - ID huy hiệu
- `awarded_at` - Thời gian tặng
- Unique constraint: `(user_id, badge_id)`

## 🔒 Bảo mật

### Authentication
- Admin endpoints kiểm tra quyền admin
- User header authentication
- CSRF protection

### Validation
- File upload validation (type, size)
- Input validation cho tất cả fields
- Duplicate award prevention

### File Security
- Kiểm tra file type
- Giới hạn kích thước file
- Sanitize file names

## 🎨 UI/UX Features

### Giao diện hiện đại
- Responsive design
- Dark theme với gradient
- Smooth animations
- Modal dialogs

### User Experience
- Real-time search
- Image preview
- Loading states
- Error handling
- Success notifications

## 📈 Performance

### Optimizations
- Database indexes
- Efficient queries
- Image optimization
- Lazy loading

### Caching
- File upload caching
- Query result caching
- Static asset caching

## 🔄 API Usage Examples

### JavaScript
```javascript
// Lấy danh sách huy hiệu
const badges = await fetch('/api/v1/badges').then(r => r.json());

// Tặng huy hiệu
await fetch('/api/v1/badges/award', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ user_id: 123, badge_id: 1 })
});
```

### PHP
```php
// Lấy huy hiệu của người dùng
$response = file_get_contents("$baseUrl/api/v1/users/123/badges");
$userBadges = json_decode($response, true);
```

### Python
```python
import requests

# Thu hồi huy hiệu
requests.post(f"{base_url}/api/v1/badges/revoke", 
    json={"user_id": 123, "badge_id": 1})
```

## 🐛 Troubleshooting

### Common Issues

1. **Upload permission denied**
   ```bash
   chmod 755 public/assets/images
   ```

2. **Database connection issues**
   - Kiểm tra Supabase credentials
   - Kiểm tra RLS policies

3. **Image not displaying**
   - Kiểm tra file path
   - Kiểm tra permissions

### Debug Mode
Enable debug logging trong SupabaseService để theo dõi API calls.

## 🧪 Testing

Badge Management System đi kèm với test suite đầy đủ:

### Test Coverage
- ✅ **65+ test cases** bao phủ tất cả chức năng
- ✅ **Unit Tests**: Controller và Service layer
- ✅ **Feature Tests**: Integration testing
- ✅ **API Tests**: Third-party integration
- ✅ **Security Tests**: Authentication và authorization

### Running Tests
```bash
# Run all badge tests
php artisan test --filter=Badge

# Run specific test files
php artisan test tests/Unit/BadgeControllerTest.php
php artisan test tests/Feature/BadgeManagementTest.php

# Run with coverage
php artisan test --coverage --filter=Badge
```

### Test Files
- `tests/Unit/BadgeControllerTest.php` - Controller tests
- `tests/Unit/SupabaseBadgeServiceTest.php` - Service tests
- `tests/Feature/BadgeManagementTest.php` - Integration tests
- `tests/Unit/BadgeTestSuite.php` - Complete test suite
- `BADGE_TESTING_GUIDE.md` - Testing documentation

## 📞 Support

Xem thêm tài liệu:
- `public/api-documentation.md` - API documentation
- `database-schema.sql` - Database schema
- `BADGE_TESTING_GUIDE.md` - Testing guide
- Laravel documentation cho configuration