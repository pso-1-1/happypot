<?php

namespace Tests;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanTestData();
    }

    public function testUserRegistration()
    {
        $password = 'newpass123';
        $email = 'newuser@test.com';

        // Test registration
        $userId = $this->createTestUser($email, $password);

        // Verify user was created
        $sql = "SELECT * FROM user WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $this->assertNotFalse($user);
        $this->assertEquals($email, $user['email']);
        $this->assertTrue(password_verify($password, $user['password']));
    }

    public function testUserLogin()
    {
        $email = 'newuser@test.com';
        $password = 'testpass123';
        
        // Create test user
        $userId = $this->createTestUser($email, $password);

        // Test login
        $sql = "SELECT * FROM user WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $this->assertNotFalse($user);
        $this->assertTrue(password_verify($password, $user['password']));
    }

    public function testDuplicatEmail()
    {
        $email = 'duplicateuser';
        
        // Create first user
        $this->createTestUser($email);

        // Try to create second user with same email
        $this->expectException(\Exception::class);
        $this->createTestUser($email);
    }

    public function testInvalidLogin()
    {
        $email = 'newuser@test.com';
        $password = 'testpass123';
        
        // Create test user
        $this->createTestUser($email, $password);

        // Test invalid password
        $sql = "SELECT * FROM user WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $this->assertNotFalse($user);
        $this->assertFalse(password_verify('wrongpassword', $user['password']));
    }
} 