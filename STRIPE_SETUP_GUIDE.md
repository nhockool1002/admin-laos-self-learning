# 🚀 Hướng dẫn Setup Stripe Payment System

## 📋 Tổng quan tích hợp

Hệ thống này được thiết kế để tích hợp Stripe vào Laravel backend và React frontend của bạn, cung cấp chức năng thành viên trả phí với 2 gói:

- **Monthly Premium**: $9.99/tháng
- **Yearly Premium**: $99.99/năm

## 🏗️ Kiến trúc hệ thống

```
Frontend (React) ↔ Backend API (Laravel) ↔ Stripe
     ↓                    ↓                  ↓
- Payment UI        - Subscription API    - Payment Processing
- User Dashboard    - Webhook Handler     - Customer Management
- Protected Routes  - Premium Middleware  - Billing Management
```

## 🛠️ Bước 1: Cài đặt Dependencies

### Backend (Laravel)
```bash
composer require stripe/stripe-php
```

### Frontend (React)
```bash
npm install @stripe/stripe-js
```

## 🗄️ Bước 2: Database Setup

### 1. Chạy Migrations
```bash
php artisan migrate
```

### 2. Seed dữ liệu mẫu
```bash
php artisan db:seed --class=SubscriptionPlanSeeder
```

### 3. Cấu trúc Database mới

#### Bảng `subscription_plans`
- Lưu thông tin các gói subscription (Monthly, Yearly)
- Chứa Stripe price IDs và thông tin giá cả

#### Bảng `user_subscriptions`
- Theo dõi subscription của từng user
- Lưu trạng thái, thời gian hết hạn, Stripe IDs

#### Bảng `users` (updated)
- Thêm fields: `stripe_customer_id`, `subscription_status`, `subscription_ends_at`

## 🔧 Bước 3: Cấu hình Stripe

### 1. Tạo Stripe Account
1. Đăng ký tại [stripe.com](https://stripe.com)
2. Tạo products và prices trong Stripe Dashboard
3. Lấy API keys (publishable và secret)

### 2. Cấu hình Environment
Thêm vào `.env`:
```env
STRIPE_KEY=pk_test_your_stripe_publishable_key_here
STRIPE_SECRET=sk_test_your_stripe_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
APP_FRONTEND_URL=http://localhost:3000
VITE_STRIPE_KEY="${STRIPE_KEY}"
```

### 3. Cập nhật Stripe Price IDs
```sql
UPDATE subscription_plans 
SET stripe_price_id = 'price_your_monthly_price_id' 
WHERE slug = 'monthly';

UPDATE subscription_plans 
SET stripe_price_id = 'price_your_yearly_price_id' 
WHERE slug = 'yearly';
```

## 🎣 Bước 4: Setup Webhooks

### 1. Tạo Webhook Endpoint trong Stripe Dashboard
- URL: `https://yourdomain.com/api/webhooks/stripe`
- Events to send:
  - `customer.subscription.created`
  - `customer.subscription.updated`
  - `customer.subscription.deleted`
  - `invoice.payment_succeeded`
  - `invoice.payment_failed`

### 2. Test Webhooks locally
```bash
# Install Stripe CLI
# Forward webhooks to local server
stripe listen --forward-to localhost:8000/api/webhooks/stripe
```

## 🚀 Bước 5: API Testing

### 1. Test lấy subscription plans
```bash
curl -X GET http://localhost:8000/api/v1/subscriptions/plans
```

### 2. Test tạo checkout session
```bash
curl -X POST http://localhost:8000/api/v1/subscriptions/checkout \
  -H "Content-Type: application/json" \
  -d '{
    "username": "test_user",
    "plan_id": 1,
    "success_url": "http://localhost:3000/success",
    "cancel_url": "http://localhost:3000/cancel"
  }'
```

### 3. Test kiểm tra subscription status
```bash
curl -X GET http://localhost:8000/api/v1/subscriptions/user/test_user
```

## 🎨 Bước 6: Frontend Integration

### 1. Tạo các React Components

```jsx
// App.js
import SubscriptionPlans from './components/SubscriptionPlans';
import SubscriptionStatus from './components/SubscriptionStatus';
import PremiumContent from './components/PremiumContent';

function App() {
  const username = 'current_user'; // Lấy từ auth context

  return (
    <div>
      <SubscriptionStatus username={username} />
      <SubscriptionPlans username={username} />
      
      <PremiumContent username={username}>
        <h2>Premium Content Here</h2>
        <p>Only visible to premium members</p>
      </PremiumContent>
    </div>
  );
}
```

### 2. Setup Environment Variables
```env
# .env.local
REACT_APP_API_URL=http://localhost:8000/api
REACT_APP_STRIPE_KEY=pk_test_your_stripe_publishable_key_here
```

### 3. Test Frontend Flow
1. Hiển thị subscription plans
2. Redirect đến Stripe Checkout
3. Handle success/cancel redirects
4. Hiển thị subscription status
5. Protect premium content

## 🔒 Bước 7: Security & Production Setup

### 1. Middleware Setup
Middleware `CheckSubscription` đã được tạo để bảo vệ premium routes:

```php
// Trong routes/api.php
Route::middleware([\App\Http\Middleware\CheckSubscription::class])->group(function () {
    Route::get('/v1/premium/courses', [PremiumController::class, 'index']);
});
```

### 2. CORS Configuration
Đảm bảo CORS được cấu hình đúng cho frontend domain.

### 3. SSL Certificates
- Bắt buộc HTTPS cho production
- Stripe webhooks yêu cầu HTTPS

## 🧪 Bước 8: Testing

### 1. Stripe Test Cards
```javascript
const testCards = {
  success: '4242424242424242',
  decline: '4000000000000002',
  insufficient_funds: '4000000000009995',
  expired: '4000000000000069'
};
```

### 2. Test Scenarios
- [ ] Successful subscription creation
- [ ] Failed payment handling
- [ ] Subscription cancellation
- [ ] Subscription resumption
- [ ] Plan upgrades/downgrades
- [ ] Webhook event processing
- [ ] Premium content access
- [ ] Expired subscription handling

## 🚀 Bước 9: Deployment

### 1. Production Environment
```env
# Production .env
STRIPE_KEY=pk_live_your_live_publishable_key
STRIPE_SECRET=sk_live_your_live_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_live_webhook_secret
APP_FRONTEND_URL=https://yourdomain.com
```

### 2. Server Configuration
- Setup SSL certificates
- Configure webhook endpoints
- Set up monitoring and logging
- Configure backup systems

### 3. Stripe Live Mode
- Switch to live keys
- Test production payment flow
- Monitor webhook delivery

## 📊 Bước 10: Monitoring & Maintenance

### 1. Artisan Commands
```bash
# Sync subscriptions từ Stripe
php artisan stripe:sync-subscriptions

# Đặt trong crontab để chạy hàng ngày
0 2 * * * php /path/to/artisan stripe:sync-subscriptions
```

### 2. Logging & Monitoring
- Monitor webhook delivery trong Stripe Dashboard
- Set up alerts cho failed payments
- Track subscription metrics

### 3. Regular Maintenance
- Kiểm tra expired subscriptions
- Sync data với Stripe định kỳ
- Monitor failed webhook deliveries

## 🆘 Troubleshooting

### Common Issues

1. **Webhook không nhận được**
   - Kiểm tra URL endpoint
   - Verify webhook secret
   - Check server logs

2. **Payment failed**
   - Kiểm tra Stripe keys
   - Verify test card numbers
   - Check network connectivity

3. **Subscription status không sync**
   - Chạy `php artisan stripe:sync-subscriptions`
   - Kiểm tra webhook logs
   - Verify database connections

### Debug Commands
```bash
# Check webhook logs
tail -f storage/logs/laravel.log | grep "Stripe webhook"

# Test Stripe connection
php artisan tinker
>>> \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
>>> \Stripe\Account::retrieve();
```

## 📚 Resources

- [Stripe Documentation](https://stripe.com/docs)
- [Laravel Cashier](https://laravel.com/docs/billing) (alternative solution)
- [Stripe CLI](https://stripe.com/docs/stripe-cli)
- [Webhook Testing](https://stripe.com/docs/webhooks/test)

---

**✅ Checklist hoàn thành:**
- [ ] Database migrations và seeds
- [ ] Stripe account và products setup
- [ ] Environment configuration
- [ ] Webhook endpoints
- [ ] API testing
- [ ] Frontend integration
- [ ] Security middleware
- [ ] Testing với test cards
- [ ] Production deployment
- [ ] Monitoring setup

**🎯 Kết quả cuối cùng:** Hệ thống subscription hoàn chỉnh với Stripe payment, bảo vệ premium content, và webhook handling tự động.