<?php

/**
 * Class Organization Integration Test
 * 
 * Tests that verify the reorganized class structure works correctly
 * and that all classes can be autoloaded from their new locations.
 * 
 * This test has long-term value for:
 * - Ensuring autoloader continues to work after changes
 * - Verifying class dependencies are maintained
 * - Detecting missing classes after reorganization
 * - Validating inheritance chains remain intact
 */

use PHPUnit\Framework\TestCase;

class ClassOrganizationTest extends TestCase
{
    /**
     * Test that all Core classes can be autoloaded
     */
    public function testCoreClassesAutoload()
    {
        $coreClasses = [
            'Database',
            'Security', 
            'Sessions',
            'Nonce'
        ];

        foreach ($coreClasses as $className) {
            $this->assertTrue(
                class_exists($className),
                "Core class {$className} should be autoloadable"
            );
        }
    }

    /**
     * Test that all Model classes can be autoloaded
     */
    public function testModelClassesAutoload()
    {
        $modelClasses = [
            'Users',
            'Leads',
            'Contacts',
            'Notes',
            'Communications',
            'Sales',
            'Roles',
            'Permissions',
            'RolesPermissions'
        ];

        foreach ($modelClasses as $className) {
            $this->assertTrue(
                class_exists($className),
                "Model class {$className} should be autoloadable"
            );
        }
    }

    /**
     * Test that all View classes can be autoloaded
     */
    public function testViewClassesAutoload()
    {
        $viewClasses = [
            'Table',
            'ViewTable',
            'ActionTable',
            'EditDeleteTable',
            'UsersList',
            'ContactsList',
            'LeadsList',
            'RolesList',
            'PermissionsList',
            'RolesPermissionsList'
        ];

        foreach ($viewClasses as $className) {
            $this->assertTrue(
                class_exists($className),
                "View class {$className} should be autoloadable"
            );
        }
    }

    /**
     * Test that all Utility classes can be autoloaded
     */
    public function testUtilityClassesAutoload()
    {
        $utilityClasses = [
            'Helpers',
            'FormComponents'
        ];

        foreach ($utilityClasses as $className) {
            $this->assertTrue(
                class_exists($className),
                "Utility class {$className} should be autoloadable"
            );
        }
    }

    /**
     * Test that all Logging classes can be autoloaded
     */
    public function testLoggingClassesAutoload()
    {
        $loggingClasses = [
            'Logit',
            'Audit',
            'AuditList',
            'InternalErrors',
            'PhpErrorLog'
        ];

        foreach ($loggingClasses as $className) {
            $this->assertTrue(
                class_exists($className),
                "Logging class {$className} should be autoloadable"
            );
        }
    }

    /**
     * Test that Database inheritance chain works correctly
     */
    public function testDatabaseInheritanceChain()
    {
        $databaseExtendingClasses = [
            'Helpers',
            'Users',
            'Leads',
            'Contacts',
            'Notes',
            'Communications',
            'Sales',
            'Roles',
            'Permissions',
            'RolesPermissions'
        ];

        foreach ($databaseExtendingClasses as $className) {
            $this->assertTrue(
                is_subclass_of($className, 'Database'),
                "Class {$className} should extend Database"
            );
        }
    }

    /**
     * Test that Table inheritance chain works correctly
     */
    public function testTableInheritanceChain()
    {
        $tableExtendingClasses = [
            'ViewTable',
            'ActionTable',
            'EditDeleteTable'
        ];

        foreach ($tableExtendingClasses as $className) {
            $this->assertTrue(
                is_subclass_of($className, 'Table'),
                "Class {$className} should extend Table"
            );
        }
    }

    /**
     * Test that specific list classes extend correct base classes
     */
    public function testListClassInheritance()
    {
        $listClassMappings = [
            'UsersList' => 'EditDeleteTable',
            'ContactsList' => 'EditDeleteTable',
            'LeadsList' => 'EditDeleteTable',
            'RolesList' => 'EditDeleteTable',
            'PermissionsList' => 'EditDeleteTable',
            'RolesPermissionsList' => 'EditDeleteTable',
            'AuditList' => 'ViewTable'
        ];

        foreach ($listClassMappings as $childClass => $parentClass) {
            $this->assertTrue(
                is_subclass_of($childClass, $parentClass),
                "Class {$childClass} should extend {$parentClass}"
            );
        }
    }

    /**
     * Test that classes can be instantiated without errors
     */
    public function testClassInstantiation()
    {
        // Test core classes that can be safely instantiated
        $instantiableClasses = [
            'Database',
            'Helpers',
            'Nonce'
        ];

        foreach ($instantiableClasses as $className) {
            try {
                $instance = new $className();
                $this->assertInstanceOf($className, $instance);
            } catch (Exception $e) {
                $this->fail("Failed to instantiate {$className}: " . $e->getMessage());
            }
        }
    }

    /**
     * Test that the multilingual Helpers class maintains its functionality
     */
    public function testHelpersMultilingualFunctionality()
    {
        $helpers = new Helpers();
        
        // Test that key multilingual methods exist
        $this->assertTrue(
            method_exists($helpers, 'get_role_array'),
            'Helpers should have get_role_array method for multilingual support'
        );
        
        $this->assertTrue(
            method_exists($helpers, 'get_system_state_array'),
            'Helpers should have get_system_state_array method for multilingual support'
        );
        
        $this->assertTrue(
            method_exists($helpers, 'get_us_states_array'),
            'Helpers should have get_us_states_array method for multilingual support'
        );
    }

    /**
     * Test that Database connection method is accessible through inheritance
     */
    public function testDatabaseConnectionAccessibility()
    {
        $helpers = new Helpers();
        
        $this->assertTrue(
            method_exists($helpers, 'dbcrm'),
            'Classes extending Database should have access to dbcrm() method'
        );
    }

    /**
     * Test directory structure exists as expected
     */
    public function testDirectoryStructureExists()
    {
        $expectedDirectories = [
            'Core',
            'Models', 
            'Views',
            'Utilities',
            'Logging'
        ];

        $classesPath = dirname(__DIR__, 3) . '/classes';
        
        foreach ($expectedDirectories as $directory) {
            $dirPath = $classesPath . '/' . $directory;
            $this->assertTrue(
                is_dir($dirPath),
                "Directory {$directory} should exist in classes folder"
            );
        }
    }

    /**
     * Test that specific files exist in their expected locations
     */
    public function testFileLocations()
    {
        $expectedFileLocations = [
            'Core/Database.php',
            'Core/Security.php',
            'Models/Users.php',
            'Models/Leads.php',
            'Core/Table.php',
            'Core/ViewTable.php',
            'Core/EditDeleteTable.php',
            'Core/ActionTable.php',
            'Views/UsersList.php',
            'Views/LeadsList.php',
            'Views/ContactsList.php',
            'Utilities/Helpers.php',
            'Logging/Audit.php'
        ];

        $classesPath = dirname(__DIR__, 3) . '/classes';
        
        foreach ($expectedFileLocations as $filePath) {
            $fullPath = $classesPath . '/' . $filePath;
            $this->assertTrue(
                file_exists($fullPath),
                "File should exist at {$filePath}"
            );
        }
    }
}