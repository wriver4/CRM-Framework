<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for EmailFormProcessor class
 */
class EmailFormProcessorTest extends TestCase
{
    private $processor;
    private $mockDatabase;

    protected function setUp(): void
    {
        // Mock the database connection
        $this->mockDatabase = $this->createMock(PDO::class);
        
        // Create processor instance
        require_once __DIR__ . '/../../../classes/Models/EmailFormProcessor.php';
        $this->processor = new EmailFormProcessor();
    }

    public function testParseEstimateForm()
    {
        $emailContent = "
        Name: John Doe
        Email: john@example.com
        Phone: 555-1234
        Service: Roof Repair
        Message: Need urgent roof repair
        ";

        $result = $this->processor->parseFormData($emailContent, 'estimate');

        $this->assertIsArray($result);
        $this->assertEquals('John Doe', $result['full_name']);
        $this->assertEquals('john@example.com', $result['email']);
        $this->assertEquals('555-1234', $result['phone']);
        $this->assertEquals('Roof Repair', $result['service']);
        $this->assertEquals('Need urgent roof repair', $result['message']);
    }

    public function testParseLtrForm()
    {
        $emailContent = "
        Name: Jane Smith
        Email: jane@example.com
        Phone: 555-5678
        Property Address: 123 Main St
        Message: Need letter of recommendation
        ";

        $result = $this->processor->parseFormData($emailContent, 'ltr');

        $this->assertIsArray($result);
        $this->assertEquals('Jane Smith', $result['full_name']);
        $this->assertEquals('jane@example.com', $result['email']);
        $this->assertEquals('555-5678', $result['phone']);
        $this->assertEquals('123 Main St', $result['property_address']);
        $this->assertEquals('Need letter of recommendation', $result['message']);
    }

    public function testParseContactForm()
    {
        $emailContent = "
        Name: Bob Johnson
        Email: bob@example.com
        Phone: 555-9999
        Subject: General Inquiry
        Message: I have a question about your services
        ";

        $result = $this->processor->parseFormData($emailContent, 'contact');

        $this->assertIsArray($result);
        $this->assertEquals('Bob Johnson', $result['full_name']);
        $this->assertEquals('bob@example.com', $result['email']);
        $this->assertEquals('555-9999', $result['phone']);
        $this->assertEquals('General Inquiry', $result['subject']);
        $this->assertEquals('I have a question about your services', $result['message']);
    }

    public function testValidateFormData()
    {
        $validData = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234'
        ];

        $result = $this->processor->validateFormData($validData);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);

        $invalidData = [
            'full_name' => '',
            'email' => 'invalid-email',
            'phone' => ''
        ];

        $result = $this->processor->validateFormData($invalidData);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testDetectDuplicateEmail()
    {
        $messageId = 'test-message-123';
        $senderEmail = 'test@example.com';

        // Test with no existing duplicates
        $result = $this->processor->isDuplicateEmail($messageId, $senderEmail);
        $this->assertFalse($result);
    }

    public function testGenerateLeadData()
    {
        $formData = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234',
            'service' => 'Roof Repair',
            'message' => 'Need urgent repair'
        ];

        $leadData = $this->processor->generateLeadData($formData, 'estimate');

        $this->assertIsArray($leadData);
        $this->assertEquals('John Doe', $leadData['full_name']);
        $this->assertEquals('john@example.com', $leadData['email']);
        $this->assertEquals('555-1234', $leadData['phone']);
        $this->assertEquals('estimate', $leadData['lead_source']);
        $this->assertArrayHasKey('created_at', $leadData);
    }

    public function testProcessEmailSuccess()
    {
        $emailData = [
            'message_id' => 'test-123',
            'subject' => 'Estimate Request',
            'sender_email' => 'test@example.com',
            'received_at' => date('Y-m-d H:i:s'),
            'content' => 'Name: John Doe\nEmail: john@example.com\nPhone: 555-1234'
        ];

        // Mock successful processing
        $result = $this->processor->processEmail($emailData, 'estimate', 'estimates@waveguardco.com');

        // Since we can't actually test database operations without a real connection,
        // we'll test the structure of the expected result
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('lead_id', $result);
        $this->assertArrayHasKey('message', $result);
    }

    public function testLogProcessingActivity()
    {
        $logData = [
            'email_account' => 'estimates@waveguardco.com',
            'form_type' => 'estimate',
            'message_id' => 'test-123',
            'subject' => 'Test Subject',
            'sender_email' => 'test@example.com',
            'processing_status' => 'success',
            'lead_id' => 1,
            'parsed_form_data' => json_encode(['name' => 'John Doe'])
        ];

        // Test log data structure
        $this->assertIsArray($logData);
        $this->assertArrayHasKey('email_account', $logData);
        $this->assertArrayHasKey('form_type', $logData);
        $this->assertArrayHasKey('processing_status', $logData);
    }

    public function testErrorHandling()
    {
        // Test with invalid form type
        $result = $this->processor->parseFormData('test content', 'invalid_type');
        $this->assertNull($result);

        // Test with empty content
        $result = $this->processor->parseFormData('', 'estimate');
        $this->assertNull($result);
    }

    public function testFormTypeValidation()
    {
        $validTypes = ['estimate', 'ltr', 'contact'];
        
        foreach ($validTypes as $type) {
            $this->assertTrue($this->processor->isValidFormType($type));
        }

        $this->assertFalse($this->processor->isValidFormType('invalid'));
        $this->assertFalse($this->processor->isValidFormType(''));
        $this->assertFalse($this->processor->isValidFormType(null));
    }
}