# 🔧 Installation Commands - Stripe Integration

## ❌ Lỗi hiện tại
```
Required package "stripe/stripe-php" is not present in the lock file.
```

## 🔄 Giải pháp

### Bước 1: Xóa composer.lock cũ và reinstall
```bash
# Xóa lock file cũ
rm composer.lock

# Xóa thư mục vendor (nếu có)
rm -rf vendor/

# Chạy composer install để tạo lock file mới
composer install --no-interaction
```

### Bước 2: Hoặc chạy composer update
```bash
# Update để sync với composer.json mới
composer update --no-interaction

# Hoặc update chỉ package cần thiết
composer update stripe/stripe-php --no-interaction
```

### Bước 3: Verify installation
```bash
# Kiểm tra package đã được cài đặt
composer show stripe/stripe-php

# Kiểm tra autoload
composer dump-autoload
```

## 🚀 Các bước setup đầy đủ

### 1. Install Dependencies
```bash
# Backend - Laravel
composer install --no-interaction
composer require stripe/stripe-php

# Frontend - React (nếu cần)
npm install @stripe/stripe-js
```

### 2. Database Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed subscription plans
php artisan db:seed --class=SubscriptionPlanSeeder
```

### 3. Environment Configuration
Cập nhật file `.env` với Stripe keys:
```env
STRIPE_KEY=pk_test_your_stripe_publishable_key_here
STRIPE_SECRET=sk_test_your_stripe_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
APP_FRONTEND_URL=http://localhost:3000
VITE_STRIPE_KEY="${STRIPE_KEY}"
```

### 4. Test Installation
```bash
# Test Stripe service
php artisan tinker
>>> use App\Services\StripeService;
>>> $service = new StripeService();
>>> echo "Stripe service loaded successfully";

# Test routes
php artisan route:list | grep subscription
```

### 5. Start Development Server
```bash
# Start Laravel server
php artisan serve

# In another terminal, start frontend (if applicable)
npm run dev
```

## 🧪 Testing Commands

### Test API Endpoints
```bash
# Test subscription plans endpoint
curl -X GET http://localhost:8000/api/v1/subscriptions/plans

# Test user subscription status
curl -X GET http://localhost:8000/api/v1/subscriptions/user/test_user

# Test checkout session creation
curl -X POST http://localhost:8000/api/v1/subscriptions/checkout \
  -H "Content-Type: application/json" \
  -d '{
    "username": "test_user",
    "plan_id": 1,
    "success_url": "http://localhost:3000/success",
    "cancel_url": "http://localhost:3000/cancel"
  }'
```

### Test Webhooks Locally
```bash
# Install Stripe CLI (if not installed)
# On macOS: brew install stripe/stripe-cli/stripe
# On Ubuntu: 
# curl -s https://packages.stripe.com/api/security/keypairs/stripe-cli-gpg/public | gpg --dearmor | sudo tee /usr/share/keyrings/stripe.gpg
# echo "deb [signed-by=/usr/share/keyrings/stripe.gpg] https://packages.stripe.com/stripe-cli-debian-local stable main" | sudo tee -a /etc/apt/sources.list.d/stripe.list
# sudo apt update && sudo apt install stripe

# Login to Stripe
stripe login

# Forward webhooks to local server
stripe listen --forward-to localhost:8000/api/webhooks/stripe
```

## 🔍 Troubleshooting

### If composer update fails:
```bash
# Clear composer cache
composer clear-cache

# Diagnose composer issues
composer diagnose

# Update composer itself
composer self-update
```

### If migration fails:
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Reset and re-run migrations
php artisan migrate:reset
php artisan migrate
```

### If Stripe connection fails:
```bash
# Test Stripe keys
php artisan tinker
>>> \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
>>> \Stripe\Account::retrieve();
```

## 📝 Files Created/Modified

### New Files:
- `database/migrations/2024_01_01_000001_create_subscription_plans_table.php`
- `database/migrations/2024_01_01_000002_create_user_subscriptions_table.php`
- `database/migrations/2024_01_01_000003_add_stripe_fields_to_users_table.php`
- `app/Models/SubscriptionPlan.php`
- `app/Models/UserSubscription.php`
- `app/Services/StripeService.php`
- `app/Http/Controllers/SubscriptionController.php`
- `app/Http/Controllers/WebhookController.php`
- `app/Http/Middleware/CheckSubscription.php`
- `app/Console/Commands/SyncStripeSubscriptions.php`
- `database/seeders/SubscriptionPlanSeeder.php`
- `routes/api.php`

### Modified Files:
- `composer.json` (added stripe/stripe-php)
- `config/services.php` (added Stripe config)
- `app/Models/User.php` (added subscription methods)
- `.env.example` (added Stripe environment variables)
- `routes/web.php` (included API routes)

## ✅ Verification Checklist

After running all commands, verify:

- [ ] Composer packages installed successfully
- [ ] Database migrations completed
- [ ] Subscription plans seeded
- [ ] API routes accessible
- [ ] Stripe service can connect
- [ ] Webhook endpoint responds
- [ ] Frontend can call APIs
- [ ] Test payment flow works

## 🆘 If You Still Have Issues

1. **Check PHP version**: `php -v` (requires PHP 8.2+)
2. **Check Laravel version**: `php artisan --version`
3. **Check database connection**: Edit `.env` with correct DB credentials
4. **Check file permissions**: `chmod -R 775 storage bootstrap/cache`
5. **Clear all caches**: 
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

Contact support if you encounter any issues after following these steps!