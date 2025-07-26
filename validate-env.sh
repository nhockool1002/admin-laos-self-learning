#!/bin/bash

# Validate .env file format
# This script checks for common dotenv parsing issues

echo "🔍 Validating .env file format..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if .env.example exists
if [ ! -f ".env.example" ]; then
    print_error ".env.example file not found"
    exit 1
fi

print_info "Checking .env.example file..."

# Check for common issues
ISSUES_FOUND=0

# 1. Check for lines without newlines at the end
if [ "$(tail -c1 .env.example | wc -l)" -eq 0 ]; then
    print_warning "File doesn't end with newline"
    echo "" >> .env.example
    print_success "Added missing newline at end of file"
fi

# 2. Check for invalid characters or formatting
while IFS= read -r line || [ -n "$line" ]; do
    line_num=$((line_num + 1))
    
    # Skip empty lines and comments
    if [[ -z "$line" ]] || [[ "$line" =~ ^[[:space:]]*# ]]; then
        continue
    fi
    
    # Check for proper KEY=VALUE format
    if [[ ! "$line" =~ ^[A-Z_][A-Z0-9_]*= ]]; then
        if [[ ! -z "$line" ]]; then
            print_error "Line $line_num: Invalid format - '$line'"
            ISSUES_FOUND=$((ISSUES_FOUND + 1))
        fi
    fi
    
    # Check for unescaped quotes
    if [[ "$line" =~ =\".*[^\\]\".*\" ]]; then
        print_warning "Line $line_num: Potential quote escaping issue - '$line'"
    fi
    
done < .env.example

# 3. Test PHP dotenv parsing
print_info "Testing PHP dotenv parsing..."

php -r "
try {
    \$lines = file('.env.example', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    \$env = [];
    foreach (\$lines as \$line_num => \$line) {
        \$line = trim(\$line);
        if (empty(\$line) || \$line[0] === '#') continue;
        
        if (strpos(\$line, '=') === false) {
            echo 'Error on line ' . (\$line_num + 1) . ': No = found in: ' . \$line . PHP_EOL;
            exit(1);
        }
        
        list(\$key, \$value) = explode('=', \$line, 2);
        \$key = trim(\$key);
        \$value = trim(\$value);
        
        if (empty(\$key)) {
            echo 'Error on line ' . (\$line_num + 1) . ': Empty key in: ' . \$line . PHP_EOL;
            exit(1);
        }
        
        \$env[\$key] = \$value;
    }
    echo 'PHP parsing test passed' . PHP_EOL;
} catch (Exception \$e) {
    echo 'PHP parsing error: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

if [ $? -eq 0 ]; then
    print_success "PHP dotenv parsing test passed"
else
    print_error "PHP dotenv parsing test failed"
    ISSUES_FOUND=$((ISSUES_FOUND + 1))
fi

# 4. Test Laravel config loading
if [ -f "artisan" ]; then
    print_info "Testing Laravel config loading..."
    
    # Copy to .env for testing
    cp .env.example .env.test
    
    php -r "
    require 'vendor/autoload.php';
    
    try {
        \$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.test');
        \$dotenv->load();
        echo 'Laravel dotenv loading test passed' . PHP_EOL;
    } catch (Exception \$e) {
        echo 'Laravel dotenv loading error: ' . \$e->getMessage() . PHP_EOL;
        exit(1);
    }
    " 2>/dev/null
    
    if [ $? -eq 0 ]; then
        print_success "Laravel dotenv loading test passed"
    else
        print_error "Laravel dotenv loading test failed"
        ISSUES_FOUND=$((ISSUES_FOUND + 1))
    fi
    
    # Clean up test file
    rm -f .env.test
fi

# 5. Check for Stripe configuration
print_info "Checking Stripe configuration..."

STRIPE_VARS=("STRIPE_KEY" "STRIPE_SECRET" "STRIPE_WEBHOOK_SECRET" "VITE_STRIPE_KEY")
for var in "${STRIPE_VARS[@]}"; do
    if grep -q "^${var}=" .env.example; then
        print_success "$var is configured"
    else
        print_warning "$var is missing"
    fi
done

# Summary
echo ""
echo "📋 Validation Summary:"
echo "====================="

if [ $ISSUES_FOUND -eq 0 ]; then
    print_success "All validation checks passed!"
    print_success ".env.example file is properly formatted"
    echo ""
    echo "🚀 Ready for CI/CD pipeline"
else
    print_error "Found $ISSUES_FOUND issues that need to be fixed"
    echo ""
    echo "🔧 Common fixes:"
    echo "1. Ensure each line follows KEY=VALUE format"
    echo "2. Remove any trailing whitespace"
    echo "3. Add newline at end of file"
    echo "4. Escape special characters in values"
fi

exit $ISSUES_FOUND