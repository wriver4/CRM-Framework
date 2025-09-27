<?php

namespace Tests\Fixtures;

/**
 * Test data fixtures for Leads module testing
 * 
 * Provides sample data for testing various scenarios:
 * - Valid lead data
 * - Edge cases
 * - International data
 * - Invalid data for error testing
 */
class LeadsTestData
{
    /**
     * Get basic valid lead data
     */
    public static function getValidLeadData(): array
    {
        return [
            'lead_source' => 1,
            'first_name' => 'John',
            'family_name' => 'Doe',
            'cell_phone' => '555-123-4567',
            'email' => 'john.doe@example.com',
            'contact_type' => 1,
            'form_street_1' => '123 Main Street',
            'form_street_2' => '',
            'form_city' => 'Anytown',
            'form_state' => 'CA',
            'form_postcode' => '90210',
            'form_country' => 'US',
            'timezone' => 'America/Los_Angeles',
            'services_interested_in' => 'Residential Security',
            'get_updates' => 1,
            'stage' => 10,
            'last_edited_by' => 1
        ];
    }

    /**
     * Get multiple valid leads for bulk testing
     */
    public static function getMultipleValidLeads(): array
    {
        return [
            [
                'lead_source' => 1,
                'first_name' => 'Alice',
                'family_name' => 'Johnson',
                'cell_phone' => '555-111-2222',
                'email' => 'alice.johnson@example.com',
                'contact_type' => 1,
                'form_street_1' => '456 Oak Avenue',
                'form_city' => 'Springfield',
                'form_state' => 'IL',
                'form_postcode' => '62701',
                'form_country' => 'US',
                'stage' => 10,
                'last_edited_by' => 1
            ],
            [
                'lead_source' => 2,
                'first_name' => 'Bob',
                'family_name' => 'Smith',
                'cell_phone' => '555-333-4444',
                'email' => 'bob.smith@example.com',
                'contact_type' => 2,
                'form_street_1' => '789 Pine Street',
                'form_city' => 'Portland',
                'form_state' => 'OR',
                'form_postcode' => '97201',
                'form_country' => 'US',
                'stage' => 20,
                'last_edited_by' => 1
            ],
            [
                'lead_source' => 3,
                'first_name' => 'Carol',
                'family_name' => 'Williams',
                'cell_phone' => '555-555-6666',
                'email' => 'carol.williams@example.com',
                'contact_type' => 1,
                'form_street_1' => '321 Elm Drive',
                'form_city' => 'Austin',
                'form_state' => 'TX',
                'form_postcode' => '73301',
                'form_country' => 'US',
                'stage' => 40,
                'last_edited_by' => 1
            ]
        ];
    }

    /**
     * Get international lead data for testing phone formatting
     */
    public static function getInternationalLeads(): array
    {
        return [
            // UK Lead
            [
                'lead_source' => 1,
                'first_name' => 'James',
                'family_name' => 'Brown',
                'cell_phone' => '2012345678',
                'email' => 'james.brown@example.co.uk',
                'contact_type' => 1,
                'form_street_1' => '10 Downing Street',
                'form_city' => 'London',
                'form_state' => 'England',
                'form_postcode' => 'SW1A 2AA',
                'form_country' => 'GB',
                'stage' => 10,
                'last_edited_by' => 1
            ],
            // Canadian Lead
            [
                'lead_source' => 2,
                'first_name' => 'Sarah',
                'family_name' => 'Miller',
                'cell_phone' => '4165551234',
                'email' => 'sarah.miller@example.ca',
                'contact_type' => 1,
                'form_street_1' => '100 Queen Street',
                'form_city' => 'Toronto',
                'form_state' => 'ON',
                'form_postcode' => 'M5H 2N2',
                'form_country' => 'CA',
                'stage' => 20,
                'last_edited_by' => 1
            ],
            // German Lead
            [
                'lead_source' => 1,
                'first_name' => 'Hans',
                'family_name' => 'Mueller',
                'cell_phone' => '301234567',
                'email' => 'hans.mueller@example.de',
                'contact_type' => 3,
                'form_street_1' => 'Unter den Linden 1',
                'form_city' => 'Berlin',
                'form_state' => 'Berlin',
                'form_postcode' => '10117',
                'form_country' => 'DE',
                'stage' => 30,
                'last_edited_by' => 1
            ]
        ];
    }

    /**
     * Get leads with different stages for stage testing
     */
    public static function getLeadsWithDifferentStages(): array
    {
        return [
            [
                'first_name' => 'Stage10',
                'family_name' => 'Lead',
                'email' => 'stage10@example.com',
                'stage' => 10,
                'cell_phone' => '555-010-0000',
                'contact_type' => 1,
                'lead_source' => 1,
                'last_edited_by' => 1
            ],
            [
                'first_name' => 'Stage20',
                'family_name' => 'PreQual',
                'email' => 'stage20@example.com',
                'stage' => 20,
                'cell_phone' => '555-020-0000',
                'contact_type' => 1,
                'lead_source' => 1,
                'last_edited_by' => 1
            ],
            [
                'first_name' => 'Stage40',
                'family_name' => 'Referral',
                'email' => 'stage40@example.com',
                'stage' => 40,
                'cell_phone' => '555-040-0000',
                'contact_type' => 1,
                'lead_source' => 2,
                'last_edited_by' => 1
            ],
            [
                'first_name' => 'Stage50',
                'family_name' => 'Prospect',
                'email' => 'stage50@example.com',
                'stage' => 50,
                'cell_phone' => '555-050-0000',
                'contact_type' => 1,
                'lead_source' => 1,
                'last_edited_by' => 1
            ],
            [
                'first_name' => 'Stage140',
                'family_name' => 'ClosedLost',
                'email' => 'stage140@example.com',
                'stage' => 140,
                'cell_phone' => '555-140-0000',
                'contact_type' => 1,
                'lead_source' => 1,
                'last_edited_by' => 1
            ]
        ];
    }

    /**
     * Get leads with various phone number formats for testing
     */
    public static function getLeadsWithVariousPhoneFormats(): array
    {
        return [
            [
                'first_name' => 'Phone1',
                'family_name' => 'Test',
                'email' => 'phone1@example.com',
                'cell_phone' => '5551234567',
                'form_country' => 'US',
                'expected_format' => '555-123-4567',
                'contact_type' => 1,
                'lead_source' => 1,
                'stage' => 10,
                'last_edited_by' => 1
            ],
            [
                'first_name' => 'Phone2',
                'family_name' => 'Test',
                'email' => 'phone2@example.com',
                'cell_phone' => '555-123-4567',
                'form_country' => 'US',
                'expected_format' => '555-123-4567',
                'contact_type' => 1,
                'lead_source' => 1,
                'stage' => 10,
                'last_edited_by' => 1
            ],
            [
                'first_name' => 'Phone3',
                'family_name' => 'Test',
                'email' => 'phone3@example.com',
                'cell_phone' => '(555) 123-4567',
                'form_country' => 'US',
                'expected_format' => '555-123-4567',
                'contact_type' => 1,
                'lead_source' => 1,
                'stage' => 10,
                'last_edited_by' => 1
            ],
            [
                'first_name' => 'Phone4',
                'family_name' => 'Test',
                'email' => 'phone4@example.com',
                'cell_phone' => '+15551234567',
                'form_country' => 'US',
                'expected_format' => '555-123-4567',
                'contact_type' => 1,
                'lead_source' => 1,
                'stage' => 10,
                'last_edited_by' => 1
            ],
            [
                'first_name' => 'Phone5',
                'family_name' => 'Test',
                'email' => 'phone5@example.com',
                'cell_phone' => '4165551234',
                'form_country' => 'CA',
                'expected_format' => '+1 416-555-1234',
                'contact_type' => 1,
                'lead_source' => 1,
                'stage' => 10,
                'last_edited_by' => 1
            ]
        ];
    }

    /**
     * Get invalid lead data for error testing
     */
    public static function getInvalidLeadData(): array
    {
        return [
            // Missing required fields
            [
                'description' => 'Missing first name',
                'data' => [
                    'family_name' => 'Test',
                    'email' => 'test@example.com',
                    'contact_type' => 1,
                    'lead_source' => 1,
                    'stage' => 10
                ]
            ],
            // Invalid email format
            [
                'description' => 'Invalid email format',
                'data' => [
                    'first_name' => 'Test',
                    'family_name' => 'User',
                    'email' => 'invalid-email',
                    'contact_type' => 1,
                    'lead_source' => 1,
                    'stage' => 10
                ]
            ],
            // Invalid stage number
            [
                'description' => 'Invalid stage number',
                'data' => [
                    'first_name' => 'Test',
                    'family_name' => 'User',
                    'email' => 'test@example.com',
                    'contact_type' => 1,
                    'lead_source' => 1,
                    'stage' => 999
                ]
            ],
            // Invalid contact type
            [
                'description' => 'Invalid contact type',
                'data' => [
                    'first_name' => 'Test',
                    'family_name' => 'User',
                    'email' => 'test@example.com',
                    'contact_type' => 99,
                    'lead_source' => 1,
                    'stage' => 10
                ]
            ]
        ];
    }

    /**
     * Get edge case data for boundary testing
     */
    public static function getEdgeCaseData(): array
    {
        return [
            // Very long names
            [
                'description' => 'Very long names',
                'data' => [
                    'first_name' => str_repeat('A', 100),
                    'family_name' => str_repeat('B', 100),
                    'email' => 'longname@example.com',
                    'contact_type' => 1,
                    'lead_source' => 1,
                    'stage' => 10,
                    'last_edited_by' => 1
                ]
            ],
            // Special characters in names
            [
                'description' => 'Special characters in names',
                'data' => [
                    'first_name' => "José María",
                    'family_name' => "O'Connor-Smith",
                    'email' => 'jose.maria@example.com',
                    'contact_type' => 1,
                    'lead_source' => 1,
                    'stage' => 10,
                    'last_edited_by' => 1
                ]
            ],
            // Empty optional fields
            [
                'description' => 'Empty optional fields',
                'data' => [
                    'first_name' => 'Minimal',
                    'family_name' => 'Data',
                    'email' => 'minimal@example.com',
                    'cell_phone' => '',
                    'form_street_1' => '',
                    'form_city' => '',
                    'form_state' => '',
                    'form_postcode' => '',
                    'contact_type' => 1,
                    'lead_source' => 1,
                    'stage' => 10,
                    'last_edited_by' => 1
                ]
            ]
        ];
    }

    /**
     * Get update test data for testing lead modifications
     */
    public static function getUpdateTestData(): array
    {
        return [
            // Stage change updates
            [
                'description' => 'Stage change to Referral',
                'updates' => [
                    'stage' => 40,
                    'last_edited_by' => 1
                ],
                'should_trigger_notification' => true
            ],
            [
                'description' => 'Stage change to Prospect',
                'updates' => [
                    'stage' => 50,
                    'last_edited_by' => 1
                ],
                'should_trigger_notification' => true
            ],
            [
                'description' => 'Stage change to Closed Lost',
                'updates' => [
                    'stage' => 140,
                    'last_edited_by' => 1
                ],
                'should_trigger_notification' => true
            ],
            [
                'description' => 'Non-trigger stage change',
                'updates' => [
                    'stage' => 20,
                    'last_edited_by' => 1
                ],
                'should_trigger_notification' => false
            ],
            // Contact information updates
            [
                'description' => 'Phone number update',
                'updates' => [
                    'cell_phone' => '555-999-8888',
                    'last_edited_by' => 1
                ],
                'should_trigger_notification' => false
            ],
            [
                'description' => 'Email update',
                'updates' => [
                    'email' => 'updated@example.com',
                    'last_edited_by' => 1
                ],
                'should_trigger_notification' => false
            ],
            // Address updates
            [
                'description' => 'Address update',
                'updates' => [
                    'form_street_1' => '999 Updated Street',
                    'form_city' => 'New City',
                    'form_state' => 'NY',
                    'last_edited_by' => 1
                ],
                'should_trigger_notification' => false
            ]
        ];
    }

    /**
     * Get performance test data for load testing
     */
    public static function getPerformanceTestData(int $count = 100): array
    {
        $data = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $data[] = [
                'lead_source' => ($i % 6) + 1,
                'first_name' => "Performance{$i}",
                'family_name' => "Test{$i}",
                'cell_phone' => sprintf('555-%03d-%04d', $i % 1000, $i),
                'email' => "performance{$i}@example.com",
                'contact_type' => ($i % 5) + 1,
                'form_street_1' => "{$i} Performance Street",
                'form_city' => 'Test City',
                'form_state' => 'CA',
                'form_postcode' => sprintf('%05d', $i),
                'form_country' => 'US',
                'stage' => [10, 20, 30, 40, 50][$i % 5],
                'last_edited_by' => 1
            ];
        }
        
        return $data;
    }

    /**
     * Get language test data for internationalization testing
     */
    public static function getLanguageTestData(): array
    {
        return [
            'en' => [
                'action' => 'Action',
                'lead_id' => 'Lead #',
                'lead_stage' => 'Stage',
                'full_name' => 'Full Name',
                'lead_cell_phone' => 'Phone',
                'lead_email' => 'Email',
                'full_address' => 'Address',
                'stage_10' => 'Lead',
                'stage_20' => 'Pre-Qualification',
                'stage_40' => 'Referral',
                'stage_50' => 'Prospect',
                'stage_140' => 'Closed Lost'
            ],
            'es' => [
                'action' => 'Acción',
                'lead_id' => 'Cliente #',
                'lead_stage' => 'Etapa',
                'full_name' => 'Nombre Completo',
                'lead_cell_phone' => 'Teléfono',
                'lead_email' => 'Email',
                'full_address' => 'Dirección',
                'stage_10' => 'Cliente Potencial',
                'stage_20' => 'Pre-Calificación',
                'stage_40' => 'Referencia',
                'stage_50' => 'Prospecto',
                'stage_140' => 'Perdido'
            ],
            'fr' => [
                'action' => 'Action',
                'lead_id' => 'Piste #',
                'lead_stage' => 'Étape',
                'full_name' => 'Nom Complet',
                'lead_cell_phone' => 'Téléphone',
                'lead_email' => 'Email',
                'full_address' => 'Adresse',
                'stage_10' => 'Piste',
                'stage_20' => 'Pré-qualification',
                'stage_40' => 'Référence',
                'stage_50' => 'Prospect',
                'stage_140' => 'Perdu'
            ]
        ];
    }
}