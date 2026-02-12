#!/bin/bash

# Fix Composer Lock File and Install Stripe Integration
# Run this script to resolve composer issues and set up Stripe payment system

echo "🔧 Fixing Composer Lock File and Installing Stripe Integration..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install composer first."
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed. Please install PHP 8.2+ first."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
print_status "PHP Version: $PHP_VERSION"

echo "🗑️  Cleaning up old composer files..."

# Remove old lock file and vendor directory
if [ -f "composer.lock" ]; then
    rm composer.lock
    print_status "Removed old composer.lock"
fi

if [ -d "vendor" ]; then
    rm -rf vendor/
    print_status "Removed vendor directory"
fi

echo "📦 Installing dependencies..."

# Clear composer cache
composer clear-cache

# Install dependencies
if composer install --no-interaction --prefer-dist; then
    print_status "Dependencies installed successfully"
else
    print_error "Failed to install dependencies"
    exit 1
fi

# Verify Stripe package installation
if composer show stripe/stripe-php &> /dev/null; then
    print_status "Stripe PHP SDK installed successfully"
else
    print_warning "Stripe PHP SDK not found, attempting to install..."
    if composer require stripe/stripe-php --no-interaction; then
        print_status "Stripe PHP SDK installed"
    else
        print_error "Failed to install Stripe PHP SDK"
        exit 1
    fi
fi

echo "🔧 Setting up Laravel environment..."

# Copy .env file if it doesn't exist
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_status "Copied .env.example to .env"
    else
        print_warning ".env.example not found"
    fi
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --no-interaction
    print_status "Generated application key"
fi

echo "🗄️  Setting up database..."

# Run migrations
if php artisan migrate --no-interaction; then
    print_status "Database migrations completed"
else
    print_warning "Database migrations failed - check your database connection"
fi

# Dump autoload
composer dump-autoload
print_status "Autoload dumped"

echo "🧪 Testing installation..."

# Test if Stripe service can be loaded
php -r "
require 'vendor/autoload.php';
try {
    new App\Services\StripeService();
    echo 'Stripe service loaded successfully' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Error loading Stripe service: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

if [ $? -eq 0 ]; then
    print_status "Stripe service test passed"
else
    print_error "Stripe service test failed"
fi

echo "📋 Summary of what was installed:"
echo "--------------------------------"
composer show | grep -E "(stripe|laravel)" | head -10

echo ""
echo "🎉 Installation completed successfully!"
echo ""
echo "📝 Next steps:"
echo "1. Update your .env file with Stripe keys:"
echo "   STRIPE_KEY=pk_test_your_key_here"
echo "   STRIPE_SECRET=sk_test_your_secret_here"
echo "   STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret"
echo ""
echo "2. Seed subscription plans:"
echo "   php artisan db:seed --class=SubscriptionPlanSeeder"
echo ""
echo "3. Start the development server:"
echo "   php artisan serve"
echo ""
echo "4. Test the API endpoints:"
echo "   curl http://localhost:8000/api/v1/subscriptions/plans"
echo ""
echo "📚 Read STRIPE_SETUP_GUIDE.md for detailed setup instructions"
echo "📖 Read STRIPE_PAYMENT_API_README.md for API documentation"