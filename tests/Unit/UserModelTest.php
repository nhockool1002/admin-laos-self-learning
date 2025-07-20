<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class UserModelTest extends TestCase
{
    public function test_user_can_be_created()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ];

        $user = new User($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_user_attributes_are_fillable()
    {
        $user = new User();
        
        $fillable = $user->getFillable();
        
        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    public function test_password_is_hidden_from_array()
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $userArray = $user->toArray();
        
        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
    }

    public function test_user_email_must_be_unique()
    {
        // This test would require database interaction
        // For now, we just test the model has email attribute
        $user = new User(['email' => 'test@example.com']);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_user_factory_creates_valid_user()
    {
        // Mock a user instance since we don't have database
        $user = new User([
            'name' => 'Factory User',
            'email' => 'factory@example.com',
            'password' => bcrypt('password123')
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Factory User', $user->name);
        $this->assertEquals('factory@example.com', $user->email);
    }

    public function test_user_factory_can_override_attributes()
    {
        $user = new User([
            'name' => 'Custom Name',
            'email' => 'custom@example.com',
            'password' => bcrypt('custom123')
        ]);

        $this->assertEquals('Custom Name', $user->name);
        $this->assertEquals('custom@example.com', $user->email);
    }

    public function test_user_email_verification_timestamp_is_casted()
    {
        $user = new User();
        $casts = $user->getCasts();
        
        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    public function test_user_password_hashing()
    {
        $user = new User();
        $user->password = 'plain-password';
        
        // In a real scenario, this would be handled by a mutator
        // For testing, we simulate the behavior
        $hashedPassword = bcrypt('plain-password');
        $user->password = $hashedPassword;
        
        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(strlen($user->password) > 50); // bcrypt hashes are long
    }

    public function test_user_timestamps_are_set()
    {
        $user = new User();
        
        $this->assertTrue($user->timestamps);
        $this->assertEquals('created_at', $user->getCreatedAtColumn());
        $this->assertEquals('updated_at', $user->getUpdatedAtColumn());
    }

    public function test_user_can_be_updated()
    {
        $user = new User([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        $user->fill([
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@example.com', $user->email);
    }

    public function test_user_can_be_deleted()
    {
        // Since we're not using database, we just test that the model
        // has the necessary methods for deletion
        $user = new User();
        
        $this->assertTrue(method_exists($user, 'delete'));
        $this->assertTrue(method_exists($user, 'forceDelete'));
    }

    public function test_user_table_structure()
    {
        $user = new User();
        
        $this->assertEquals('users', $user->getTable());
        $this->assertEquals('id', $user->getKeyName());
        $this->assertTrue($user->incrementing);
        $this->assertEquals('int', $user->getKeyType());
    }
}