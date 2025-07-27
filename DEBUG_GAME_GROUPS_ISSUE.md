# Debug Guide: Game Groups Type Issue

## 🔍 **Vấn đề hiện tại**
Trang "Cài đặt trò chơi theo bài học" vẫn đang load các game groups Type A thay vì chỉ load Type B.

## 🛠️ **Cách kiểm tra và debug**

### 1. Kiểm tra Database Schema
```sql
-- Kiểm tra xem cột group_game_type đã được thêm chưa
SELECT column_name, data_type, column_default 
FROM information_schema.columns 
WHERE table_name = 'game_groups' 
AND column_name = 'group_game_type';

-- Kiểm tra dữ liệu hiện tại
SELECT id, name, group_game_type 
FROM game_groups 
ORDER BY group_game_type, name;
```

### 2. Apply Database Migration
Nếu cột `group_game_type` chưa tồn tại, chạy migration:
```sql
ALTER TABLE public.game_groups 
ADD COLUMN group_game_type character(1) NOT NULL DEFAULT 'A' 
CHECK (group_game_type IN ('A', 'B'));
```

### 3. Kiểm tra API Response
Mở Developer Tools (F12) và kiểm tra:

#### a) Truy cập debug endpoint:
```
GET /debug/game-groups
```

#### b) Kiểm tra API endpoints:
```
GET /supabase/game-groups          (should return Type A groups only)
GET /supabase/lesson-game-groups   (should return Type B groups only)
```

### 4. Sử dụng Debug Button
1. Vào trang "Cài đặt trò chơi theo bài học"
2. Click nút "Debug Groups" (màu xanh)
3. Mở Console (F12) để xem kết quả chi tiết

### 5. Tạo Test Data
Tạo nhóm trò chơi Type B để test:
```sql
INSERT INTO game_groups (name, description, group_game_type) 
VALUES ('Nhóm test Type B', 'Nhóm test cho lesson games', 'B');
```

## 🔧 **Các bước giải quyết**

### Bước 1: Xác nhận Database Schema
```sql
-- Xem tất cả columns của game_groups
\d game_groups

-- Hoặc
DESCRIBE game_groups;
```

### Bước 2: Kiểm tra Data
```sql
-- Count theo type
SELECT 
    group_game_type,
    COUNT(*) as count
FROM game_groups 
GROUP BY group_game_type;

-- Xem chi tiết
SELECT * FROM game_groups WHERE group_game_type = 'B';
```

### Bước 3: Test API Trực tiếp
Sử dụng Postman hoặc curl:
```bash
# Test Type A endpoint
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "User: {\"username\":\"admin\",\"is_admin\":true}" \
     http://localhost:8000/supabase/game-groups

# Test Type B endpoint  
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "User: {\"username\":\"admin\",\"is_admin\":true}" \
     http://localhost:8000/supabase/lesson-game-groups
```

### Bước 4: Kiểm tra Backend Logic
Xem log của SupabaseService:
```php
// Trong getLessonGameGroups() method
Log::debug('Fetching lesson game groups with params:', $params);
```

## 🎯 **Expected Results**

### Sau khi áp dụng migration:
- **Type A groups**: Hiển thị trong "Quản lý trò chơi" → "Quản lý nhóm trò chơi"  
- **Type B groups**: Hiển thị trong "Quản lý khoá học" → "Cài đặt trò chơi theo bài học"

### API Response mong muốn:
```json
// GET /supabase/game-groups (Type A)
[
  {
    "id": "uuid-1", 
    "name": "Nhóm A",
    "group_game_type": "A"
  }
]

// GET /supabase/lesson-game-groups (Type B)  
[
  {
    "id": "uuid-2",
    "name": "Nhóm B", 
    "group_game_type": "B"
  }
]
```

## 🚨 **Troubleshooting**

### Nếu vẫn thấy Type A groups trong lesson games:
1. **Hard refresh** trang (Ctrl+F5)
2. **Clear browser cache**
3. **Restart Laravel server**
4. **Check database connection**

### Nếu không có Type B groups nào:
1. Chưa có data Type B → Tạo nhóm mới
2. Migration chưa chạy → Apply SQL migration
3. Backend filter sai → Check SupabaseService code

### Error messages phổ biến:
- **"Column 'group_game_type' doesn't exist"** → Run migration
- **"No Type B groups found"** → Create test data  
- **"API returns empty array"** → Check database connection

## ✅ **Verification Checklist**

- [ ] Database có cột `group_game_type`
- [ ] Có ít nhất 1 group Type A và 1 group Type B
- [ ] API `/supabase/game-groups` chỉ trả về Type A
- [ ] API `/supabase/lesson-game-groups` chỉ trả về Type B  
- [ ] Frontend hiển thị đúng groups theo tab
- [ ] Dropdown chỉ hiển thị Type B groups trong lesson games
- [ ] Console không có error messages