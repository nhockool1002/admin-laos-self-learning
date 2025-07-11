# Hệ thống Authentication với SessionStorage

## Tổng quan

Hệ thống đã được chuyển đổi từ Laravel Session sang Browser SessionStorage để quản lý authentication. Điều này cho phép:

- Quản lý authentication ở client-side
- Không cần lưu trữ session trên server
- Tăng tính bảo mật và hiệu suất

## Cách hoạt động

### 1. Đăng nhập
- User nhập thông tin đăng nhập trên trang `/login`
- JavaScript gửi request POST đến `/login` với thông tin đăng nhập
- Server xác thực và trả về JSON response với:
  - `access_token`: Token để xác thực các request tiếp theo
  - `user`: Thông tin user (username, email, is_admin)
- JavaScript lưu thông tin vào sessionStorage:
  ```javascript
  sessionStorage.setItem('access_token', data.access_token);
  sessionStorage.setItem('user', JSON.stringify(data.user));
  ```

### 2. Kiểm tra Authentication
- Mỗi trang admin sẽ kiểm tra sessionStorage khi load
- Nếu không có token hoặc user không phải admin → redirect về `/login`
- Nếu hợp lệ → hiển thị trang admin

### 3. API Calls
- Tất cả API calls đều gửi kèm token trong header `Authorization`
- Server kiểm tra token để xác thực request
- Nếu token không hợp lệ → trả về 401 Unauthorized

### 4. Đăng xuất
- JavaScript gọi API `/logout`
- Xóa sessionStorage:
  ```javascript
  sessionStorage.removeItem('access_token');
  sessionStorage.removeItem('user');
  ```
- Redirect về trang login

## Các thay đổi chính

### AuthController
- Loại bỏ việc sử dụng Laravel session
- Trả về JSON response thay vì redirect
- Thêm method `checkAuth()` để kiểm tra authentication

### Views
- **login.blade.php**: Sử dụng JavaScript để xử lý form submit
- **admin.blade.php**: Đọc thông tin từ sessionStorage và hiển thị danh sách users

### Middleware
- `CheckAuth` middleware đã được đơn giản hóa
- Việc kiểm tra authentication chủ yếu được thực hiện ở client-side

### Routes
- Loại bỏ middleware `auth` khỏi các routes
- Thêm API endpoint `/check-auth` để kiểm tra authentication

## Bảo mật

### Ưu điểm
- Token được lưu trong sessionStorage (chỉ tồn tại trong tab hiện tại)
- Không lưu trữ session trên server
- Mỗi request đều được xác thực qua token

### Lưu ý
- Token hiện tại chỉ là base64 encoded string
- Trong production, nên sử dụng JWT hoặc token có thời hạn
- Cần implement token validation trên server-side

## Sử dụng

1. Truy cập `/login`
2. Nhập thông tin đăng nhập
3. Hệ thống sẽ tự động chuyển hướng đến trang admin nếu đăng nhập thành công
4. Trang admin sẽ hiển thị danh sách users từ Supabase
5. Click "Đăng xuất" để thoát khỏi hệ thống

## API Endpoints

- `POST /login`: Đăng nhập
- `GET /logout`: Đăng xuất  
- `GET /check-auth`: Kiểm tra authentication
- `GET /supabase/users`: Lấy danh sách users (yêu cầu authentication) 