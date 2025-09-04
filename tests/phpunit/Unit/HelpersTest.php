<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Helpers class
 */
class HelpersTest extends TestCase
{
    private $helpers;

    protected function setUp(): void
    {
        // Classes are loaded via autoloader in bootstrap
        $this->helpers = new \Helpers();
    }

    public function testHelpersClassExists()
    {
        $this->assertInstanceOf(\Helpers::class, $this->helpers);
    }

    public function testHashPasswordMethod()
    {
        $password = 'testpassword123';
        $hash = $this->helpers->hash_password($password);
        
        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertTrue(password_verify($password, $hash));
    }

    public function testHashPasswordDifferentResults()
    {
        $password = 'testpassword123';
        $hash1 = $this->helpers->hash_password($password);
        $hash2 = $this->helpers->hash_password($password);
        
        // Hashes should be different due to salt
        $this->assertNotEquals($hash1, $hash2);
        
        // But both should verify correctly
        $this->assertTrue(password_verify($password, $hash1));
        $this->assertTrue(password_verify($password, $hash2));
    }

    public function testGetRoleArrayMethod()
    {
        // Create a mock language array
        $lang = [
            'role_id_21' => 'Super Admin',
            'role_id_20' => 'Admin',
            'role_id_19' => 'Manager',
            'role_id_18' => 'User',
            'role_id_17' => 'Guest',
            'role_id_16' => 'Role 16',
            'role_id_15' => 'Role 15',
            'role_id_14' => 'Role 14',
            'role_id_13' => 'Role 13',
            'role_id_12' => 'Role 12',
            'role_id_11' => 'Role 11',
            'role_id_10' => 'Role 10',
            'role_id_9' => 'Role 9',
            'role_id_8' => 'Role 8',
            'role_id_7' => 'Role 7',
            'role_id_6' => 'Role 6',
            'role_id_5' => 'Role 5',
            'role_id_4' => 'Role 4',
            'role_id_3' => 'Role 3',
            'role_id_2' => 'Role 2',
            'role_id_1' => 'Role 1'
        ];

        $roleArray = $this->helpers->get_role_array($lang);
        
        $this->assertIsArray($roleArray);
        $this->assertArrayHasKey('21', $roleArray);
        $this->assertEquals('Super Admin', $roleArray['21']);
        $this->assertArrayHasKey('20', $roleArray);
        $this->assertEquals('Admin', $roleArray['20']);
    }

    public function testUnsetPageVariablesMethod()
    {
        // This method doesn't return anything, just test it doesn't throw errors
        $this->helpers->unset_page_variables();
        $this->assertTrue(true); // If we get here, the method executed without errors
    }
}