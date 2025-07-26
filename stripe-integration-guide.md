# 🚀 Stripe Integration Guide for Laravel Learning Platform

## 📋 Overview
This guide will help you integrate Stripe subscription functionality into your Laravel learning platform using Laravel Cashier.

## 🛠️ Step 1: Install Required Packages

```bash
# Install Laravel Cashier (Stripe)
composer require laravel/cashier

# Install Stripe PHP SDK (if not included)
composer require stripe/stripe-php

# Install additional helpers
composer require spatie/laravel-permission
```

## 🔧 Step 2: Environment Configuration

Add these to your `.env` file:

```env
# Stripe Configuration
STRIPE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here

# Cashier Configuration
CASHIER_CURRENCY=usd
CASHIER_CURRENCY_LOCALE=en_US
```

## 📦 Step 3: Publish and Run Migrations

```bash
# Publish Cashier migrations
php artisan vendor:publish --tag="cashier-migrations"

# Run migrations
php artisan migrate
```

## 🏗️ Step 4: Update User Model

Update your `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    protected $fillable = [
        'username', // Keep your existing primary key
        'email',
        'password',
        'stripe_customer_id',
        'subscription_status',
        'subscription_ends_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_ends_at' => 'datetime',
        ];
    }

    // Relationships for your existing schema
    public function subscriptionPlans()
    {
        return $this->hasMany(UserSubscription::class, 'username', 'username');
    }

    public function badges()
    {
        return $this->hasMany(UserBadge::class, 'username', 'username');
    }

    public function progress()
    {
        return $this->hasMany(UserProgress::class, 'username', 'username');
    }

    // Subscription helper methods
    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'trialing']);
    }

    public function canAccessPremiumContent(): bool
    {
        return $this->hasActiveSubscription();
    }

    public function canAccessCourse($courseId): bool
    {
        // Check if course requires premium access
        $course = VideoCourse::find($courseId);
        if (!$course || !$course->is_premium) {
            return true; // Free content
        }
        
        return $this->hasActiveSubscription();
    }
}
```

## 🎯 Step 5: Create Models for Subscription Management

Create new models for the subscription system:

### SubscriptionPlan Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'description', 'price', 'currency',
        'billing_interval', 'stripe_product_id', 'features', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }
}
```

### UserSubscription Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserSubscription extends Model
{
    use HasUuids;

    protected $table = 'user_subscriptions';

    protected $fillable = [
        'username', 'stripe_customer_id', 'stripe_subscription_id',
        'plan_id', 'status', 'current_period_start', 'current_period_end',
        'trial_end', 'canceled_at', 'ended_at'
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_end' => 'datetime',
        'canceled_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }
}
```

## 🎮 Step 6: Create Subscription Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function plans()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        return view('subscription.plans', compact('plans'));
    }

    public function createCheckoutSession(Request $request)
    {
        $user = auth()->user();
        $planId = $request->input('plan_id');
        $plan = SubscriptionPlan::findOrFail($planId);

        // Create or retrieve Stripe customer
        if (!$user->stripe_customer_id) {
            $customer = \Stripe\Customer::create([
                'email' => $user->email,
                'name' => $user->username,
            ]);
            $user->update(['stripe_customer_id' => $customer->id]);
        }

        // Create Checkout Session
        $session = Session::create([
            'payment_method_types' => ['card'],
            'customer' => $user->stripe_customer_id,
            'line_items' => [[
                'price' => $plan->id, // This is the Stripe Price ID
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => route('subscription.success'),
            'cancel_url' => route('subscription.cancel'),
            'metadata' => [
                'username' => $user->username,
                'plan_id' => $plan->id,
            ],
        ]);

        return redirect($session->url);
    }

    public function success()
    {
        return view('subscription.success');
    }

    public function cancel()
    {
        return view('subscription.cancel');
    }

    public function portal()
    {
        $user = auth()->user();
        
        if (!$user->stripe_customer_id) {
            return redirect()->route('subscription.plans')
                ->with('error', 'You need an active subscription to access the billing portal.');
        }

        $session = \Stripe\BillingPortal\Session::create([
            'customer' => $user->stripe_customer_id,
            'return_url' => route('dashboard'),
        ]);

        return redirect($session->url);
    }
}
```

## 🔔 Step 7: Webhook Handler

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response('Webhook signature verification failed.', 400);
        }

        // Handle the event
        switch ($event['type']) {
            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event['data']['object']);
                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event['data']['object']);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event['data']['object']);
                break;
            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($event['data']['object']);
                break;
            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event['data']['object']);
                break;
        }

        return response('Success', 200);
    }

    private function handleSubscriptionCreated($subscription)
    {
        $user = User::where('stripe_customer_id', $subscription['customer'])->first();
        if (!$user) return;

        UserSubscription::create([
            'username' => $user->username,
            'stripe_customer_id' => $subscription['customer'],
            'stripe_subscription_id' => $subscription['id'],
            'plan_id' => $subscription['items']['data'][0]['price']['id'],
            'status' => $subscription['status'],
            'current_period_start' => date('Y-m-d H:i:s', $subscription['current_period_start']),
            'current_period_end' => date('Y-m-d H:i:s', $subscription['current_period_end']),
            'trial_end' => $subscription['trial_end'] ? date('Y-m-d H:i:s', $subscription['trial_end']) : null,
        ]);

        $user->update([
            'subscription_status' => $subscription['status'],
            'subscription_ends_at' => date('Y-m-d H:i:s', $subscription['current_period_end']),
        ]);
    }

    private function handleSubscriptionUpdated($subscription)
    {
        $userSub = UserSubscription::where('stripe_subscription_id', $subscription['id'])->first();
        if (!$userSub) return;

        $userSub->update([
            'status' => $subscription['status'],
            'current_period_start' => date('Y-m-d H:i:s', $subscription['current_period_start']),
            'current_period_end' => date('Y-m-d H:i:s', $subscription['current_period_end']),
            'canceled_at' => $subscription['canceled_at'] ? date('Y-m-d H:i:s', $subscription['canceled_at']) : null,
        ]);

        $userSub->user->update([
            'subscription_status' => $subscription['status'],
            'subscription_ends_at' => date('Y-m-d H:i:s', $subscription['current_period_end']),
        ]);
    }

    private function handleSubscriptionDeleted($subscription)
    {
        $userSub = UserSubscription::where('stripe_subscription_id', $subscription['id'])->first();
        if (!$userSub) return;

        $userSub->update([
            'status' => 'canceled',
            'ended_at' => now(),
        ]);

        $userSub->user->update([
            'subscription_status' => 'canceled',
        ]);
    }

    private function handlePaymentSucceeded($invoice)
    {
        // Log successful payment
        \Log::info('Payment succeeded', ['invoice' => $invoice['id']]);
    }

    private function handlePaymentFailed($invoice)
    {
        // Handle failed payment
        \Log::warning('Payment failed', ['invoice' => $invoice['id']]);
    }
}
```

## 🛡️ Step 8: Middleware for Premium Content

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, $feature = null)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscription.plans')
                ->with('error', 'This content requires an active subscription.');
        }

        // Check specific feature access if needed
        if ($feature && !$this->userCanAccessFeature($user, $feature)) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Your current plan does not include this feature.');
        }

        return $next($request);
    }

    private function userCanAccessFeature($user, $feature)
    {
        $subscription = $user->subscriptionPlans()->where('status', 'active')->first();
        if (!$subscription) return false;

        $planFeatures = $subscription->plan->features ?? [];
        return in_array($feature, $planFeatures);
    }
}
```

## 🔗 Step 9: Routes Configuration

Add to your `routes/web.php`:

```php
// Subscription routes
Route::middleware(['auth'])->group(function () {
    Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::post('/subscription/checkout', [SubscriptionController::class, 'createCheckoutSession'])->name('subscription.checkout');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/subscription/portal', [SubscriptionController::class, 'portal'])->name('subscription.portal');
});

// Webhook route (no auth middleware)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// Premium content routes
Route::middleware(['auth', 'subscription:premium_courses'])->group(function () {
    Route::get('/premium-courses', [CourseController::class, 'premiumIndex'])->name('courses.premium');
});
```

## 📱 Step 10: Frontend Views

### Subscription Plans View (`resources/views/subscription/plans.blade.php`)
```html
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Choose Your Plan</h1>
    
    <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto">
        @foreach($plans as $plan)
        <div class="bg-white rounded-lg shadow-lg p-6 {{ $plan->id === 'price_premium_monthly' ? 'border-2 border-blue-500' : '' }}">
            @if($plan->id === 'price_premium_monthly')
            <div class="bg-blue-500 text-white text-center py-2 px-4 rounded-t-lg -mt-6 -mx-6 mb-4">
                Most Popular
            </div>
            @endif
            
            <h3 class="text-xl font-bold mb-2">{{ $plan->name }}</h3>
            <p class="text-gray-600 mb-4">{{ $plan->description }}</p>
            
            <div class="text-3xl font-bold mb-4">
                {{ $plan->formatted_price }}
                <span class="text-sm text-gray-500">/ {{ $plan->billing_interval }}</span>
            </div>
            
            <ul class="mb-6 space-y-2">
                @foreach($plan->features as $feature)
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    {{ ucfirst(str_replace('_', ' ', $feature)) }}
                </li>
                @endforeach
            </ul>
            
            <form action="{{ route('subscription.checkout') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                    Get Started
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
```

## ⚙️ Step 11: Configuration Files

Update `config/services.php`:

```php
'stripe' => [
    'model' => App\Models\User::class,
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],
```

## 🚀 Step 12: Deployment Checklist

1. **Stripe Setup:**
   - Create Stripe account
   - Set up products and prices in Stripe Dashboard
   - Configure webhooks endpoint: `your-domain.com/stripe/webhook`
   - Enable required webhook events:
     - `customer.subscription.created`
     - `customer.subscription.updated`
     - `customer.subscription.deleted`
     - `invoice.payment_succeeded`
     - `invoice.payment_failed`

2. **Database Migration:**
   - Run the new subscription schema migrations
   - Update existing data if needed

3. **Environment Variables:**
   - Add all Stripe keys to production `.env`
   - Ensure webhook secret is configured

4. **Testing:**
   - Test subscription flow in Stripe test mode
   - Verify webhook handling
   - Test content access controls

## 🎯 Benefits of This Implementation

1. **Scalable Architecture:** Supports multiple subscription plans and features
2. **Stripe Integration:** Full Stripe Checkout and billing portal integration
3. **Content Access Control:** Granular control over premium content
4. **Webhook Handling:** Automatic subscription status updates
5. **User Experience:** Seamless subscription management
6. **Analytics Ready:** Payment history and subscription tracking
7. **Feature Gating:** Control access to specific platform features

## 📊 Usage Analytics

The schema includes tables to track:
- Payment history for financial reporting
- Feature usage for plan optimization
- Subscription lifecycle for churn analysis
- Content access patterns for product decisions

This implementation provides a robust foundation for your learning platform's subscription system while maintaining compatibility with your existing database structure.