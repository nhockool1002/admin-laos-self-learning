#!/bin/bash

# Fix Pipeline Composer Lock File Issue
# This script resolves the composer.lock synchronization problem

echo "🔧 Fixing Pipeline Composer Issue..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if we're in a Laravel project
if [ ! -f "composer.json" ]; then
    print_error "composer.json not found. Are you in the correct directory?"
    exit 1
fi

if [ ! -f "artisan" ]; then
    print_error "artisan file not found. This doesn't appear to be a Laravel project."
    exit 1
fi

print_info "Detected Laravel project"

# Step 1: Backup current composer.lock if exists
if [ -f "composer.lock" ]; then
    print_info "Backing up current composer.lock"
    cp composer.lock composer.lock.backup
    print_status "Backup created: composer.lock.backup"
fi

# Step 2: Validate current composer.json
print_info "Validating composer.json..."
if composer validate --no-check-all --strict; then
    print_status "composer.json is valid"
else
    print_warning "composer.json has validation issues, but continuing..."
fi

# Step 3: Remove problematic lock file
if [ -f "composer.lock" ]; then
    print_info "Removing outdated composer.lock"
    rm composer.lock
    print_status "Removed composer.lock"
fi

# Step 4: Clear composer cache
print_info "Clearing composer cache..."
composer clear-cache
print_status "Cache cleared"

# Step 5: Update dependencies to create new lock file
print_info "Creating new composer.lock with current dependencies..."
if composer update --no-interaction --prefer-dist --no-scripts; then
    print_status "New composer.lock created successfully"
else
    print_error "Failed to create composer.lock"
    
    # Restore backup if exists
    if [ -f "composer.lock.backup" ]; then
        print_info "Restoring backup..."
        mv composer.lock.backup composer.lock
        print_status "Backup restored"
    fi
    exit 1
fi

# Step 6: Verify Stripe package
print_info "Verifying Stripe package installation..."
if composer show stripe/stripe-php > /dev/null 2>&1; then
    STRIPE_VERSION=$(composer show stripe/stripe-php | grep "versions" | head -1)
    print_status "Stripe PHP SDK installed: $STRIPE_VERSION"
else
    print_warning "Stripe PHP SDK not found in lock file"
    print_info "This might be expected if stripe/stripe-php is in require-dev"
fi

# Step 7: Run composer install to verify
print_info "Testing composer install with new lock file..."
if composer install --no-interaction --prefer-dist --dry-run; then
    print_status "composer install simulation successful"
else
    print_error "composer install simulation failed"
    exit 1
fi

# Step 8: Validate final state
print_info "Validating final composer state..."
if composer validate; then
    print_status "Final validation passed"
else
    print_error "Final validation failed"
    exit 1
fi

# Step 9: Generate optimized autoloader
print_info "Generating optimized autoloader..."
composer dump-autoload --optimize
print_status "Autoloader optimized"

# Step 10: Show summary
echo ""
echo "📋 Summary:"
echo "----------"
print_status "composer.lock regenerated successfully"
print_status "All dependencies synchronized"
print_status "Pipeline should now work correctly"

echo ""
echo "📝 What was fixed:"
echo "- Removed outdated composer.lock"
echo "- Created new lock file with current composer.json"
echo "- Verified all dependencies are properly locked"
echo "- Optimized autoloader for better performance"

echo ""
echo "🚀 Next steps for CI/CD:"
echo "1. Commit the new composer.lock file:"
echo "   git add composer.lock"
echo "   git commit -m 'Fix: Update composer.lock with Stripe dependency'"
echo ""
echo "2. Push to trigger pipeline:"
echo "   git push origin main"
echo ""
echo "3. Pipeline should now run successfully with:"
echo "   composer install --prefer-dist --no-interaction --no-progress"

# Clean up backup if everything succeeded
if [ -f "composer.lock.backup" ]; then
    rm composer.lock.backup
    print_status "Cleaned up backup file"
fi

print_status "Pipeline fix completed! 🎉"