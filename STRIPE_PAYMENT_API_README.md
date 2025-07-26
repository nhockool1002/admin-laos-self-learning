# 💳 Stripe Payment Integration API Documentation

## 📋 Tổng quan

Hệ thống thanh toán Stripe được tích hợp để hỗ trợ gói thành viên trả phí với 2 lựa chọn:
- **Monthly Premium**: $9.99/tháng
- **Yearly Premium**: $99.99/năm (tiết kiệm 2 tháng)

## 🔧 Cài đặt và Cấu hình

### 1. Backend Laravel Setup

```bash
# Install Stripe PHP SDK
composer require stripe/stripe-php

# Run migrations
php artisan migrate

# Seed subscription plans
php artisan db:seed --class=SubscriptionPlanSeeder
```

### 2. Environment Configuration

Thêm vào file `.env`:

```env
# Stripe Configuration
STRIPE_KEY=pk_test_your_stripe_publishable_key_here
STRIPE_SECRET=sk_test_your_stripe_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here

# Frontend URL for redirect after payment
APP_FRONTEND_URL=http://localhost:3000

# Stripe for frontend
VITE_STRIPE_KEY="${STRIPE_KEY}"
```

### 3. Stripe Dashboard Setup

1. Tạo Products và Prices trong Stripe Dashboard:
   - **Monthly Premium**: recurring monthly
   - **Yearly Premium**: recurring yearly

2. Cập nhật `stripe_price_id` trong bảng `subscription_plans`

3. Thiết lập Webhook endpoint: `https://yourdomain.com/api/webhooks/stripe`

4. Subscribe các events:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`

## 🚀 API Endpoints

### Base URL
```
https://yourdomain.com/api/v1/subscriptions
```

### 1. Lấy danh sách gói subscription

**GET** `/plans`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Premium Monthly",
      "slug": "monthly",
      "stripe_price_id": "price_monthly_premium",
      "price": "9.99",
      "currency": "USD",
      "billing_period": "month",
      "description": "Premium access with monthly billing",
      "features": [
        "Access to all premium content",
        "Ad-free experience",
        "Priority support",
        "Download for offline viewing",
        "Access to exclusive courses"
      ],
      "formatted_price": "9.99 USD"
    },
    {
      "id": 2,
      "name": "Premium Yearly",
      "slug": "yearly",
      "stripe_price_id": "price_yearly_premium", 
      "price": "99.99",
      "currency": "USD",
      "billing_period": "year",
      "description": "Premium access with yearly billing (2 months free)",
      "features": [
        "Access to all premium content",
        "Ad-free experience", 
        "Priority support",
        "Download for offline viewing",
        "Access to exclusive courses",
        "2 months free compared to monthly plan"
      ],
      "formatted_price": "99.99 USD"
    }
  ]
}
```

### 2. Tạo Checkout Session

**POST** `/checkout`

**Request Body:**
```json
{
  "username": "john_doe",
  "plan_id": 1,
  "success_url": "http://localhost:3000/subscription/success",
  "cancel_url": "http://localhost:3000/subscription/cancel"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "checkout_url": "https://checkout.stripe.com/pay/cs_test_...",
    "session_id": "cs_test_..."
  }
}
```

### 3. Kiểm tra trạng thái subscription của user

**GET** `/user/{username}`

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "username": "john_doe",
      "email": "john@example.com",
      "subscription_status": "active",
      "subscription_ends_at": "2024-12-01T10:00:00.000000Z",
      "days_remaining": 25,
      "is_premium": true
    },
    "subscription": {
      "id": 1,
      "plan": {
        "id": 1,
        "name": "Premium Monthly",
        "billing_period": "month",
        "price": "9.99"
      },
      "status": "active",
      "current_period_start": "2024-11-01T10:00:00.000000Z",
      "current_period_end": "2024-12-01T10:00:00.000000Z",
      "days_remaining": 25
    }
  }
}
```

### 4. Hủy subscription

**POST** `/cancel`

**Request Body:**
```json
{
  "username": "john_doe"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription canceled successfully"
}
```

### 5. Khôi phục subscription

**POST** `/resume`

**Request Body:**
```json
{
  "username": "john_doe"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription resumed successfully"
}
```

### 6. Thay đổi gói subscription

**POST** `/change-plan`

**Request Body:**
```json
{
  "username": "john_doe",
  "new_plan_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription plan changed successfully"
}
```

### 7. Lấy thông tin hóa đơn sắp tới

**GET** `/invoice/{username}`

**Response:**
```json
{
  "success": true,
  "data": {
    "amount_due": 999,
    "currency": "usd",
    "period_start": 1701421200,
    "period_end": 1704099600
  }
}
```

## 🎨 Frontend ReactJS Integration

### 1. Install Stripe.js

```bash
npm install @stripe/stripe-js
```

### 2. Subscription Plans Component

```jsx
// components/SubscriptionPlans.jsx
import React, { useState, useEffect } from 'react';
import { loadStripe } from '@stripe/stripe-js';

const stripePromise = loadStripe(process.env.REACT_APP_STRIPE_KEY);

function SubscriptionPlans({ username }) {
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchPlans();
  }, []);

  const fetchPlans = async () => {
    try {
      const response = await fetch('/api/v1/subscriptions/plans');
      const data = await response.json();
      if (data.success) {
        setPlans(data.data);
      }
    } catch (error) {
      console.error('Error fetching plans:', error);
    }
  };

  const handleSubscribe = async (planId) => {
    setLoading(true);
    try {
      const response = await fetch('/api/v1/subscriptions/checkout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          username: username,
          plan_id: planId,
          success_url: `${window.location.origin}/subscription/success`,
          cancel_url: `${window.location.origin}/subscription/cancel`
        }),
      });

      const data = await response.json();
      
      if (data.success) {
        // Redirect to Stripe Checkout
        window.location.href = data.data.checkout_url;
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error('Error creating checkout session:', error);
      alert('Failed to create checkout session');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="subscription-plans">
      <h2>Choose Your Plan</h2>
      <div className="plans-grid">
        {plans.map((plan) => (
          <div key={plan.id} className="plan-card">
            <h3>{plan.name}</h3>
            <div className="price">
              ${plan.price}/{plan.billing_period}
            </div>
            <p>{plan.description}</p>
            <ul>
              {plan.features.map((feature, index) => (
                <li key={index}>{feature}</li>
              ))}
            </ul>
            <button
              onClick={() => handleSubscribe(plan.id)}
              disabled={loading}
              className="subscribe-btn"
            >
              {loading ? 'Processing...' : 'Subscribe Now'}
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

export default SubscriptionPlans;
```

### 3. Subscription Status Component

```jsx
// components/SubscriptionStatus.jsx
import React, { useState, useEffect } from 'react';

function SubscriptionStatus({ username }) {
  const [subscription, setSubscription] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchSubscriptionStatus();
  }, [username]);

  const fetchSubscriptionStatus = async () => {
    try {
      const response = await fetch(`/api/v1/subscriptions/user/${username}`);
      const data = await response.json();
      
      if (data.success) {
        setSubscription(data.data);
      }
    } catch (error) {
      console.error('Error fetching subscription status:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = async () => {
    if (!window.confirm('Are you sure you want to cancel your subscription?')) {
      return;
    }

    try {
      const response = await fetch('/api/v1/subscriptions/cancel', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username }),
      });

      const data = await response.json();
      
      if (data.success) {
        alert('Subscription canceled successfully');
        fetchSubscriptionStatus(); // Refresh data
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error('Error canceling subscription:', error);
      alert('Failed to cancel subscription');
    }
  };

  const handleResume = async () => {
    try {
      const response = await fetch('/api/v1/subscriptions/resume', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username }),
      });

      const data = await response.json();
      
      if (data.success) {
        alert('Subscription resumed successfully');
        fetchSubscriptionStatus(); // Refresh data
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error('Error resuming subscription:', error);
      alert('Failed to resume subscription');
    }
  };

  if (loading) {
    return <div>Loading subscription status...</div>;
  }

  if (!subscription || !subscription.user.is_premium) {
    return (
      <div className="no-subscription">
        <h3>No Active Subscription</h3>
        <p>Subscribe to access premium content!</p>
      </div>
    );
  }

  return (
    <div className="subscription-status">
      <h3>Your Subscription</h3>
      <div className="status-info">
        <p><strong>Plan:</strong> {subscription.subscription.plan.name}</p>
        <p><strong>Status:</strong> {subscription.user.subscription_status}</p>
        <p><strong>Expires:</strong> {new Date(subscription.user.subscription_ends_at).toLocaleDateString()}</p>
        <p><strong>Days Remaining:</strong> {subscription.user.days_remaining}</p>
      </div>
      
      <div className="actions">
        {subscription.user.subscription_status === 'active' && (
          <button onClick={handleCancel} className="cancel-btn">
            Cancel Subscription
          </button>
        )}
        
        {subscription.user.subscription_status === 'canceled' && (
          <button onClick={handleResume} className="resume-btn">
            Resume Subscription
          </button>
        )}
      </div>
    </div>
  );
}

export default SubscriptionStatus;
```

### 4. Protected Content Hook

```jsx
// hooks/useSubscription.js
import { useState, useEffect } from 'react';

export function useSubscription(username) {
  const [isPremium, setIsPremium] = useState(false);
  const [loading, setLoading] = useState(true);
  const [subscription, setSubscription] = useState(null);

  useEffect(() => {
    if (!username) return;

    const checkSubscription = async () => {
      try {
        const response = await fetch(`/api/v1/subscriptions/user/${username}`);
        const data = await response.json();
        
        if (data.success) {
          setIsPremium(data.data.user.is_premium);
          setSubscription(data.data);
        }
      } catch (error) {
        console.error('Error checking subscription:', error);
        setIsPremium(false);
      } finally {
        setLoading(false);
      }
    };

    checkSubscription();
  }, [username]);

  return { isPremium, loading, subscription };
}
```

### 5. Protected Route Component

```jsx
// components/PremiumContent.jsx
import React from 'react';
import { useSubscription } from '../hooks/useSubscription';
import SubscriptionPlans from './SubscriptionPlans';

function PremiumContent({ username, children }) {
  const { isPremium, loading } = useSubscription(username);

  if (loading) {
    return <div>Checking subscription...</div>;
  }

  if (!isPremium) {
    return (
      <div className="premium-required">
        <h2>Premium Subscription Required</h2>
        <p>This content is only available for premium members.</p>
        <SubscriptionPlans username={username} />
      </div>
    );
  }

  return children;
}

export default PremiumContent;
```

### 6. Success/Cancel Pages

```jsx
// pages/SubscriptionSuccess.jsx
import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

function SubscriptionSuccess() {
  const navigate = useNavigate();

  useEffect(() => {
    // Optional: Verify payment success with backend
    // Then redirect to dashboard after 3 seconds
    const timer = setTimeout(() => {
      navigate('/dashboard');
    }, 3000);

    return () => clearTimeout(timer);
  }, [navigate]);

  return (
    <div className="success-page">
      <h1>🎉 Subscription Successful!</h1>
      <p>Thank you for subscribing! You now have access to premium content.</p>
      <p>Redirecting to dashboard in 3 seconds...</p>
    </div>
  );
}

export default SubscriptionSuccess;
```

```jsx
// pages/SubscriptionCancel.jsx
import React from 'react';
import { Link } from 'react-router-dom';

function SubscriptionCancel() {
  return (
    <div className="cancel-page">
      <h1>Subscription Canceled</h1>
      <p>Your subscription was not completed. You can try again anytime.</p>
      <Link to="/subscription" className="retry-btn">
        Try Again
      </Link>
    </div>
  );
}

export default SubscriptionCancel;
```

## 🔒 Security & Best Practices

### 1. Webhook Security
- Always verify webhook signatures
- Use HTTPS for webhook endpoints
- Log all webhook events for debugging

### 2. Frontend Security
- Never expose secret keys in frontend
- Validate user permissions on every API call
- Use secure session management

### 3. Error Handling
- Implement proper error boundaries
- Provide clear error messages to users
- Log errors for monitoring

## 🧪 Testing

### 1. Stripe Test Cards

```javascript
// Test Cards
const testCards = {
  success: '4242424242424242',
  decline: '4000000000000002',
  insufficient_funds: '4000000000009995',
  expired: '4000000000000069'
};
```

### 2. Test Webhooks
Use Stripe CLI for local webhook testing:

```bash
stripe listen --forward-to localhost:8000/api/webhooks/stripe
```

## 📊 Monitoring & Analytics

### 1. Key Metrics to Track
- Subscription conversion rate
- Churn rate
- Revenue growth
- Failed payments

### 2. Stripe Dashboard
Monitor all payment activities, customer data, and revenue metrics in Stripe Dashboard.

## 🚀 Deployment Checklist

- [ ] Set production Stripe keys
- [ ] Configure webhook endpoints
- [ ] Test payment flow end-to-end
- [ ] Set up monitoring and alerts
- [ ] Configure SSL certificates
- [ ] Set up backup and recovery

---

**Lưu ý:** Thay thế tất cả test keys bằng live keys khi deploy production và đảm bảo tất cả webhooks được cấu hình đúng.