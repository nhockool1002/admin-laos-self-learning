# 🔧 Fix Dotenv Parsing Errors

## ❌ Lỗi hiện tại

```bash
🧪 Running Unit Tests...
The environment file is invalid!
Failed to parse dotenv file. Encountered unexpected whitespace at ["${STRIPE_KEY}"DB_CONNECTION=pgsql].
Error: Process completed with exit code 1.
```

## 🎯 Nguyên nhân

1. **Missing newline** tại cuối file `.env.example`
2. **Line concatenation** do thiếu line break
3. **Invalid character encoding** hoặc hidden characters
4. **Improper quoting** của environment variables

## 🚀 Giải pháp nhanh

### Cách 1: Chạy script tự động fix (Khuyến nghị)

```bash
./fix-dotenv.sh
```

### Cách 2: Manual fix

```bash
# Bước 1: Backup file
cp .env.example .env.example.backup

# Bước 2: Add missing newline
echo "" >> .env.example

# Bước 3: Remove carriage returns và trailing spaces
sed -i 's/\r$//' .env.example
sed -i 's/[[:space:]]*$//' .env.example

# Bước 4: Test parsing
php -r "
\$lines = file('.env.example', FILE_IGNORE_NEW_LINES);
foreach (\$lines as \$line) {
    \$line = trim(\$line);
    if (empty(\$line) || \$line[0] === '#') continue;
    if (strpos(\$line, '=') === false) {
        echo 'Invalid line: ' . \$line . PHP_EOL;
        exit(1);
    }
}
echo 'Format is valid' . PHP_EOL;
"
```

### Cách 3: Recreate file hoàn toàn

```bash
# Backup old file
mv .env.example .env.example.old

# Create new clean file
cat > .env.example << 'EOF'
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laos_learning_admin
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# Stripe Configuration
STRIPE_KEY=pk_test_your_stripe_publishable_key_here
STRIPE_SECRET=sk_test_your_stripe_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here

# Frontend URL for redirect after payment
APP_FRONTEND_URL=http://localhost:3000

APP_SUPABASE_URL='none'
APP_SUPABASE_ANON_KEY=anonkey

# Stripe for frontend
VITE_STRIPE_KEY=pk_test_your_stripe_publishable_key_here
EOF
```

## 🔍 Debugging Steps

### 1. Check file endings
```bash
# Check for missing newline
hexdump -C .env.example | tail -5

# Check line endings (should be 0A for Unix)
file .env.example
```

### 2. Validate line by line
```bash
# Check each line manually
cat -n .env.example | grep -v '^[[:space:]]*#' | grep -v '^[[:space:]]*$'
```

### 3. Test PHP parsing
```bash
php -r "
\$content = file_get_contents('.env.example');
echo 'File size: ' . strlen(\$content) . ' bytes' . PHP_EOL;
echo 'Last char: ' . ord(substr(\$content, -1)) . PHP_EOL;
echo 'Last 5 chars: ';
for (\$i = -5; \$i < 0; \$i++) {
    echo ord(substr(\$content, \$i, 1)) . ' ';
}
echo PHP_EOL;
"
```

## 📁 Files được tạo để fix

### 1. `fix-dotenv.sh`
- ✅ Auto-detect và fix common issues
- ✅ Backup original files
- ✅ Test parsing after fixes
- ✅ Comprehensive validation

### 2. `validate-env.sh`
- ✅ Validate .env file format
- ✅ Check for parsing issues
- ✅ Test Laravel dotenv loading
- ✅ Verify Stripe configuration

### 3. Updated `.github/workflows/ci.yml`
- ✅ Pre-validate .env.example
- ✅ Auto-fix formatting issues
- ✅ Comprehensive testing

## 🎯 Pipeline Updates

```yaml
- name: Validate .env.example format
  run: |
    echo "🔍 Validating .env.example format..."
    # Check for proper line endings
    if [ "$(tail -c1 .env.example | wc -l)" -eq 0 ]; then
      echo "Adding missing newline to .env.example"
      echo "" >> .env.example
    fi
    
    # Test basic parsing
    php -r "
    \$lines = file('.env.example', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach (\$lines as \$line_num => \$line) {
      \$line = trim(\$line);
      if (empty(\$line) || \$line[0] === '#') continue;
      if (strpos(\$line, '=') === false) {
        echo 'Invalid line ' . (\$line_num + 1) . ': ' . \$line . PHP_EOL;
        exit(1);
      }
    }
    echo '.env.example format is valid' . PHP_EOL;
    "
```

## ✅ Common Issues và Solutions

### Issue 1: Missing final newline
**Error:** `Encountered unexpected whitespace`
**Fix:** `echo "" >> .env.example`

### Issue 2: Carriage returns (Windows)
**Error:** `Invalid character encoding`
**Fix:** `sed -i 's/\r$//' .env.example`

### Issue 3: Trailing whitespace
**Error:** `Unexpected whitespace`
**Fix:** `sed -i 's/[[:space:]]*$//' .env.example`

### Issue 4: Missing equals sign
**Error:** `No = found in line`
**Fix:** Ensure all non-comment lines follow `KEY=VALUE` format

### Issue 5: Unescaped quotes
**Error:** `Quote parsing error`
**Fix:** Escape quotes or use single quotes

### Issue 6: Variable substitution
**Error:** `Undefined variable`
**Fix:** Ensure referenced variables are defined first

## 🧪 Testing Commands

```bash
# Test 1: Basic format validation
./validate-env.sh

# Test 2: Fix all issues
./fix-dotenv.sh

# Test 3: Laravel config loading
cp .env.example .env
php artisan config:clear
php artisan config:cache

# Test 4: Pipeline simulation
php -r "
require 'vendor/autoload.php';
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.example');
\$dotenv->load();
echo 'Dotenv loading successful' . PHP_EOL;
"
```

## 📋 Verification Checklist

Trước khi commit:

- [ ] File `.env.example` ends with newline
- [ ] No carriage returns (`\r`) in file
- [ ] No trailing whitespace on lines
- [ ] All non-comment lines have `KEY=VALUE` format
- [ ] Quotes are properly escaped
- [ ] Variable references are valid
- [ ] `./validate-env.sh` passes
- [ ] `./fix-dotenv.sh` reports no issues
- [ ] Laravel config loading works
- [ ] Pipeline test passes locally

## 🚀 Next Steps

Sau khi fix:

```bash
# 1. Test locally
./fix-dotenv.sh
./validate-env.sh

# 2. Commit changes
git add .env.example
git commit -m "Fix: dotenv parsing errors in .env.example"

# 3. Push to trigger pipeline
git push origin main

# 4. Monitor pipeline
# Pipeline should now pass without dotenv errors
```

## 📚 Additional Resources

- [Laravel Environment Configuration](https://laravel.com/docs/configuration#environment-configuration)
- [PHP dotenv Documentation](https://github.com/vlucas/phpdotenv)
- [Common .env File Issues](https://github.com/vlucas/phpdotenv#common-problems)

---

**💡 Pro Tip:** Luôn use Unix line endings (`LF`) cho .env files và đảm bảo file kết thúc bằng newline để tránh parsing errors.