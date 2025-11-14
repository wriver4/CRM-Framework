#!/usr/bin/env php
<?php
/**
 * Try to find what password matches the hash
 */

$hash = '*6D4ADF073FB0AADD25FCF73C815D1CEB7A17DE1F';

// Common passwords to try
$passwords = [
    'TestDB_2025_Secure!',
    'democrm_test',
    'test',
    'password',
    'democrm',
    '',
];

echo "Testing passwords against hash: $hash\n\n";

foreach ($passwords as $password) {
    $testHash = '*' . strtoupper(sha1(sha1($password, true)));
    $match = ($testHash === $hash) ? '✅ MATCH!' : '';
    echo sprintf("%-25s -> %s %s\n", "'$password'", $testHash, $match);
}

echo "\n";
echo "If none match, the password was set to something else.\n";
echo "What password do you want to use for democrm_test?\n";