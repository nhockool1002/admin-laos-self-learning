<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class ValidationTest extends TestCase
{
    public function test_email_validation_rules()
    {
        $validEmails = [
            'test@example.com',
            'user123@domain.co.uk',
            'admin@localhost.dev',
            'name.surname@company.org'
        ];

        $invalidEmails = [
            'invalid-email',
            '@domain.com',
            'user@',
            'user..name@domain.com',
            ''
        ];

        foreach ($validEmails as $email) {
            $validator = Validator::make(['email' => $email], ['email' => 'required|email']);
            $this->assertFalse($validator->fails(), "Email '{$email}' should be valid");
        }

        foreach ($invalidEmails as $email) {
            $validator = Validator::make(['email' => $email], ['email' => 'required|email']);
            $this->assertTrue($validator->fails(), "Email '{$email}' should be invalid");
        }
    }

    public function test_password_strength_validation()
    {
        // Test minimum length requirement
        $weakPasswords = [
            '',
            '123',
            'abc',
            '12345'
        ];

        $strongPasswords = [
            'password123',
            'mySecurePass456',
            'VeryStrongPassword2024!'
        ];

        foreach ($weakPasswords as $password) {
            $validator = Validator::make(['password' => $password], ['password' => 'required|min:6']);
            $this->assertTrue($validator->fails(), "Password '{$password}' should fail minimum length validation");
        }

        foreach ($strongPasswords as $password) {
            $validator = Validator::make(['password' => $password], ['password' => 'required|min:6']);
            $this->assertFalse($validator->fails(), "Password '{$password}' should pass minimum length validation");
        }
    }

    public function test_username_validation_rules()
    {
        $validUsernames = [
            'user123',
            'admin',
            'test_user',
            'MyUsername',
            'user-name'
        ];

        $invalidUsernames = [
            '',
            'a', // too short
            'ab', // too short
            'user@name', // contains @
            'user name', // contains space
            'très_long_username_that_exceeds_the_maximum_length_limit' // too long
        ];

        foreach ($validUsernames as $username) {
            $validator = Validator::make(['username' => $username], [
                'username' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_-]+$/'
            ]);
            $this->assertFalse($validator->fails(), "Username '{$username}' should be valid");
        }

        foreach ($invalidUsernames as $username) {
            $validator = Validator::make(['username' => $username], [
                'username' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_-]+$/'
            ]);
            $this->assertTrue($validator->fails(), "Username '{$username}' should be invalid");
        }
    }

    public function test_course_title_validation()
    {
        $validTitles = [
            'Khóa học tiếng Lào cơ bản',
            'Advanced Laotian Course',
            'Beginner Level 1',
            'Test Course 123'
        ];

        $invalidTitles = [
            '', // empty
            'A', // too short
            'AB', // too short
            str_repeat('Very long title ', 20) // too long (over 255 chars)
        ];

        foreach ($validTitles as $title) {
            $validator = Validator::make(['title' => $title], [
                'title' => 'required|string|min:3|max:255'
            ]);
            $this->assertFalse($validator->fails(), "Title '{$title}' should be valid");
        }

        foreach ($invalidTitles as $title) {
            $validator = Validator::make(['title' => $title], [
                'title' => 'required|string|min:3|max:255'
            ]);
            $this->assertTrue($validator->fails(), "Title should be invalid");
        }
    }

    public function test_boolean_validation()
    {
        $validBooleans = [
            true,
            false,
            1,
            0,
            '1',
            '0'
        ];

        $invalidBooleans = [
            'true',
            'false',
            'yes',
            'no',
            2,
            -1,
            [],
            null
        ];

        foreach ($validBooleans as $value) {
            $validator = Validator::make(['is_admin' => $value], [
                'is_admin' => 'boolean'
            ]);
            $this->assertFalse($validator->fails(), "Value '{$value}' should be valid boolean");
        }

        foreach ($invalidBooleans as $value) {
            $validator = Validator::make(['is_admin' => $value], [
                'is_admin' => 'boolean'
            ]);
            $valueStr = is_array($value) ? 'array' : (is_null($value) ? 'null' : $value);
            $this->assertTrue($validator->fails(), "Value '{$valueStr}' should be invalid boolean");
        }
    }

    public function test_numeric_id_validation()
    {
        $validIds = [
            1,
            '1',
            '123',
            999999
        ];

        $invalidIds = [
            0,
            -1,
            'abc',
            '1.5',
            '',
            null
        ];

        foreach ($validIds as $id) {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|integer|min:1'
            ]);
            $this->assertFalse($validator->fails(), "ID '{$id}' should be valid");
        }

        foreach ($invalidIds as $id) {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|integer|min:1'
            ]);
            $this->assertTrue($validator->fails(), "ID should be invalid");
        }
    }

    public function test_md5_password_hash_format()
    {
        $passwords = [
            'password123',
            'mySecretPassword',
            'admin2024!'
        ];

        foreach ($passwords as $password) {
            $hashedPassword = md5($password);
            
            // MD5 hash should be 32 characters long
            $this->assertEquals(32, strlen($hashedPassword));
            
            // Should contain only hexadecimal characters
            $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $hashedPassword);
            
            // Same password should always produce same hash
            $this->assertEquals($hashedPassword, md5($password));
        }
    }

    public function test_text_content_validation()
    {
        $validContent = [
            'Hello world',
            'Khóa học tiếng Lào cơ bản cho người mới bắt đầu',
            str_repeat('a', 100), // 100 chars
            'Ab', // minimum 2 chars
        ];

        $invalidContent = [
            '', // empty
            'a', // too short (less than 2 chars)
            str_repeat('a', 5001), // too long (more than 5000 chars)
        ];

        foreach ($validContent as $content) {
            $validator = Validator::make(['content' => $content], [
                'content' => 'required|string|min:2|max:5000'
            ]);
            $this->assertFalse($validator->fails(), "Content should be valid");
        }

        foreach ($invalidContent as $content) {
            $validator = Validator::make(['content' => $content], [
                'content' => 'required|string|min:2|max:5000'
            ]);
            $this->assertTrue($validator->fails(), "Content should be invalid");
        }
    }

    public function test_array_validation()
    {
        $validArrays = [
            [],
            ['item1'],
            ['item1', 'item2', 'item3']
        ];

        $invalidArrays = [
            'not_an_array',
            123,
            null,
            false
        ];

        foreach ($validArrays as $array) {
            $validator = Validator::make(['items' => $array], [
                'items' => 'array'
            ]);
            $this->assertFalse($validator->fails(), "Should be valid array");
        }

        foreach ($invalidArrays as $notArray) {
            $validator = Validator::make(['items' => $notArray], [
                'items' => 'array'
            ]);
            $this->assertTrue($validator->fails(), "Should be invalid array");
        }
    }

    public function test_custom_validation_scenarios()
    {
        // Test login validation (email or username)
        $loginValidator = function($loginValue) {
            $rules = [];
            if (filter_var($loginValue, FILTER_VALIDATE_EMAIL)) {
                $rules['login'] = 'required|email';
            } else {
                $rules['login'] = 'required|string|min:3';
            }
            
            $validator = Validator::make(['login' => $loginValue], $rules);
            return !$validator->fails();
        };

        $this->assertTrue($loginValidator('test@example.com'));
        $this->assertTrue($loginValidator('username123'));
        $this->assertFalse($loginValidator(''));
        $this->assertFalse($loginValidator('ab'));

        // Test optional field validation
        $optionalValidator = Validator::make(['description' => null], [
            'description' => 'nullable|string|max:1000'
        ]);
        $this->assertFalse($optionalValidator->fails());

        $optionalValidator = Validator::make(['description' => 'Valid description'], [
            'description' => 'nullable|string|max:1000'
        ]);
        $this->assertFalse($optionalValidator->fails());
    }
}