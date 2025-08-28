<?php
/**
 * Check lead_id field relationships between leads and contacts tables
 */

// Minimal setup to avoid logging issues
error_reporting(E_ERROR | E_PARSE);

try {
    // Direct database connection
    $dsn = 'mysql:host=localhost;dbname=democrm_democrm;charset=utf8mb4';
    $username = 'democrm_democrm';
    $password = 'b3J2sy5T4JNm60';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "=== CHECKING LEAD_ID FIELD RELATIONSHIPS ===\n\n";
    
    // Check leads table structure
    echo "1. LEADS TABLE STRUCTURE:\n";
    $stmt = $pdo->query("DESCRIBE leads");
    $leads_columns = $stmt->fetchAll();
    
    foreach ($leads_columns as $col) {
        if (in_array($col['Field'], ['id', 'lead_id', 'lead_number'])) {
            echo "   {$col['Field']}: {$col['Type']} {$col['Key']} {$col['Extra']}\n";
        }
    }
    
    // Check contacts table structure
    echo "\n2. CONTACTS TABLE STRUCTURE:\n";
    $stmt = $pdo->query("DESCRIBE contacts");
    $contacts_columns = $stmt->fetchAll();
    
    foreach ($contacts_columns as $col) {
        if (in_array($col['Field'], ['id', 'lead_id'])) {
            echo "   {$col['Field']}: {$col['Type']} {$col['Key']} {$col['Extra']}\n";
        }
    }
    
    // Check sample data from leads
    echo "\n3. SAMPLE LEADS DATA:\n";
    $stmt = $pdo->query("SELECT id, lead_id FROM leads ORDER BY id DESC LIMIT 5");
    $leads_sample = $stmt->fetchAll();
    
    foreach ($leads_sample as $lead) {
        echo "   Lead DB ID: {$lead['id']}, Lead ID: {$lead['lead_id']}\n";
    }
    
    // Check sample data from contacts
    echo "\n4. SAMPLE CONTACTS DATA:\n";
    $stmt = $pdo->query("SELECT id, lead_id FROM contacts WHERE lead_id IS NOT NULL ORDER BY id DESC LIMIT 5");
    $contacts_sample = $stmt->fetchAll();
    
    foreach ($contacts_sample as $contact) {
        echo "   Contact DB ID: {$contact['id']}, Lead ID: {$contact['lead_id']}\n";
    }
    
    // Check for mismatches
    echo "\n5. CHECKING FOR MISMATCHES:\n";
    $stmt = $pdo->query("
        SELECT 
            l.id as lead_db_id,
            l.lead_id as lead_field_id,
            c.id as contact_db_id,
            c.lead_id as contact_lead_id
        FROM leads l
        LEFT JOIN contacts c ON l.id = c.lead_id
        WHERE c.lead_id IS NOT NULL
        AND l.lead_id != c.lead_id
        LIMIT 10
    ");
    $mismatches = $stmt->fetchAll();
    
    if (empty($mismatches)) {
        echo "   ✓ No mismatches found between leads.lead_id and contacts.lead_id\n";
    } else {
        echo "   ✗ MISMATCHES FOUND:\n";
        foreach ($mismatches as $mismatch) {
            echo "     Lead DB ID {$mismatch['lead_db_id']}: lead_id={$mismatch['lead_field_id']}, but contact has lead_id={$mismatch['contact_lead_id']}\n";
        }
    }
    
    // Check what contacts.lead_id should reference
    echo "\n6. UNDERSTANDING THE RELATIONSHIP:\n";
    echo "   The contacts.lead_id field should reference:\n";
    echo "   - leads.id (the database primary key) OR\n";
    echo "   - leads.lead_id (the business identifier)\n";
    
    $stmt = $pdo->query("
        SELECT 
            'Using leads.id' as relationship_type,
            COUNT(*) as matching_records
        FROM leads l
        INNER JOIN contacts c ON l.id = c.lead_id
        
        UNION ALL
        
        SELECT 
            'Using leads.lead_id' as relationship_type,
            COUNT(*) as matching_records
        FROM leads l
        INNER JOIN contacts c ON l.lead_id = c.lead_id
    ");
    $relationships = $stmt->fetchAll();
    
    foreach ($relationships as $rel) {
        echo "   {$rel['relationship_type']}: {$rel['matching_records']} matching records\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}