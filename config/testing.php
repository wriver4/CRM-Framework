<?php

/**
 * Testing Configuration
 * 
 * This file contains all testing-related configuration including:
 * - Test database settings
 * - Test mode toggles
 * - Test data seeding options
 * - PHPUnit and Playwright integration settings
 */

return [
    
    // ============================================
    // TESTING MODE CONFIGURATION
    // ============================================
    
    'enabled' => getenv('TESTING_MODE') === 'true' || (php_sapi_name() === 'cli' && isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing'),
    
    'mode' => getenv('TESTING_MODE_TYPE') ?: 'auto', // 'auto', 'persistent', 'ephemeral'
    
    // ============================================
    // TEST DATABASE CONFIGURATION
    // ============================================
    
    'database' => [
        // Ephemeral test database (created/destroyed per test run)
        'ephemeral' => [
            'host' => getenv('TEST_DB_HOST') ?: 'localhost',
            'name' => getenv('TEST_DB_NAME') ?: 'democrm_test_ephemeral',
            'username' => getenv('TEST_DB_USER') ?: 'democrm_test',
            'password' => getenv('TEST_DB_PASS') ?: 'TestDB_2025_Secure!',
            'charset' => 'utf8mb4',
            'auto_create' => true,
            'auto_destroy' => true,
        ],
        
        // Persistent test database (reset between tests but not destroyed)
        'persistent' => [
            'host' => getenv('TEST_DB_HOST') ?: 'localhost',
            'name' => getenv('TEST_DB_NAME') ?: 'democrm_test',
            'username' => getenv('TEST_DB_USER') ?: 'democrm_test',
            'password' => getenv('TEST_DB_PASS') ?: 'TestDB_2025_Secure!',
            'charset' => 'utf8mb4',
            'auto_create' => true,
            'auto_destroy' => false,
            'auto_reset' => true,
        ],
        
        // Database snapshot configuration
        'snapshots' => [
            'enabled' => true,
            'directory' => __DIR__ . '/../tests/snapshots',
            'auto_snapshot' => true, // Create snapshot before each test suite
            'auto_restore' => true,  // Restore snapshot after each test suite
        ],
    ],
    
    // ============================================
    // TEST DATA SEEDING
    // ============================================
    
    'seeding' => [
        'enabled' => true,
        'auto_seed' => true, // Automatically seed data before tests
        
        // Seed data sets
        'datasets' => [
            'minimal' => [
                'users' => 2,      // Admin + Regular user
                'roles' => 3,      // Admin, Manager, User
                'permissions' => 10, // Basic permissions
            ],
            'standard' => [
                'users' => 5,
                'roles' => 5,
                'permissions' => 50,
                'leads' => 20,
                'contacts' => 30,
            ],
            'full' => [
                'users' => 20,
                'roles' => 10,
                'permissions' => 100,
                'leads' => 100,
                'contacts' => 150,
                'notes' => 200,
            ],
        ],
        
        // Default dataset for tests
        'default_dataset' => 'standard',
        
        // RBAC-specific test data
        'rbac' => [
            'test_roles' => [
                'super_admin' => 'Full system access',
                'sales_manager' => 'Sales module management',
                'sales_rep' => 'Sales module read/write',
                'viewer' => 'Read-only access',
                'restricted' => 'Minimal access for testing denials',
            ],
            'test_permissions' => [
                // Module-level
                'leads.access', 'contacts.access', 'admin.access',
                // Action-level
                'leads.view', 'leads.create', 'leads.edit', 'leads.delete', 'leads.export',
                'contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete',
                // Field-level
                'leads.view.email', 'leads.edit.stage', 'leads.view.notes',
                // Record-level
                'leads.view.own', 'leads.edit.own', 'leads.view.team', 'leads.view.all',
            ],
        ],
    ],
    
    // ============================================
    // PHPUNIT CONFIGURATION
    // ============================================
    
    'phpunit' => [
        'bootstrap' => __DIR__ . '/../tests/bootstrap.php',
        'test_suites' => [
            'unit' => __DIR__ . '/../tests/phpunit/Unit',
            'integration' => __DIR__ . '/../tests/phpunit/Integration',
            'feature' => __DIR__ . '/../tests/phpunit/Feature',
        ],
        
        // Test isolation
        'isolation' => [
            'database' => true,  // Isolate database per test
            'session' => true,   // Isolate session per test
            'files' => true,     // Isolate file system per test
        ],
        
        // Performance
        'parallel' => [
            'enabled' => true,
            'processes' => 4,
        ],
    ],
    
    // ============================================
    // PLAYWRIGHT CONFIGURATION
    // ============================================
    
    'playwright' => [
        'base_url' => getenv('PLAYWRIGHT_BASE_URL') ?: 'https://democrm.waveguardco.net',
        'headless' => getenv('PLAYWRIGHT_HEADLESS') !== 'false',
        'slow_mo' => (int) (getenv('PLAYWRIGHT_SLOW_MO') ?: 0),
        
        // Test users for E2E testing
        'test_users' => [
            'admin' => [
                'username' => 'test_admin',
                'password' => 'test_admin_password',
                'role' => 'admin',
            ],
            'manager' => [
                'username' => 'test_manager',
                'password' => 'test_manager_password',
                'role' => 'manager',
            ],
            'user' => [
                'username' => 'test_user',
                'password' => 'test_user_password',
                'role' => 'user',
            ],
            'restricted' => [
                'username' => 'test_restricted',
                'password' => 'test_restricted_password',
                'role' => 'restricted',
            ],
        ],
        
        // Browser configuration
        'browsers' => ['chromium', 'firefox', 'webkit'],
        'viewport' => [
            'width' => 1280,
            'height' => 720,
        ],
        
        // Screenshots and videos
        'screenshots' => [
            'enabled' => true,
            'on_failure' => true,
            'directory' => __DIR__ . '/../tests/screenshots',
        ],
        'videos' => [
            'enabled' => true,
            'on_failure' => true,
            'directory' => __DIR__ . '/../tests/videos',
        ],
    ],
    
    // ============================================
    // TEST HELPERS & UTILITIES
    // ============================================
    
    'helpers' => [
        'factories' => __DIR__ . '/../tests/phpunit/Helpers/Factories',
        'fixtures' => __DIR__ . '/../tests/phpunit/Fixtures',
        'mocks' => __DIR__ . '/../tests/phpunit/Mocks',
    ],
    
    // ============================================
    // DEBUGGING & LOGGING
    // ============================================
    
    'debug' => [
        'enabled' => getenv('TEST_DEBUG') === 'true',
        'sql_logging' => true,
        'verbose' => getenv('TEST_VERBOSE') === 'true',
        'log_file' => __DIR__ . '/../logs/test.log',
    ],
    
    // ============================================
    // CLEANUP CONFIGURATION
    // ============================================
    
    'cleanup' => [
        'auto_cleanup' => true,
        'cleanup_on_success' => true,
        'cleanup_on_failure' => false, // Keep data for debugging
        'cleanup_screenshots' => false, // Keep screenshots
        'cleanup_logs' => false,        // Keep logs
    ],
];