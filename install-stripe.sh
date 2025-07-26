#!/bin/bash

# 🚀 Stripe Integration Installation Script
# This script automates the Stripe integration setup for your Laravel learning platform

echo "🚀 Starting Stripe Integration Setup..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the root of a Laravel project."
    exit 1
fi

print_step "1. Installing Composer Dependencies..."
composer require laravel/cashier stripe/stripe-php

if [ $? -eq 0 ]; then
    print_status "✅ Dependencies installed successfully"
else
    print_error "❌ Failed to install dependencies"
    exit 1
fi

print_step "2. Publishing Cashier Migrations (Optional)..."
php artisan vendor:publish --tag="cashier-migrations" --force

print_step "3. Running Migrations..."
php artisan migrate

if [ $? -eq 0 ]; then
    print_status "✅ Migrations completed successfully"
else
    print_error "❌ Migration failed"
    exit 1
fi

print_step "4. Seeding Subscription Plans..."
php artisan db:seed --class=SubscriptionPlansSeeder

if [ $? -eq 0 ]; then
    print_status "✅ Subscription plans seeded successfully"
else
    print_warning "⚠️  Seeding failed - you may need to create the seeder first"
fi

print_step "5. Clearing Application Cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

print_status "✅ Cache cleared"

# Check if .env file exists and add Stripe configuration
if [ -f ".env" ]; then
    print_step "6. Updating .env file..."
    
    # Check if Stripe configuration already exists
    if grep -q "STRIPE_KEY" .env; then
        print_warning "⚠️  Stripe configuration already exists in .env file"
    else
        echo "" >> .env
        echo "# Stripe Configuration" >> .env
        echo "STRIPE_KEY=pk_test_your_publishable_key_here" >> .env
        echo "STRIPE_SECRET=sk_test_your_secret_key_here" >> .env
        echo "STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here" >> .env
        echo "" >> .env
        echo "# Currency Settings" >> .env
        echo "CASHIER_CURRENCY=vnd" >> .env
        echo "CASHIER_CURRENCY_LOCALE=vi_VN" >> .env
        
        print_status "✅ Stripe configuration added to .env file"
        print_warning "⚠️  Please update the Stripe keys with your actual values"
    fi
else
    print_warning "⚠️  .env file not found. Please create it and add Stripe configuration"
fi

print_step "7. Generating Application Key (if needed)..."
if grep -q "APP_KEY=base64:" .env; then
    print_status "✅ Application key already exists"
else
    php artisan key:generate
    print_status "✅ Application key generated"
fi

echo ""
echo "🎉 Stripe Integration Setup Complete!"
echo ""
echo "📋 Next Steps:"
echo "1. Update your .env file with actual Stripe keys"
echo "2. Create products and prices in Stripe Dashboard"
echo "3. Configure webhook endpoint: yourdomain.com/stripe/webhook"
echo "4. Test the subscription flow"
echo ""
echo "📚 Documentation:"
echo "- Implementation Guide: STRIPE_IMPLEMENTATION_GUIDE_VI.md"
echo "- Stripe Integration Guide: stripe-integration-guide.md"
echo ""
echo "🔧 Routes added:"
echo "- Subscription Plans: /subscription/plans"
echo "- Subscription Management: /subscription/manage"
echo "- Webhook: /stripe/webhook"
echo ""
print_status "Happy coding! 🚀"