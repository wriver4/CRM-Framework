<?php

/**
 * Email Form Mapping Configuration
 * Handles form-specific field patterns and processing rules
 * Integrates with existing CRM framework patterns
 */

class EmailFormMapper extends Database
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get form-specific field mappings and processing rules
     */
    public function getFormMapping($formType)
    {
        switch ($formType) {
            case 'estimate':
                return $this->getEstimateFormMapping();
            case 'ltr':
                return $this->getLTRFormMapping();
            case 'contact':
                return $this->getContactFormMapping();
            default:
                return $this->getDefaultMapping();
        }
    }
    
    /**
     * ESTIMATE FORM MAPPING
     * For system quotes and installations
     */
    private function getEstimateFormMapping()
    {
        return [
            'form_type' => 'estimate',
            'lead_source' => 1,
            'contact_type' => 1, // Homeowner by default
            'services_interested_in' => 'full_system',
            'stage' => 'Lead',
            
            // Field mapping patterns for email parsing
            'field_patterns' => [
                // Basic contact info
                'name' => [
                    '/Name:\s*(.+)/i',
                    '/Full Name:\s*(.+)/i',
                    '/Contact Name:\s*(.+)/i',
                    '/Property Owner:\s*(.+)/i'
                ],
                'email' => [
                    '/Email:\s*([^\s]+@[^\s]+\.[^\s]+)/i',
                    '/E-mail:\s*([^\s]+@[^\s]+\.[^\s]+)/i',
                    '/Email Address:\s*([^\s]+@[^\s]+\.[^\s]+)/i'
                ],
                'phone' => [
                    '/Phone:\s*(.+)/i',
                    '/Cell Phone:\s*(.+)/i',
                    '/Mobile:\s*(.+)/i',
                    '/Phone Number:\s*(.+)/i'
                ],
                
                // Property information (estimate-specific)
                'property_address' => [
                    '/Property Address:\s*(.+)/i',
                    '/Address:\s*(.+)/i',
                    '/Location:\s*(.+)/i',
                    '/Property Location:\s*(.+)/i'
                ],
                'property_city' => [
                    '/City:\s*(.+)/i',
                    '/Property City:\s*(.+)/i'
                ],
                'property_state' => [
                    '/State:\s*(.+)/i',
                    '/Property State:\s*(.+)/i'
                ],
                'property_zip' => [
                    '/Zip:\s*(.+)/i',
                    '/ZIP Code:\s*(.+)/i',
                    '/Postal Code:\s*(.+)/i'
                ],
                
                // Property details
                'property_type' => [
                    '/Property Type:\s*(.+)/i',
                    '/Structure Type:\s*(.+)/i',
                    '/Building Type:\s*(.+)/i'
                ],
                'property_size' => [
                    '/Property Size:\s*(.+)/i',
                    '/Acreage:\s*(.+)/i',
                    '/Lot Size:\s*(.+)/i',
                    '/Square Footage:\s*(.+)/i'
                ],
                'structure_details' => [
                    '/Structure Details:\s*(.+)/i',
                    '/Building Details:\s*(.+)/i',
                    '/Home Description:\s*(.+)/i'
                ],
                
                // Fire protection specifics
                'current_protection' => [
                    '/Current Fire Protection:\s*(.+)/i',
                    '/Existing Systems:\s*(.+)/i',
                    '/Current Systems:\s*(.+)/i'
                ],
                'fire_risk_level' => [
                    '/Fire Risk:\s*(.+)/i',
                    '/Risk Level:\s*(.+)/i',
                    '/Fire Danger:\s*(.+)/i'
                ],
                'evacuation_zone' => [
                    '/Evacuation Zone:\s*(.+)/i',
                    '/Fire Zone:\s*(.+)/i'
                ],
                
                // Timeline and budget
                'timeline' => [
                    '/Timeline:\s*(.+)/i',
                    '/When Needed:\s*(.+)/i',
                    '/Installation Timeline:\s*(.+)/i',
                    '/Urgency:\s*(.+)/i'
                ],
                'budget_range' => [
                    '/Budget:\s*(.+)/i',
                    '/Budget Range:\s*(.+)/i',
                    '/Investment Level:\s*(.+)/i'
                ],
                
                // Additional info
                'special_requirements' => [
                    '/Special Requirements:\s*(.+)/i',
                    '/Additional Needs:\s*(.+)/i',
                    '/Special Considerations:\s*(.+)/i'
                ],
                'message' => [
                    '/Message:\s*(.+)/i',
                    '/Additional Information:\s*(.+)/i',
                    '/Comments:\s*(.+)/i',
                    '/Details:\s*(.+)/i'
                ]
            ],
            
            // Custom note generation
            'note_template' => "New estimate request received for {property_type} property.\n\n" .
                             "Property Details:\n" .
                             "- Address: {property_address}\n" .
                             "- Size: {property_size}\n" .
                             "- Current Protection: {current_protection}\n" .
                             "- Timeline: {timeline}\n" .
                             "- Budget Range: {budget_range}\n\n" .
                             "Additional Information: {message}"
        ];
    }
    
    /**
     * LTR (LONG TERM RETARDANT) FORM MAPPING
     * For fire retardant applications and AG-BEE drone services
     */
    private function getLTRFormMapping()
    {
        return [
            'form_type' => 'ltr',
            'lead_source' => 2,
            'contact_type' => 2, // Property Manager/Agricultural
            'services_interested_in' => 'fire_retardant',
            'stage' => 'Lead',
            
            'field_patterns' => [
                // Basic contact info
                'name' => [
                    '/Name:\s*(.+)/i',
                    '/Contact Name:\s*(.+)/i',
                    '/Farm Owner:\s*(.+)/i',
                    '/Ranch Manager:\s*(.+)/i'
                ],
                'email' => [
                    '/Email:\s*([^\s]+@[^\s]+\.[^\s]+)/i',
                    '/E-mail:\s*([^\s]+@[^\s]+\.[^\s]+)/i'
                ],
                'phone' => [
                    '/Phone:\s*(.+)/i',
                    '/Cell:\s*(.+)/i',
                    '/Mobile:\s*(.+)/i'
                ],
                'company' => [
                    '/Company:\s*(.+)/i',
                    '/Farm Name:\s*(.+)/i',
                    '/Ranch Name:\s*(.+)/i',
                    '/Business Name:\s*(.+)/i',
                    '/Operation Name:\s*(.+)/i'
                ],
                
                // Property/Land information
                'property_address' => [
                    '/Property Address:\s*(.+)/i',
                    '/Farm Address:\s*(.+)/i',
                    '/Ranch Location:\s*(.+)/i',
                    '/Land Address:\s*(.+)/i'
                ],
                'property_city' => ['/City:\s*(.+)/i'],
                'property_state' => ['/State:\s*(.+)/i'],
                'property_zip' => ['/Zip:\s*(.+)/i', '/ZIP:\s*(.+)/i'],
                
                // Agricultural/Land specifics
                'land_type' => [
                    '/Land Type:\s*(.+)/i',
                    '/Property Type:\s*(.+)/i',
                    '/Operation Type:\s*(.+)/i',
                    '/Agricultural Type:\s*(.+)/i'
                ],
                'total_acreage' => [
                    '/Total Acreage:\s*(.+)/i',
                    '/Property Size:\s*(.+)/i',
                    '/Land Size:\s*(.+)/i',
                    '/Total Acres:\s*(.+)/i'
                ],
                'treatment_acreage' => [
                    '/Treatment Area:\s*(.+)/i',
                    '/Acres to Treat:\s*(.+)/i',
                    '/Application Area:\s*(.+)/i',
                    '/Coverage Needed:\s*(.+)/i'
                ],
                
                // Application specifics
                'application_type' => [
                    '/Application Type:\s*(.+)/i',
                    '/Service Type:\s*(.+)/i',
                    '/Treatment Type:\s*(.+)/i'
                ],
                'application_method' => [
                    '/Application Method:\s*(.+)/i',
                    '/Preferred Method:\s*(.+)/i',
                    '/Delivery Method:\s*(.+)/i'
                ],
                'coverage_frequency' => [
                    '/Treatment Frequency:\s*(.+)/i',
                    '/Application Schedule:\s*(.+)/i',
                    '/Frequency:\s*(.+)/i'
                ],
                
                // Timing and scheduling
                'preferred_timing' => [
                    '/Preferred Timing:\s*(.+)/i',
                    '/Best Time:\s*(.+)/i',
                    '/Scheduling:\s*(.+)/i',
                    '/Timeline:\s*(.+)/i'
                ],
                'seasonal_considerations' => [
                    '/Seasonal Considerations:\s*(.+)/i',
                    '/Season:\s*(.+)/i',
                    '/Weather Considerations:\s*(.+)/i'
                ],
                
                // Access and logistics
                'property_access' => [
                    '/Property Access:\s*(.+)/i',
                    '/Access Notes:\s*(.+)/i',
                    '/Site Access:\s*(.+)/i'
                ],
                'landing_area' => [
                    '/Landing Area:\s*(.+)/i',
                    '/Drone Landing:\s*(.+)/i',
                    '/Equipment Access:\s*(.+)/i'
                ],
                
                // Environmental considerations
                'wildlife_concerns' => [
                    '/Wildlife:\s*(.+)/i',
                    '/Environmental Concerns:\s*(.+)/i',
                    '/Wildlife Considerations:\s*(.+)/i'
                ],
                'water_sources' => [
                    '/Water Sources:\s*(.+)/i',
                    '/Nearby Water:\s*(.+)/i',
                    '/Water Access:\s*(.+)/i'
                ],
                
                'message' => [
                    '/Message:\s*(.+)/i',
                    '/Additional Information:\s*(.+)/i',
                    '/Special Instructions:\s*(.+)/i',
                    '/Comments:\s*(.+)/i'
                ]
            ],
            
            'note_template' => "New LTR (Fire Retardant) application request.\n\n" .
                             "Property Information:\n" .
                             "- Type: {land_type}\n" .
                             "- Total Acreage: {total_acreage}\n" .
                             "- Treatment Area: {treatment_acreage}\n" .
                             "- Location: {property_address}\n\n" .
                             "Application Details:\n" .
                             "- Method: {application_method}\n" .
                             "- Frequency: {coverage_frequency}\n" .
                             "- Timing: {preferred_timing}\n\n" .
                             "Site Considerations:\n" .
                             "- Access: {property_access}\n" .
                             "- Wildlife: {wildlife_concerns}\n" .
                             "- Water Sources: {water_sources}\n\n" .
                             "Additional Notes: {message}"
        ];
    }
    
    /**
     * CONTACT FORM MAPPING
     * For general inquiries and information requests
     */
    private function getContactFormMapping()
    {
        return [
            'form_type' => 'contact',
            'lead_source' => 4, // Email
            'contact_type' => 1, // Homeowner
            'services_interested_in' => 'general_inquiry',
            'stage' => 'Lead',
            
            'field_patterns' => [
                // Basic contact info
                'name' => [
                    '/Name:\s*(.+)/i',
                    '/Full Name:\s*(.+)/i',
                    '/Contact Name:\s*(.+)/i'
                ],
                'email' => [
                    '/Email:\s*([^\s]+@[^\s]+\.[^\s]+)/i',
                    '/E-mail:\s*([^\s]+@[^\s]+\.[^\s]+)/i',
                    '/Email Address:\s*([^\s]+@[^\s]+\.[^\s]+)/i'
                ],
                'phone' => [
                    '/Phone:\s*(.+)/i',
                    '/Cell Phone:\s*(.+)/i',
                    '/Mobile:\s*(.+)/i',
                    '/Phone Number:\s*(.+)/i'
                ],
                'company' => [
                    '/Company:\s*(.+)/i',
                    '/Business Name:\s*(.+)/i',
                    '/Organization:\s*(.+)/i'
                ],
                
                // Address information
                'address' => [
                    '/Address:\s*(.+)/i',
                    '/Street Address:\s*(.+)/i',
                    '/Location:\s*(.+)/i'
                ],
                'city' => ['/City:\s*(.+)/i'],
                'state' => ['/State:\s*(.+)/i'],
                'zip' => ['/Zip:\s*(.+)/i', '/ZIP Code:\s*(.+)/i'],
                
                // Inquiry specifics
                'inquiry_type' => [
                    '/Inquiry Type:\s*(.+)/i',
                    '/Subject:\s*(.+)/i',
                    '/Regarding:\s*(.+)/i'
                ],
                'service_interest' => [
                    '/Service Interest:\s*(.+)/i',
                    '/Services Needed:\s*(.+)/i',
                    '/Interested In:\s*(.+)/i'
                ],
                'preferred_contact' => [
                    '/Preferred Contact:\s*(.+)/i',
                    '/Best Way to Contact:\s*(.+)/i',
                    '/Contact Preference:\s*(.+)/i'
                ],
                'best_time' => [
                    '/Best Time:\s*(.+)/i',
                    '/Preferred Time:\s*(.+)/i',
                    '/Available:\s*(.+)/i'
                ],
                
                'message' => [
                    '/Message:\s*(.+)/i',
                    '/Comments:\s*(.+)/i',
                    '/Additional Information:\s*(.+)/i',
                    '/Details:\s*(.+)/i',
                    '/Question:\s*(.+)/i'
                ]
            ],
            
            'note_template' => "New general contact inquiry received.\n\n" .
                             "Contact Information:\n" .
                             "- Name: {name}\n" .
                             "- Email: {email}\n" .
                             "- Phone: {phone}\n" .
                             "- Company: {company}\n\n" .
                             "Inquiry Details:\n" .
                             "- Type: {inquiry_type}\n" .
                             "- Service Interest: {service_interest}\n" .
                             "- Preferred Contact: {preferred_contact}\n" .
                             "- Best Time: {best_time}\n\n" .
                             "Message: {message}"
        ];
    }
    
    /**
     * Default mapping for unknown form types
     */
    private function getDefaultMapping()
    {
        return [
            'form_type' => 'contact',
            'lead_source' => 4, // Email
            'contact_type' => 1, // Homeowner
            'services_interested_in' => 'general_inquiry',
            'stage' => 'Lead',
            
            'field_patterns' => [
                'name' => ['/Name:\s*(.+)/i', '/Full Name:\s*(.+)/i'],
                'email' => ['/Email:\s*([^\s]+@[^\s]+\.[^\s]+)/i'],
                'phone' => ['/Phone:\s*(.+)/i', '/Cell:\s*(.+)/i'],
                'message' => ['/Message:\s*(.+)/i', '/Comments:\s*(.+)/i']
            ],
            
            'note_template' => "New email form submission received.\n\nMessage: {message}"
        ];
    }
}