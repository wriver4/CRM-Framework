<?php
/**
 * Test Configuration File
 * 
 * This file contains configuration settings for the test environment.
 * It helps manage different testing scenarios and database configurations.
 */

// Test Environment Configuration
define('TEST_ENVIRONMENT', 'development');
define('TESTING_MODE', 'local'); // Can be 'local' or 'remote'

// Database Configuration for Testing
// Note: Integration tests require a separate test database to avoid affecting production data
$test_db_config = [
    'host' => 'localhost',
    'database' => 'democrm_test', // Separate test database
    'username' => 'democrm_test_user',
    'password' => 'test_password',
    'charset' => 'utf8mb4'
];

// Test Data Configuration
$test_data_config = [
    'cleanup_after_tests' => true,
    'use_transactions' => true, // Rollback after each test
    'seed_test_data' => false
];

// Feature Test Configuration
$feature_test_config = [
    'base_url' => 'https://democrm.waveguardco.net',
    'require_authentication' => true,
    'test_user_credentials' => [
        'username' => 'test_user',
        'password' => 'test_password'
    ]
];

// Performance Test Configuration
$performance_config = [
    'max_execution_time' => 30, // seconds
    'memory_limit' => '256M',
    'large_dataset_size' => 1000,
    'stress_test_iterations' => 100
];

// Test Reporting Configuration
$reporting_config = [
    'generate_coverage_report' => false,
    'coverage_output_dir' => __DIR__ . '/coverage',
    'log_test_results' => true,
    'log_file' => __DIR__ . '/logs/test-results.log'
];

// Export configuration for use in tests
return [
    'environment' => TEST_ENVIRONMENT,
    'mode' => TESTING_MODE,
    'database' => $test_db_config,
    'test_data' => $test_data_config,
    'feature_tests' => $feature_test_config,
    'performance' => $performance_config,
    'reporting' => $reporting_config
];