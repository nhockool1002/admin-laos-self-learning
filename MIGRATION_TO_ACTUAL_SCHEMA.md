# Migration Guide: Updating Badge Management System to Match Actual Supabase Schema

## Những thay đổi chính đã được thực hiện

### 1. Database Schema Updates

#### Table Names:
- ✅ `badges` → `badges_system`
- ✅ `user_badges` (không đổi)

#### Field Changes trong `badges_system`:
- ✅ `id`: BIGSERIAL → TEXT (ID dạng string như 'badge_001')
- ✅ `image_url` → `image_path`
- ✅ Thêm field `condition` (TEXT)
- ✅ Xóa `created_at`, `updated_at` (tự động)

#### Field Changes trong `user_badges`:
- ✅ `user_id` (BIGINT) → `username` (TEXT)
- ✅ `badge_id`: BIGINT → TEXT
- ✅ `awarded_at` → `achieved_date`

### 2. Backend Code Updates

#### SupabaseService.php:
```php
// Updated methods:
- getBadges() - now uses '/badges_system'
- getBadgeById() - now uses '/badges_system'
- createBadge() - now uses '/badges_system'
- updateBadge() - now uses '/badges_system'
- deleteBadge() - now uses '/badges_system'

// User badges methods:
- getUserBadgesByUsername() - new method using 'username'
- checkUserBadgeExists() - now uses 'username' and 'badge_id'
- awardUserBadge() - now uses 'username' and 'achieved_date'
- revokeUserBadge() - now uses 'username'
```

#### BadgeController.php:
```php
// Updated validation and data:
- store(): now includes 'id' generation, 'image_path', 'condition'
- update(): now handles 'image_path', 'condition'
- destroy(): now uses 'image_path'
- awardBadge(): now uses 'username', 'achieved_date'
- revokeBadge(): now uses 'username'
- apiUserBadges(): now uses 'username' parameter
```

#### Routes:
```php
// Updated routes:
- '/users/{username}/badges' (instead of userId)
- '/supabase/user-badges/{username}' (instead of userId)
```

### 3. Frontend Updates

#### HTML Changes:
- ✅ Added `condition` field to badge form
- ✅ Changed user selection from dropdown to text input for username
- ✅ Updated display to show badge ID and condition

#### JavaScript Changes:
- ✅ Updated to use `image_path` instead of `image_url`
- ✅ Updated to use `username` instead of `user_id`
- ✅ Updated to use `achieved_date` instead of `awarded_at`
- ✅ Added support for `condition` field

### 4. Database Schema File

Created `database-schema-actual.sql` với structure khớp với Supabase thực tế:

```sql
-- Badges table
CREATE TABLE badges_system (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    image_path TEXT NOT NULL,
    condition TEXT
);

-- User badges table
CREATE TABLE user_badges (
    id SERIAL PRIMARY KEY,
    username TEXT NOT NULL,
    badge_id TEXT NOT NULL REFERENCES badges_system(id),
    achieved_date TIMESTAMP DEFAULT NOW(),
    UNIQUE(username, badge_id)
);
```

### 5. Sample Data

Updated sample data to match your current data:

```sql
INSERT INTO badges_system (id, name, description, image_path, condition) VALUES
('badge_001', 'Huy hiệu Phụ Âm I', 'Hoàn thành bài luyện tập phụ âm với điểm cao', '/badges/best-practice.png', 'nan'),
('badge_002', 'Huy hiệu Phiên Âm I', 'Hoàn thành bài luyện tập phiên âm với điểm cao', '/badges/nguyenam1.png', 'nan');

INSERT INTO user_badges (username, badge_id, achieved_date) VALUES
('nhockool002', 'badge_001', '2025-06-12 16:06:14.848');
```

### 6. API Updates

#### Request Format Changes:

**Old Award Badge Request:**
```json
{
    "user_id": 123,
    "badge_id": 1
}
```

**New Award Badge Request:**
```json
{
    "username": "nhockool002",
    "badge_id": "badge_001"
}
```

**Old Create Badge Request:**
```json
{
    "name": "Test Badge",
    "description": "Test Description",
    "image_url": "/assets/images/badge.png"
}
```

**New Create Badge Request:**
```json
{
    "id": "badge_003",
    "name": "Test Badge", 
    "description": "Test Description",
    "image_path": "/assets/images/badge.png",
    "condition": "Complete all lessons"
}
```

### 7. Updated API Endpoints

```
GET /api/v1/badges
GET /api/v1/badges/{id}
GET /api/v1/users/{username}/badges  // Changed from userId
POST /api/v1/badges/award
POST /api/v1/badges/revoke
```

### 8. Backward Compatibility

Được giữ để compatibility:
- `getUserBadgesByUserId()` method vẫn hoạt động, internally sử dụng username

### 9. Testing Updates

Cần update test files để phù hợp với schema mới:
- Update mock data generation
- Update API endpoint tests  
- Update field validation tests

### 10. Migration Steps

1. ✅ **Backend Code**: Updated SupabaseService, BadgeController
2. ✅ **Frontend Code**: Updated Blade templates and JavaScript
3. ✅ **Routes**: Updated route parameters
4. ✅ **Database Schema**: Created actual schema file
5. ✅ **Documentation**: Updated API docs and README

### 11. Files Changed

```
app/Services/SupabaseService.php          - Table names, field names
app/Http/Controllers/BadgeController.php  - Validation, data handling
routes/web.php                           - Route parameters
resources/views/badges.blade.php         - HTML/JS updates
database-schema-actual.sql               - New schema file
```

### 12. Verification Steps

1. **Check Database Connection**: Ensure tables `badges_system` and `user_badges` exist
2. **Test Badge Creation**: Create badge with condition field
3. **Test Badge Award**: Award badge using username
4. **Test API Endpoints**: Test with new request format
5. **Check Image Handling**: Ensure `image_path` works correctly

### 13. Key Benefits

- ✅ **Perfect Match**: Code now matches your actual Supabase schema
- ✅ **Data Integrity**: Proper foreign key relationships
- ✅ **Flexibility**: Text-based IDs for badges
- ✅ **User-Friendly**: Username-based operations
- ✅ **Extended Features**: Condition field for badge criteria

Hệ thống Badge Management hiện đã được cập nhật để hoàn toàn khớp với database schema thực tế trên Supabase của bạn! 🎉