<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for EmailAccountManager class
 */
class EmailAccountManagerTest extends TestCase
{
    private $manager;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../../classes/Models/EmailAccountManager.php';
        $this->manager = new EmailAccountManager();
    }

    public function testValidateAccountConfig()
    {
        $validConfig = [
            'email_address' => 'test@example.com',
            'form_type' => 'estimate',
            'imap_host' => 'mail.example.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'username' => 'test@example.com',
            'password' => 'encrypted_password'
        ];

        $result = $this->manager->validateAccountConfig($validConfig);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);

        $invalidConfig = [
            'email_address' => 'invalid-email',
            'form_type' => 'invalid_type',
            'imap_host' => '',
            'imap_port' => 'not_a_number',
            'username' => '',
            'password' => ''
        ];

        $result = $this->manager->validateAccountConfig($invalidConfig);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testEncryptDecryptPassword()
    {
        $plainPassword = 'test_password_123';
        
        $encrypted = $this->manager->encryptPassword($plainPassword);
        $this->assertNotEquals($plainPassword, $encrypted);
        $this->assertNotEmpty($encrypted);

        $decrypted = $this->manager->decryptPassword($encrypted);
        $this->assertEquals($plainPassword, $decrypted);
    }

    public function testValidateEmailAddress()
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.co.uk',
            'admin+test@company.org'
        ];

        foreach ($validEmails as $email) {
            $this->assertTrue($this->manager->isValidEmail($email));
        }

        $invalidEmails = [
            'invalid-email',
            '@domain.com',
            'user@',
            'user name@domain.com',
            ''
        ];

        foreach ($invalidEmails as $email) {
            $this->assertFalse($this->manager->isValidEmail($email));
        }
    }

    public function testValidateFormType()
    {
        $validTypes = ['estimate', 'ltr', 'contact'];
        
        foreach ($validTypes as $type) {
            $this->assertTrue($this->manager->isValidFormType($type));
        }

        $invalidTypes = ['invalid', 'test', '', null];
        
        foreach ($invalidTypes as $type) {
            $this->assertFalse($this->manager->isValidFormType($type));
        }
    }

    public function testValidateImapSettings()
    {
        $validSettings = [
            'imap_host' => 'mail.example.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl'
        ];

        $result = $this->manager->validateImapSettings($validSettings);
        $this->assertTrue($result['valid']);

        $invalidSettings = [
            'imap_host' => '',
            'imap_port' => 'invalid',
            'imap_encryption' => 'invalid'
        ];

        $result = $this->manager->validateImapSettings($invalidSettings);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testImapPortValidation()
    {
        $validPorts = [143, 993, 995, 110];
        
        foreach ($validPorts as $port) {
            $this->assertTrue($this->manager->isValidImapPort($port));
        }

        $invalidPorts = [0, -1, 99999, 'string', null];
        
        foreach ($invalidPorts as $port) {
            $this->assertFalse($this->manager->isValidImapPort($port));
        }
    }

    public function testImapEncryptionValidation()
    {
        $validEncryptions = ['ssl', 'tls', 'none'];
        
        foreach ($validEncryptions as $encryption) {
            $this->assertTrue($this->manager->isValidEncryption($encryption));
        }

        $invalidEncryptions = ['invalid', 'https', '', null];
        
        foreach ($invalidEncryptions as $encryption) {
            $this->assertFalse($this->manager->isValidEncryption($encryption));
        }
    }

    public function testGenerateAccountData()
    {
        $inputData = [
            'email_address' => 'test@example.com',
            'form_type' => 'estimate',
            'imap_host' => 'mail.example.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'username' => 'test@example.com',
            'password' => 'plain_password',
            'is_active' => 1
        ];

        $accountData = $this->manager->generateAccountData($inputData);

        $this->assertIsArray($accountData);
        $this->assertEquals('test@example.com', $accountData['email_address']);
        $this->assertEquals('estimate', $accountData['form_type']);
        $this->assertEquals('mail.example.com', $accountData['imap_host']);
        $this->assertEquals(993, $accountData['imap_port']);
        $this->assertEquals('ssl', $accountData['imap_encryption']);
        $this->assertEquals('test@example.com', $accountData['username']);
        $this->assertNotEquals('plain_password', $accountData['password']); // Should be encrypted
        $this->assertEquals(1, $accountData['is_active']);
        $this->assertArrayHasKey('created_at', $accountData);
    }

    public function testAccountStatusMethods()
    {
        // Test status validation
        $this->assertTrue($this->manager->isValidStatus(1));
        $this->assertTrue($this->manager->isValidStatus(0));
        $this->assertFalse($this->manager->isValidStatus(2));
        $this->assertFalse($this->manager->isValidStatus('string'));

        // Test status conversion
        $this->assertEquals(1, $this->manager->normalizeStatus(true));
        $this->assertEquals(0, $this->manager->normalizeStatus(false));
        $this->assertEquals(1, $this->manager->normalizeStatus('1'));
        $this->assertEquals(0, $this->manager->normalizeStatus('0'));
    }

    public function testConnectionStringGeneration()
    {
        $config = [
            'imap_host' => 'mail.example.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl'
        ];

        $connectionString = $this->manager->generateConnectionString($config);
        
        $this->assertIsString($connectionString);
        $this->assertStringContains('mail.example.com', $connectionString);
        $this->assertStringContains('993', $connectionString);
        $this->assertStringContains('ssl', $connectionString);
    }

    public function testErrorMessageGeneration()
    {
        $errors = [
            'email_address' => 'Invalid email format',
            'imap_host' => 'Host is required'
        ];

        $message = $this->manager->formatErrorMessage($errors);
        
        $this->assertIsString($message);
        $this->assertStringContains('Invalid email format', $message);
        $this->assertStringContains('Host is required', $message);
    }
}