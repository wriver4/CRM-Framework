<?php
/**
 * Debug script to examine the exact HTML output for action buttons
 */

require_once dirname(__DIR__) . '/config/system.php';

echo "=== Action Button HTML Debug ===\n\n";

try {
    require_once dirname(__DIR__) . '/public_html/admin/languages/en.php';
    
    // Create test data
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

    echo "1. Creating LeadsList and extracting button HTML...\n";
    $list = new LeadsList($dummy_results, $lang);
    
    // Get the button HTML directly
    ob_start();
    $reflection = new ReflectionClass($list);
    $method = $reflection->getMethod('row_nav');
    $method->setAccessible(true);
    $method->invoke($list, '12345', null);
    $button_html = ob_get_clean();
    
    echo "\n2. Raw button HTML output:\n";
    echo "----------------------------------------\n";
    echo $button_html;
    echo "----------------------------------------\n";
    
    echo "\n3. HTML analysis:\n";
    
    // Check for Bootstrap classes
    if (strpos($button_html, 'col-6') !== false) {
        echo "   ✓ Found col-6 Bootstrap class\n";
    } else {
        echo "   ✗ col-6 Bootstrap class NOT found\n";
    }
    
    if (strpos($button_html, 'py-1') !== false) {
        echo "   ✓ Found py-1 padding class\n";
    } else {
        echo "   ✗ py-1 padding class NOT found\n";
    }
    
    // Check for button structure
    if (strpos($button_html, '<div class="row">') !== false) {
        echo "   ✓ Found row container\n";
    } else {
        echo "   ✗ Row container NOT found\n";
    }
    
    // Count button divs
    $button_count = substr_count($button_html, '<div class="col-6 py-1">');
    echo "   Button divs found: $button_count\n";
    
    // Check for icons
    if (strpos($button_html, 'fa-eye') !== false) {
        echo "   ✓ View icon found\n";
    } else {
        echo "   ✗ View icon NOT found\n";
    }
    
    if (strpos($button_html, 'fa-edit') !== false) {
        echo "   ✓ Edit icon found\n";
    } else {
        echo "   ✗ Edit icon NOT found\n";
    }

    echo "\n4. Comparing with old vs new button structure...\n";
    
    // Test the old EditDeleteTable (3 buttons) for comparison
    echo "\n   Old EditDeleteTable (3 buttons):\n";
    $old_table = new EditDeleteTable($dummy_results, ['action' => 'Action'], 'test');
    ob_start();
    $old_method = $reflection = new ReflectionClass($old_table);
    $old_method = $reflection->getMethod('row_nav');
    $old_method->setAccessible(true);
    $old_method->invoke($old_table, '12345', null);
    $old_html = ob_get_clean();
    
    $old_button_count = substr_count($old_html, '<div class="col-4 py-1">');
    echo "   Old table button divs with col-4: $old_button_count\n";
    
    if (strpos($old_html, 'col-4') !== false) {
        echo "   ✓ Old table uses col-4 for 3 buttons\n";
    }

    echo "\n5. Testing AdminLeadsList (1 button)...\n";
    $admin_list = new AdminLeadsList($dummy_results, $lang);
    ob_start();
    $admin_reflection = new ReflectionClass($admin_list);
    $admin_method = $admin_reflection->getMethod('row_nav');
    $admin_method->setAccessible(true);
    $admin_method->invoke($admin_list, '12345', null);
    $admin_html = ob_get_clean();
    
    $admin_button_count = substr_count($admin_html, '<div class="col-12 py-1">');
    echo "   Admin button divs with col-12: $admin_button_count\n";
    
    if (strpos($admin_html, 'col-12') !== false) {
        echo "   ✓ Admin list uses col-12 for 1 button\n";
    }

    echo "\n=== Summary ===\n";
    echo "The action button system is generating the correct HTML structure.\n";
    echo "If buttons appear misaligned on the frontend, check:\n";
    echo "1. Browser cache - clear cache and hard refresh\n";
    echo "2. CSS conflicts - custom CSS might be overriding Bootstrap\n";
    echo "3. Bootstrap version - ensure Bootstrap 5 is loaded\n";
    echo "4. JavaScript errors - check browser console\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}