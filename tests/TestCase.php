<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test database connection
        $this->db = new \mysqli(
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_USER') ?: 'recipeadmin',
            getenv('DB_PASSWORD') ?: 'kod12345',
            getenv('DB_NAME') ?: 'recipedb'
        );

        if ($this->db->connect_error) {
            throw new \Exception('Database connection failed: ' . $this->db->connect_error);
        }
    }

    protected function tearDown(): void
    {
        if ($this->db) {
            $this->db->close();
        }
        parent::tearDown();
    }

    protected function createTestUser($email = 'testuser@test.com', $password = 'testpass123', $fname = 'Test', $lname = 'User')
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // SQL INSERT disesuaikan dengan kolom 'email', 'fname', 'lname', 'password' di tabel 'user'
        $sql = "INSERT INTO user (email, fname, lname, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ssss', $email, $fname, $lname, $hashedPassword);
        $stmt->execute();
        return $this->db->insert_id;
    }

    protected function createTestRecipe($userId, $title = 'Test Recipe')
    {
        $sql = "INSERT INTO recipes (user_id, title, ingredients, instructions) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $ingredients = 'Test ingredients';
        $instructions = 'Test instructions';
        $stmt->bind_param('isss', $userId, $title, $ingredients, $instructions);
        $stmt->execute();
        return $this->db->insert_id;
    }

    protected function cleanTestData()
    {
        $this->db->query("DELETE FROM recipes");
        $this->db->query("DELETE FROM user");
    }
} 