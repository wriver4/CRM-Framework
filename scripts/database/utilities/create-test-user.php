<?php
/**
 * Create Test Database User
 */

echo "\n🔄 Setting up test database user...\n\n";

try {
    // Connect as the production user (who has admin privileges)
    $pdo = new PDO(
        "mysql:host=localhost",
        'democrm_democrm',
        'b3J2sy5T4JNm60'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL statements to set up test user
    $statements = [
        "CREATE USER IF NOT EXISTS 'democrm_test'@'localhost' IDENTIFIED BY 'TestDB_2025_Secure!'",
        "GRANT ALL PRIVILEGES ON `democrm_test`.* TO 'democrm_test'@'localhost'",
        "GRANT ALL PRIVILEGES ON `democrm_test_%`.* TO 'democrm_test'@'localhost'",
        "GRANT CREATE, DROP ON *.* TO 'democrm_test'@'localhost'",
        "FLUSH PRIVILEGES"
    ];
    
    foreach ($statements as $i => $sql) {
        try {
            $pdo->exec($sql);
            echo "✓ Step " . ($i + 1) . ": Success\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "⊘ Step " . ($i + 1) . ": Already exists (skipped)\n";
            } else {
                echo "❌ Step " . ($i + 1) . ": " . $e->getMessage() . "\n";
                throw $e;
            }
        }
    }
    
    // Verify user exists
    $result = $pdo->query("SELECT User, Host FROM mysql.user WHERE User = 'democrm_test'");
    if ($result->rowCount() > 0) {
        echo "\n✅ Test user 'democrm_test' is ready!\n\n";
    } else {
        echo "\n⚠️  User creation verification failed\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    exit(1);
}
?>