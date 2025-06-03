<?php

namespace Tests;

class RecipeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanTestData();
    }

    public function testCreateRecipe()
    {
        // Create test user
        $userId = $this->createTestUser();

        // Create test recipe
        $recipeId = $this->createTestRecipe($userId);

        // Verify recipe was created
        $sql = "SELECT * FROM recipe WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $recipeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $recipe = $result->fetch_assoc();

        $this->assertNotFalse($recipe);
        $this->assertEquals($userId, $recipe['user_id']);
        $this->assertEquals('Test Recipe', $recipe['title']);
        $this->assertEquals('Test ingredients', $recipe['ingredients']);
        $this->assertEquals('Test instructions', $recipe['instructions']);
    }

    public function testUpdateRecipe()
    {
        // Create test user and recipe
        $userId = $this->createTestUser();
        $recipeId = $this->createTestRecipe($userId);

        // Update recipe
        $newTitle = 'Updated Recipe';
        $newIngredients = 'Updated ingredients';
        $newInstructions = 'Updated instructions';

        $sql = "UPDATE recipe SET title = ?, ingredients = ?, instructions = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssi', $newTitle, $newIngredients, $newInstructions, $recipeId);
        $stmt->execute();

        // Verify update
        $sql = "SELECT * FROM recipe WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $recipeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $recipe = $result->fetch_assoc();

        $this->assertEquals($newTitle, $recipe['title']);
        $this->assertEquals($newIngredients, $recipe['ingredients']);
        $this->assertEquals($newInstructions, $recipe['instructions']);
    }

    public function testDeleteRecipe()
    {
        // Create test user and recipe
        $userId = $this->createTestUser();
        $recipeId = $this->createTestRecipe($userId);

        // Delete recipe
        $sql = "DELETE FROM recipe WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $recipeId);
        $stmt->execute();

        // Verify deletion
        $sql = "SELECT * FROM recipe WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $recipeId);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(0, $result->num_rows);
    }

    public function testListUserRecipe()
    {
        // Create test user
        $userId = $this->createTestUser();

        // Create multiple recipe
        $this->createTestRecipe($userId, 'Recipe 1');
        $this->createTestRecipe($userId, 'Recipe 2');
        $this->createTestRecipe($userId, 'Recipe 3');

        // Get user's recipe
        $sql = "SELECT * FROM recipe WHERE user_id = ? ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $recipe = $result->fetch_all(MYSQLI_ASSOC);

        $this->assertCount(3, $recipe);
        $this->assertEquals('Recipe 3', $recipe[0]['title']);
        $this->assertEquals('Recipe 2', $recipe[1]['title']);
        $this->assertEquals('Recipe 1', $recipe[2]['title']);
    }

    public function testRecipeSearch()
    {
        // Create test user
        $userId = $this->createTestUser();

        // Create recipe with different titles
        $this->createTestRecipe($userId, 'Chicken Curry');
        $this->createTestRecipe($userId, 'Beef Steak');
        $this->createTestRecipe($userId, 'Vegetable Soup');

        // Search for recipe containing 'Chicken'
        $searchTerm = '%Chicken%';
        $sql = "SELECT * FROM recipe WHERE title LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $recipe = $result->fetch_all(MYSQLI_ASSOC);

        $this->assertCount(1, $recipe);
        $this->assertEquals('Chicken Curry', $recipe[0]['title']);
    }
} 