# 🚀 Hướng Dẫn Tích Hợp Stripe Cho Hệ Thống Thành Viên Trả Phí

## 📋 Tổng Quan

Hướng dẫn này sẽ giúp bạn tích hợp Stripe vào Laravel learning platform với database hiện tại, bao gồm:
- Quản lý gói đăng ký (subscription plans)
- Xử lý thanh toán tự động
- Kiểm soát truy cập nội dung premium
- Theo dõi lịch sử thanh toán
- Webhook handling cho đồng bộ dữ liệu

## 🎯 Phân Tích Database Hiện Tại

### Điểm mạnh của database hiện tại:
✅ **Cấu trúc tốt**: Có hệ thống user, course, progress tracking, gamification  
✅ **Khả năng mở rộng**: Dễ dàng thêm premium features  
✅ **Quan hệ rõ ràng**: Foreign keys và indexes đã được thiết kế tốt  

### Những gì cần bổ sung:
🔄 **User model**: Cập nhật để sử dụng `username` làm primary key  
➕ **Subscription tables**: Thêm bảng quản lý đăng ký và thanh toán  
🔒 **Access control**: Kiểm soát nội dung premium  
📊 **Analytics**: Theo dõi usage và revenue  

## 🛠️ Bước 1: Cài Đặt Dependencies

```bash
# Cài đặt Laravel Cashier và Stripe
composer require laravel/cashier
composer require stripe/stripe-php

# Publish Cashier migrations (tùy chọn)
php artisan vendor:publish --tag="cashier-migrations"
```

## ⚙️ Bước 2: Cấu Hình Environment

Thêm vào file `.env`:

```env
# Stripe Configuration
STRIPE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here

# Currency Settings
CASHIER_CURRENCY=vnd
CASHIER_CURRENCY_LOCALE=vi_VN
```

## 🗄️ Bước 3: Chạy Migrations

```bash
# Chạy các migration đã tạo
php artisan migrate

# Seed subscription plans
php artisan db:seed --class=SubscriptionPlansSeeder
```

## 🏗️ Bước 4: Cấu Hình Routes

Thêm vào `routes/web.php`:

```php
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\StripeWebhookController;

// Subscription routes (cần auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::post('/subscription/checkout', [SubscriptionController::class, 'createCheckoutSession'])->name('subscription.checkout');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/subscription/manage', [SubscriptionController::class, 'manage'])->name('subscription.manage');
    Route::get('/subscription/portal', [SubscriptionController::class, 'portal'])->name('subscription.portal');
    Route::post('/subscription/cancel-subscription', [SubscriptionController::class, 'cancelSubscription'])->name('subscription.cancel-subscription');
    Route::post('/subscription/resume', [SubscriptionController::class, 'resumeSubscription'])->name('subscription.resume');
    Route::post('/subscription/change-plan', [SubscriptionController::class, 'changePlan'])->name('subscription.change-plan');
});

// Webhook route (không cần auth)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

// Premium content routes
Route::middleware(['auth', 'subscription:premium_courses'])->group(function () {
    Route::get('/premium-courses', [CourseController::class, 'premiumIndex'])->name('courses.premium');
    Route::get('/premium-games', [GameController::class, 'premiumIndex'])->name('games.premium');
});
```

## 🔧 Bước 5: Đăng Ký Middleware

Thêm vào `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... existing middleware
    'subscription' => \App\Http\Middleware\CheckSubscription::class,
];
```

## 💳 Bước 6: Thiết Lập Stripe Dashboard

### 6.1 Tạo Products và Prices
1. Đăng nhập Stripe Dashboard
2. Tạo Products:
   - **Gói Cơ Bản**: `prod_basic`
   - **Gói Premium**: `prod_premium`
   - **Gói Doanh Nghiệp**: `prod_enterprise`

3. Tạo Prices cho mỗi product:
   - `price_basic_monthly` → 99,000 VND/tháng
   - `price_premium_monthly` → 199,000 VND/tháng
   - `price_premium_yearly` → 1,900,000 VND/năm
   - `price_enterprise_monthly` → 499,000 VND/tháng

### 6.2 Cấu Hình Webhooks
1. Tạo webhook endpoint: `https://yourdomain.com/stripe/webhook`
2. Chọn events:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
   - `customer.subscription.trial_will_end`

## 🎨 Bước 7: Tạo Views (Tùy chọn - có thể tùy chỉnh)

### 7.1 Subscription Plans View

```html
<!-- resources/views/subscription/plans.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Chọn Gói Đăng Ký</h1>
    
    @if($currentSubscription)
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
            <strong>Gói hiện tại:</strong> {{ $currentSubscription->plan->name }}
            <span class="float-right">
                <a href="{{ route('subscription.manage') }}" class="text-blue-600 hover:underline">
                    Quản lý đăng ký
                </a>
            </span>
        </div>
    @endif
    
    <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto">
        @foreach($plans as $plan)
        <div class="bg-white rounded-lg shadow-lg p-6 {{ $plan->id === 'price_premium_monthly' ? 'border-2 border-blue-500' : '' }}">
            @if($plan->id === 'price_premium_monthly')
            <div class="bg-blue-500 text-white text-center py-2 px-4 rounded-t-lg -mt-6 -mx-6 mb-4">
                Phổ Biến Nhất
            </div>
            @endif
            
            <h3 class="text-xl font-bold mb-2">{{ $plan->name }}</h3>
            <p class="text-gray-600 mb-4">{{ $plan->description }}</p>
            
            <div class="text-3xl font-bold mb-4">
                {{ number_format($plan->price) }} ₫
                <span class="text-sm text-gray-500">/ {{ $plan->billing_interval === 'month' ? 'tháng' : 'năm' }}</span>
            </div>
            
            <ul class="mb-6 space-y-2">
                @foreach($plan->features as $feature)
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $this->translateFeature($feature) }}
                </li>
                @endforeach
            </ul>
            
            @if(!$currentSubscription || $currentSubscription->plan_id !== $plan->id)
            <form action="{{ route('subscription.checkout') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                    Đăng Ký Ngay
                </button>
            </form>
            @else
            <button disabled class="w-full bg-gray-400 text-white py-2 px-4 rounded-lg">
                Gói Hiện Tại
            </button>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
```

## 🔒 Bước 8: Kiểm Soát Truy Cập Premium Content

### 8.1 Trong Controllers

```php
// CourseController.php
public function show($id)
{
    $course = VideoCourse::findOrFail($id);
    
    // Kiểm tra quyền truy cập
    if ($course->is_premium && !auth()->user()->canAccessCourse($id)) {
        return redirect()->route('subscription.plans')
            ->with('error', 'Khóa học này yêu cầu gói đăng ký Premium.');
    }
    
    return view('courses.show', compact('course'));
}
```

### 8.2 Trong Blade Templates

```html
@if($course->is_premium && !auth()->user()->canAccessCourse($course->id))
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
        <strong>Nội dung Premium!</strong> 
        <a href="{{ route('subscription.plans') }}" class="text-blue-600 hover:underline">
            Nâng cấp để truy cập
        </a>
    </div>
@else
    <!-- Hiển thị nội dung khóa học -->
@endif
```

## 📊 Bước 9: Theo Dõi và Analytics

### 9.1 Revenue Tracking

```php
// Tổng doanh thu tháng này
$monthlyRevenue = PaymentHistory::successful()
    ->thisMonth()
    ->sum('amount');

// Số lượng subscriber active
$activeSubscribers = User::where('subscription_status', 'active')->count();
```

### 9.2 Usage Analytics

```php
// Track feature usage
public function downloadVideo(Request $request)
{
    $user = auth()->user();
    
    if (!$user->canUseFeature('video_downloads')) {
        return response()->json(['error' => 'Feature not available in your plan'], 403);
    }
    
    // Increment usage count
    $feature = UserPlanFeature::firstOrCreate([
        'username' => $user->username,
        'feature_name' => 'video_downloads'
    ]);
    
    if (!$feature->incrementUsage()) {
        return response()->json(['error' => 'Usage limit exceeded'], 403);
    }
    
    // Process download...
}
```

## 🚀 Bước 10: Deployment Checklist

### 10.1 Stripe Configuration
- [ ] Tạo Stripe account production
- [ ] Cấu hình Products và Prices trong production
- [ ] Thiết lập webhook endpoint production
- [ ] Cập nhật environment variables production

### 10.2 Database
- [ ] Chạy migrations trên production
- [ ] Seed subscription plans
- [ ] Backup database trước khi deploy

### 10.3 Testing
- [ ] Test subscription flow với test cards
- [ ] Verify webhook handling
- [ ] Test content access controls
- [ ] Test cancellation và resume

## 💡 Lợi Ích Của Implementation Này

### 🔧 Kỹ Thuật:
- **Scalable**: Hỗ trợ multiple plans và features
- **Secure**: Webhook signature verification
- **Reliable**: Error handling và logging
- **Maintainable**: Clean code structure

### 💼 Kinh Doanh:
- **Flexible Pricing**: Nhiều gói phù hợp với user khác nhau
- **Feature Gating**: Kiểm soát tính năng theo từng gói
- **Analytics**: Theo dõi revenue và usage
- **Customer Experience**: Self-service subscription management

### 🎯 User Experience:
- **Seamless**: Stripe Checkout đơn giản
- **Transparent**: Billing portal tự quản lý
- **Responsive**: Real-time status updates
- **Localized**: Giao diện tiếng Việt

## 🔧 Customization Guidelines

### Thêm Tính Năng Mới:
1. Thêm feature vào `subscription_plans.features`
2. Cập nhật middleware kiểm tra
3. Implement logic trong controllers
4. Update UI components

### Thay Đổi Pricing:
1. Tạo Price mới trong Stripe
2. Cập nhật seeder
3. Run migration/seeder
4. Test upgrade flow

### Localization:
- Sử dụng Laravel localization cho messages
- Customize Stripe Checkout text
- Update email templates

## 🎉 Kết Luận

Implementation này cung cấp:
- **Foundation vững chắc** cho subscription system
- **Integration liền mạch** với database hiện tại  
- **Flexibility cao** để customize theo nhu cầu
- **Production-ready** với error handling và security

Bạn có thể bắt đầu với Basic plan và Premium plan, sau đó mở rộng thêm Enterprise features khi cần thiết.