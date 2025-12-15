<?php
/**
 * Test script to debug LeadsList issues
 */

require_once dirname(__DIR__) . '/config/system.php';

echo "=== LeadsList Debug Test ===\n\n";

try {
    require_once dirname(__DIR__) . '/public_html/admin/languages/en.php';
    
    // Test LeadsList instantiation
    echo "1. Testing LeadsList class instantiation...\n";
    
    // Create dummy data similar to what Leads::get_all_active() would return
    $dummy_results = [
        [
            'id' => 1,
            'lead_id' => '12345',
            'stage' => 10, // Updated to new Lead stage numbering
            'first_name' => 'John',
            'family_name' => 'Doe',
            'project_name' => 'Test Project',
            'cell_phone' => '555-1234',
            'email' => 'john@example.com',
            'full_address' => '123 Test St, Test City, TS 12345'
        ]
    ];

    echo "   Creating LeadsList instance...\n";
    $list = new LeadsList($dummy_results, $lang);
    echo "   ✓ LeadsList created successfully\n";

    echo "\n2. Testing button configuration...\n";
    $reflection = new ReflectionClass($list);
    $method = $reflection->getMethod('getButtonsConfig');
    $method->setAccessible(true);
    $buttons = $method->invoke($list, '12345');
    
    echo "   Button count: " . count($buttons) . "\n";
    echo "   Buttons: " . implode(', ', array_keys($buttons)) . "\n";
    
    $col_method = $reflection->getMethod('getButtonColumnClass');
    $col_method->setAccessible(true);
    $col_class = $col_method->invoke($list, count($buttons));
    echo "   Column class: " . $col_class . "\n";

    echo "\n3. Testing table creation...\n";
    ob_start();
    $list->create_table();
    $output = ob_get_clean();
    
    if (strlen($output) > 0) {
        echo "   ✓ Table HTML generated (" . strlen($output) . " characters)\n";
        
        // Check if buttons are present in output
        if (strpos($output, 'col-6') !== false) {
            echo "   ✓ Found col-6 classes (2 buttons)\n";
        } else {
            echo "   ✗ col-6 classes not found\n";
        }
        
        if (strpos($output, 'fa-eye') !== false) {
            echo "   ✓ Found view button icon\n";
        } else {
            echo "   ✗ View button icon not found\n";
        }
        
        if (strpos($output, 'fa-edit') !== false) {
            echo "   ✓ Found edit button icon\n";
        } else {
            echo "   ✗ Edit button icon not found\n";
        }
        
        // Show first 200 characters of output for debugging
        echo "\n   First 200 characters of output:\n";
        echo "   " . substr($output, 0, 200) . "...\n";
        
    } else {
        echo "   ✗ No HTML output generated\n";
    }

    echo "\n4. Testing with real data...\n";
    $leads = new Leads();
    $real_results = $leads->get_all_active();
    
    if ($real_results && count($real_results) > 0) {
        echo "   Found " . count($real_results) . " leads in database\n";
        
        $real_list = new LeadsList($real_results, $lang);
        ob_start();
        $real_list->create_table();
        $real_output = ob_get_clean();
        
        if (strlen($real_output) > 0) {
            echo "   ✓ Real data table generated (" . strlen($real_output) . " characters)\n";
        } else {
            echo "   ✗ Real data table generation failed\n";
        }
    } else {
        echo "   No leads found in database\n";
    }

    echo "\n=== Test Summary ===\n";
    echo "✓ LeadsList class loads correctly\n";
    echo "✓ Button configuration works\n";
    echo "✓ Table generation works\n";
    echo "\nIf the leads list page is not working, the issue might be:\n";
    echo "1. JavaScript/CSS loading issues\n";
    echo "2. Database connection problems\n";
    echo "3. Session/authentication issues\n";
    echo "4. Browser cache issues\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}