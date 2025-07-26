-- Stripe Subscription Schema for Laravel Learning Platform
-- This schema extends the existing database for paid membership functionality

-- 1. Subscription Plans Table
CREATE TABLE subscription_plans (
    id VARCHAR(255) PRIMARY KEY, -- Stripe Price ID
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    billing_interval ENUM('month', 'year') NOT NULL,
    stripe_product_id VARCHAR(255) NOT NULL, -- Stripe Product ID
    features JSON, -- List of features included in this plan
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. User Subscriptions Table
CREATE TABLE user_subscriptions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    username VARCHAR(255) NOT NULL,
    stripe_customer_id VARCHAR(255) NOT NULL,
    stripe_subscription_id VARCHAR(255) UNIQUE,
    plan_id VARCHAR(255) NOT NULL,
    status ENUM('incomplete', 'incomplete_expired', 'trialing', 'active', 'past_due', 'canceled', 'unpaid') NOT NULL,
    current_period_start TIMESTAMP,
    current_period_end TIMESTAMP,
    trial_end TIMESTAMP NULL,
    canceled_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    
    INDEX idx_user_subscriptions_username (username),
    INDEX idx_user_subscriptions_stripe_customer (stripe_customer_id),
    INDEX idx_user_subscriptions_status (status)
);

-- 3. Payment History Table
CREATE TABLE payment_history (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    username VARCHAR(255) NOT NULL,
    subscription_id UUID,
    stripe_payment_intent_id VARCHAR(255),
    stripe_invoice_id VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('pending', 'succeeded', 'failed', 'canceled', 'refunded') NOT NULL,
    payment_method VARCHAR(50), -- card, bank_transfer, etc.
    description TEXT,
    paid_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id),
    
    INDEX idx_payment_history_username (username),
    INDEX idx_payment_history_status (status),
    INDEX idx_payment_history_paid_at (paid_at)
);

-- 4. Content Access Control Table
CREATE TABLE content_access (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    content_type ENUM('course', 'lesson', 'game', 'badge') NOT NULL,
    content_id VARCHAR(255) NOT NULL, -- course_id, lesson_id, game_id, badge_id
    required_plan_id VARCHAR(255), -- NULL means free content
    is_premium BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (required_plan_id) REFERENCES subscription_plans(id),
    
    UNIQUE KEY unique_content_access (content_type, content_id),
    INDEX idx_content_access_type_id (content_type, content_id),
    INDEX idx_content_access_premium (is_premium)
);

-- 5. User Plan Features Table (Track feature usage)
CREATE TABLE user_plan_features (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    username VARCHAR(255) NOT NULL,
    subscription_id UUID,
    feature_name VARCHAR(255) NOT NULL, -- e.g., 'video_downloads', 'premium_courses', 'badges_unlimited'
    usage_count INT DEFAULT 0,
    usage_limit INT NULL, -- NULL means unlimited
    reset_period ENUM('daily', 'weekly', 'monthly', 'never') DEFAULT 'monthly',
    last_reset_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id),
    
    UNIQUE KEY unique_user_feature (username, feature_name),
    INDEX idx_user_features_username (username),
    INDEX idx_user_features_subscription (subscription_id)
);

-- 6. Extend existing users table
ALTER TABLE users 
ADD COLUMN stripe_customer_id VARCHAR(255) NULL,
ADD COLUMN subscription_status ENUM('free', 'trialing', 'active', 'past_due', 'canceled') DEFAULT 'free',
ADD COLUMN subscription_ends_at TIMESTAMP NULL,
ADD INDEX idx_users_stripe_customer (stripe_customer_id),
ADD INDEX idx_users_subscription_status (subscription_status);

-- 7. Extend video_courses table for premium content
ALTER TABLE video_courses 
ADD COLUMN is_premium BOOLEAN DEFAULT false,
ADD COLUMN required_plan_id VARCHAR(255) NULL,
ADD FOREIGN KEY fk_courses_plan (required_plan_id) REFERENCES subscription_plans(id),
ADD INDEX idx_courses_premium (is_premium);

-- 8. Sample subscription plans
INSERT INTO subscription_plans (id, name, description, price, currency, billing_interval, stripe_product_id, features, is_active) VALUES
('price_basic_monthly', 'Basic Monthly', 'Access to basic courses and features', 9.99, 'USD', 'month', 'prod_basic', 
 '["basic_courses", "progress_tracking", "basic_badges", "email_support"]', true),
 
('price_premium_monthly', 'Premium Monthly', 'Full access to all courses and premium features', 19.99, 'USD', 'month', 'prod_premium', 
 '["all_courses", "progress_tracking", "all_badges", "video_downloads", "priority_support", "premium_games"]', true),
 
('price_premium_yearly', 'Premium Yearly', 'Full access with 20% discount', 191.90, 'USD', 'year', 'prod_premium', 
 '["all_courses", "progress_tracking", "all_badges", "video_downloads", "priority_support", "premium_games"]', true);

-- 9. Set some courses as premium content
INSERT INTO content_access (content_type, content_id, required_plan_id, is_premium) VALUES
('course', 'advanced_course_1', 'price_premium_monthly', true),
('course', 'advanced_course_2', 'price_premium_monthly', true);

-- 10. Create views for easier querying
CREATE VIEW v_active_subscriptions AS
SELECT 
    us.*,
    sp.name as plan_name,
    sp.price,
    sp.billing_interval,
    u.email,
    u.username
FROM user_subscriptions us
JOIN subscription_plans sp ON us.plan_id = sp.id
JOIN users u ON us.username = u.username
WHERE us.status IN ('trialing', 'active');

CREATE VIEW v_user_access_summary AS
SELECT 
    u.username,
    u.email,
    u.subscription_status,
    u.subscription_ends_at,
    us.plan_id,
    sp.name as plan_name,
    CASE 
        WHEN u.subscription_status IN ('active', 'trialing') THEN true
        ELSE false
    END as has_active_subscription
FROM users u
LEFT JOIN user_subscriptions us ON u.username = us.username AND us.status IN ('active', 'trialing')
LEFT JOIN subscription_plans sp ON us.plan_id = sp.id;