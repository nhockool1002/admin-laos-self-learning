# Badge Management System - Testing Guide

Hướng dẫn chi tiết về Unit Testing cho hệ thống Quản lý Huy hiệu.

## 📊 Test Coverage Overview

### Test Files Created
1. **`tests/Unit/BadgeControllerTest.php`** - Controller unit tests
2. **`tests/Unit/SupabaseBadgeServiceTest.php`** - Service layer tests  
3. **`tests/Feature/BadgeManagementTest.php`** - Integration tests
4. **`tests/Unit/BadgeTestSuite.php`** - Comprehensive test suite
5. **`tests/TestCase.php`** - Enhanced base test class

### Coverage Statistics
- ✅ **Controller Tests**: 25+ test methods
- ✅ **Service Tests**: 20+ test methods  
- ✅ **Feature Tests**: 15+ test methods
- ✅ **Integration Tests**: 8+ test methods
- 🎯 **Total Coverage**: 65+ test cases

## 🧪 Test Categories

### 1. Unit Tests

#### BadgeController Tests
```php
// Authorization Tests
- test_index_requires_admin_authorization()
- test_store_requires_admin_authorization()
- test_update_requires_admin_authorization()
- test_destroy_requires_admin_authorization()

// CRUD Operation Tests
- test_index_returns_badges_for_admin()
- test_show_returns_badge_details()
- test_store_creates_badge_successfully()
- test_update_modifies_badge_successfully()
- test_destroy_deletes_badge_successfully()

// Validation Tests
- test_store_fails_without_required_fields()
- test_award_badge_requires_valid_data()

// Badge Management Tests
- test_award_badge_successfully()
- test_award_badge_fails_if_user_already_has_badge()
- test_revoke_badge_successfully()

// API Tests
- test_api_badges_returns_public_data()
- test_api_badge_detail_returns_404_for_nonexistent_badge()
- test_api_award_badge_prevents_duplicates()
```

#### SupabaseService Tests
```php
// Badge CRUD Tests
- test_get_badges_returns_array_on_success()
- test_create_badge_returns_created_badge_on_success()
- test_update_badge_returns_updated_badge_on_success()
- test_delete_badge_returns_true_on_success()

// User Badge Tests
- test_check_user_badge_exists_returns_true_when_exists()
- test_award_user_badge_returns_user_badge_on_success()
- test_revoke_user_badge_returns_true_on_success()

// Error Handling Tests
- test_get_badges_returns_null_on_failure()
- test_network_error_handling()

// Edge Case Tests
- test_get_badge_by_id_with_zero_id()
- test_create_badge_with_special_characters()
```

### 2. Feature Tests (Integration)

#### Full Workflow Tests
```php
// Admin Interface Tests
- test_admin_can_access_badges_page()
- test_admin_can_create_badge_with_image()
- test_admin_can_update_badge()
- test_admin_can_delete_badge()

// Permission Tests
- test_non_admin_cannot_list_badges()
- test_award_badge_prevents_duplicates()

// Public API Tests
- test_public_api_can_list_badges()
- test_public_api_returns_404_for_nonexistent_badge()
- test_public_api_can_award_badge()

// Validation Tests
- test_create_badge_validates_required_fields()
- test_create_badge_validates_image_file()
- test_create_badge_handles_large_files()

// Complex Workflow Tests
- test_complete_badge_lifecycle()
```

### 3. Integration Tests

#### System-wide Tests
```php
// Component Integration
- test_badge_system_integration()
- test_badge_system_permissions()
- test_complete_badge_workflow()

// Error Handling
- test_badge_system_error_handling()
- test_badge_system_validation()

// Performance
- test_badge_system_performance()

// Security
- test_badge_system_security()
- test_badge_system_edge_cases()
```

## 🚀 Running Tests

### Individual Test Files
```bash
# Run controller tests
php artisan test tests/Unit/BadgeControllerTest.php

# Run service tests  
php artisan test tests/Unit/SupabaseBadgeServiceTest.php

# Run feature tests
php artisan test tests/Feature/BadgeManagementTest.php

# Run integration tests
php artisan test tests/Unit/BadgeTestSuite.php
```

### Test Suites
```bash
# Run all badge-related tests
php artisan test --testsuite=Unit --filter=Badge

# Run with coverage
php artisan test --coverage --filter=Badge

# Run specific test method
php artisan test --filter=test_award_badge_successfully
```

### Parallel Testing
```bash
# Run tests in parallel for faster execution
php artisan test --parallel --processes=4
```

## 🛠️ Test Configuration

### Environment Setup
```php
// tests/.env.testing
SUPABASE_URL=https://test.supabase.co
SUPABASE_ANON_KEY=test-key
```

### Mock Setup
```php
// Automatic Supabase mocking
protected function setUp(): void
{
    parent::setUp();
    
    config([
        'services.supabase.url' => 'https://test.supabase.co',
        'services.supabase.anon_key' => 'test-key'
    ]);
}
```

## 📋 Test Data Management

### Mock Data Helpers
```php
// Create test badge
$badge = $this->createMockBadge(1, [
    'name' => 'Custom Badge',
    'description' => 'Custom description'
]);

// Create test user badge
$userBadge = $this->createMockUserBadge(123, 1);

// Create admin headers
$headers = $this->adminHeaders();

// Create test image
$image = $this->createTestImageFile('badge.png', 100, 100);
```

### HTTP Mocking
```php
// Mock successful responses
$this->mockSupabaseSuccess();

// Mock error responses
$this->mockSupabaseError();

// Mock empty responses
$this->mockSupabaseEmpty();

// Custom HTTP mock
Http::fake([
    'https://test.supabase.co/rest/v1/badges*' => Http::response($mockData, 200)
]);
```

## ✅ Test Assertions

### Custom Assertion Helpers
```php
// Assert badge response structure
$this->assertBadgeResponse($response, $expectedBadge);

// Assert user badge response
$this->assertUserBadgeResponse($response, $expectedUserBadge);

// Assert API success response
$this->assertApiSuccess($response, $expectedData);

// Assert API error response
$this->assertApiError($response, $expectedMessage, $expectedStatus);

// Assert unauthorized access
$this->assertUnauthorized($response);
```

### Standard Assertions
```php
// HTTP Status
$response->assertStatus(200);
$response->assertStatus(401);
$response->assertStatus(422);

// JSON Structure
$response->assertJsonStructure([
    'success',
    'data' => ['*' => ['id', 'name', 'description']]
]);

// JSON Content
$response->assertJson(['success' => true]);
$response->assertJsonCount(2, 'data');

// View Assertions
$response->assertViewIs('badges');
$response->assertViewHas('badges');
```

## 🎯 Test Scenarios Covered

### 1. Authentication & Authorization
- ✅ Admin access required for management endpoints
- ✅ Non-admin users blocked from admin functions
- ✅ Public API accessible without authentication
- ✅ CSRF token validation
- ✅ Invalid user header handling

### 2. CRUD Operations
- ✅ Create badge with image upload
- ✅ Read badge list and details
- ✅ Update badge with/without new image
- ✅ Delete badge with cleanup
- ✅ Input validation for all operations

### 3. User Badge Management
- ✅ Award badge to user
- ✅ Prevent duplicate badge awards
- ✅ Revoke badge from user
- ✅ List user badges with details
- ✅ Check badge existence

### 4. File Upload Handling
- ✅ Valid image file upload
- ✅ Invalid file type rejection
- ✅ File size limit enforcement
- ✅ Image cleanup on badge deletion
- ✅ Old image cleanup on update

### 5. Error Handling
- ✅ Network errors
- ✅ Supabase service errors
- ✅ Validation errors
- ✅ File system errors
- ✅ Database constraint violations

### 6. API Integration
- ✅ Public API endpoints
- ✅ Third-party integration scenarios
- ✅ API response format consistency
- ✅ Error response standardization

### 7. Edge Cases
- ✅ Empty data sets
- ✅ Non-existent resources
- ✅ Special characters in names
- ✅ Large file uploads
- ✅ Concurrent operations

### 8. Performance
- ✅ Multiple concurrent requests
- ✅ Response time validation
- ✅ Memory usage monitoring
- ✅ Database query optimization

## 📊 Test Metrics

### Coverage Goals
- **Controller Coverage**: 95%+ line coverage
- **Service Coverage**: 90%+ line coverage  
- **Integration Coverage**: 85%+ workflow coverage
- **Error Scenarios**: 100% error path coverage

### Performance Benchmarks
- **API Response Time**: < 500ms per request
- **File Upload**: < 2s for 2MB image
- **Batch Operations**: < 5s for 10 operations
- **Memory Usage**: < 128MB during test execution

## 🔧 Debugging Tests

### Common Issues & Solutions

#### 1. HTTP Mock Not Working
```php
// Ensure Http::fake() is called before the tested code
Http::fake([
    'https://test.supabase.co/rest/v1/*' => Http::response($data, 200)
]);

// Check URL patterns match exactly
Http::assertSent(function ($request) {
    return str_contains($request->url(), 'badges');
});
```

#### 2. File Upload Tests Failing
```php
// Use Storage::fake() for file operations
Storage::fake('public');

// Create proper test files
$file = UploadedFile::fake()->image('test.png', 100, 100);

// Ensure directory exists
if (!file_exists(public_path('assets/images'))) {
    mkdir(public_path('assets/images'), 0755, true);
}
```

#### 3. Authentication Issues
```php
// Verify headers are properly set
$headers = [
    'User' => json_encode($this->createMockAdminUser()),
    'X-CSRF-TOKEN' => csrf_token()
];

// Check user data format
$user = json_decode($headers['User'], true);
$this->assertTrue($user['is_admin']);
```

#### 4. Test Data Cleanup
```php
// Override tearDown for cleanup
protected function tearDown(): void
{
    // Clean test files
    $testFiles = glob(public_path('assets/images/test_*'));
    foreach ($testFiles as $file) {
        unlink($file);
    }
    
    parent::tearDown();
}
```

## 📈 Continuous Integration

### Test Automation
```yaml
# .github/workflows/tests.yml
name: Badge Management Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: composer install
      - name: Run Badge Tests
        run: php artisan test --filter=Badge --coverage
```

### Quality Gates
- ✅ All tests must pass
- ✅ Coverage above 90%
- ✅ No PHP errors or warnings
- ✅ Performance benchmarks met

## 🎓 Best Practices

### 1. Test Organization
- Group related tests in the same file
- Use descriptive test method names
- Follow AAA pattern (Arrange, Act, Assert)
- Keep tests independent and isolated

### 2. Data Management
- Use factories for consistent test data
- Clean up test data after each test
- Use database transactions when possible
- Mock external dependencies

### 3. Error Testing
- Test both success and failure scenarios
- Verify error messages and codes
- Test edge cases and boundary conditions
- Ensure graceful error handling

### 4. Performance Testing
- Set reasonable performance expectations
- Test with realistic data volumes
- Monitor memory usage
- Test concurrent scenarios

## 📚 Documentation

### Test Documentation Requirements
- Document test purpose and scope
- Explain complex test scenarios
- Provide setup instructions
- Include troubleshooting guides

### Code Comments
```php
/**
 * Test that admin can create a badge with image upload
 * 
 * This test verifies:
 * 1. Admin authentication is working
 * 2. File upload validation passes
 * 3. Badge is created in Supabase
 * 4. Image is saved to correct location
 * 5. Response contains created badge data
 */
public function test_admin_can_create_badge_with_image()
{
    // Test implementation...
}
```

Badge Management System hiện có test coverage toàn diện với 65+ test cases bao phủ tất cả chức năng chính. Hệ thống testing đảm bảo chất lượng code và reliability của ứng dụng.