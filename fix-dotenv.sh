#!/bin/bash

# Fix common dotenv parsing issues
# This script automatically fixes .env file formatting problems

echo "🔧 Fixing dotenv parsing issues..."

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

# Function to fix a .env file
fix_env_file() {
    local file=$1
    local backup_file="${file}.backup"
    
    if [ ! -f "$file" ]; then
        print_error "File $file not found"
        return 1
    fi
    
    print_info "Processing $file..."
    
    # Create backup
    cp "$file" "$backup_file"
    print_info "Backup created: $backup_file"
    
    # Create temporary fixed file
    local temp_file="${file}.tmp"
    > "$temp_file"
    
    local line_num=0
    local fixes_applied=0
    
    while IFS= read -r line || [ -n "$line" ]; do
        line_num=$((line_num + 1))
        original_line="$line"
        
        # Fix 1: Remove carriage returns (Windows line endings)
        line=$(echo "$line" | tr -d '\r')
        
        # Fix 2: Remove trailing whitespace
        line=$(echo "$line" | sed 's/[[:space:]]*$//')
        
        # Fix 3: Skip completely empty lines (but keep comment lines)
        if [ -z "$line" ]; then
            echo >> "$temp_file"
            continue
        fi
        
        # Fix 4: Handle comment lines
        if [[ "$line" =~ ^[[:space:]]*# ]]; then
            echo "$line" >> "$temp_file"
            continue
        fi
        
        # Fix 5: Ensure KEY=VALUE format for non-comment lines
        if [[ "$line" != *"="* ]] && [[ ! -z "$line" ]]; then
            print_warning "Line $line_num: Missing '=' in non-comment line: '$line'"
            print_warning "Skipping malformed line"
            continue
        fi
        
        # Fix 6: Handle quoted values properly
        if [[ "$line" =~ ^[A-Z_][A-Z0-9_]*= ]]; then
            # Extract key and value
            key=$(echo "$line" | cut -d'=' -f1)
            value=$(echo "$line" | cut -d'=' -f2-)
            
            # Fix 7: Handle variable substitution syntax
            if [[ "$value" =~ ^\$\{.*\}$ ]]; then
                # Keep variable substitution as is
                echo "${key}=${value}" >> "$temp_file"
            elif [[ "$value" =~ ^\".*\"$ ]] || [[ "$value" =~ ^\'.*\'$ ]]; then
                # Already quoted, keep as is
                echo "${key}=${value}" >> "$temp_file"
            elif [[ "$value" == *" "* ]] || [[ "$value" == *"\$"* ]]; then
                # Contains spaces or variables, add quotes
                echo "${key}=\"${value}\"" >> "$temp_file"
            else
                # Simple value, no quotes needed
                echo "${key}=${value}" >> "$temp_file"
            fi
        else
            # Keep line as is if it doesn't match expected format
            echo "$line" >> "$temp_file"
        fi
        
        # Track if we made changes
        if [ "$original_line" != "$line" ]; then
            fixes_applied=$((fixes_applied + 1))
        fi
        
    done < "$file"
    
    # Add final newline if missing
    if [ "$(tail -c1 "$temp_file" 2>/dev/null | wc -l)" -eq 0 ]; then
        echo >> "$temp_file"
        fixes_applied=$((fixes_applied + 1))
        print_info "Added missing final newline"
    fi
    
    # Replace original file with fixed version
    mv "$temp_file" "$file"
    
    if [ $fixes_applied -gt 0 ]; then
        print_success "Applied $fixes_applied fixes to $file"
    else
        print_success "$file was already properly formatted"
        # Remove backup if no changes were made
        rm "$backup_file"
    fi
    
    return 0
}

# Function to test dotenv parsing
test_dotenv_parsing() {
    local file=$1
    
    print_info "Testing dotenv parsing for $file..."
    
    php -r "
    try {
        \$lines = file('$file', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        \$env = [];
        \$errors = 0;
        
        foreach (\$lines as \$line_num => \$line) {
            \$line = trim(\$line);
            if (empty(\$line) || \$line[0] === '#') continue;
            
            if (strpos(\$line, '=') === false) {
                echo 'Error on line ' . (\$line_num + 1) . ': No = found in: ' . \$line . PHP_EOL;
                \$errors++;
                continue;
            }
            
            list(\$key, \$value) = explode('=', \$line, 2);
            \$key = trim(\$key);
            \$value = trim(\$value);
            
            if (empty(\$key)) {
                echo 'Error on line ' . (\$line_num + 1) . ': Empty key in: ' . \$line . PHP_EOL;
                \$errors++;
                continue;
            }
            
            // Handle quoted values
            if ((substr(\$value, 0, 1) === '\"' && substr(\$value, -1) === '\"') ||
                (substr(\$value, 0, 1) === \"'\" && substr(\$value, -1) === \"'\")) {
                \$value = substr(\$value, 1, -1);
            }
            
            \$env[\$key] = \$value;
        }
        
        if (\$errors === 0) {
            echo 'Parsing test passed - ' . count(\$env) . ' variables loaded' . PHP_EOL;
            exit(0);
        } else {
            echo 'Parsing test failed with ' . \$errors . ' errors' . PHP_EOL;
            exit(1);
        }
    } catch (Exception \$e) {
        echo 'Parsing error: ' . \$e->getMessage() . PHP_EOL;
        exit(1);
    }
    "
    
    return $?
}

# Main execution
ENV_FILES=(".env.example")

# Add .env if it exists
if [ -f ".env" ]; then
    ENV_FILES+=(".env")
fi

TOTAL_ERRORS=0

for file in "${ENV_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo ""
        echo "🔧 Processing $file..."
        echo "=========================="
        
        # Fix the file
        if fix_env_file "$file"; then
            # Test parsing
            if test_dotenv_parsing "$file"; then
                print_success "$file is now properly formatted and parseable"
            else
                print_error "$file still has parsing issues"
                TOTAL_ERRORS=$((TOTAL_ERRORS + 1))
            fi
        else
            print_error "Failed to fix $file"
            TOTAL_ERRORS=$((TOTAL_ERRORS + 1))
        fi
    else
        print_warning "$file not found, skipping"
    fi
done

echo ""
echo "📋 Summary:"
echo "==========="

if [ $TOTAL_ERRORS -eq 0 ]; then
    print_success "All .env files are properly formatted!"
    print_success "Pipeline should now work correctly"
    
    echo ""
    echo "🚀 Next steps:"
    echo "1. Test locally: php artisan config:clear && php artisan config:cache"
    echo "2. Commit changes: git add .env.example && git commit -m 'Fix: dotenv formatting'"
    echo "3. Push to trigger pipeline: git push"
else
    print_error "Found issues in $TOTAL_ERRORS files"
    echo ""
    echo "🔍 Check the backup files (*.backup) if you need to restore"
    echo "🔧 Manual review may be needed for complex cases"
fi

exit $TOTAL_ERRORS