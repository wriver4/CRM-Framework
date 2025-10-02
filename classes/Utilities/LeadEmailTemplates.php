<?php

/**
 * LeadEmailTemplates Class
 * 
 * Generates email templates for lead thank you emails
 * Supports multiple lead sources with customized content
 */

class LeadEmailTemplates
{
    private $lang;
    
    /**
     * Constructor
     * 
     * @param array $lang Language array
     */
    public function __construct($lang)
    {
        $this->lang = $lang;
    }
    
    /**
     * Generate thank you email for a lead based on source
     * 
     * @param array $lead_data Lead data array
     * @param int $lead_source_id Lead source ID (1-6)
     * @return array Array with 'subject', 'html', and 'text' keys
     */
    public function generate_thank_you_email($lead_data, $lead_source_id)
    {
        $full_name = $lead_data['full_name'] ?? $this->lang['email_dear_customer'];
        $first_name = $this->get_first_name($full_name);
        
        switch ($lead_source_id) {
            case 1: // Web Estimate
                return $this->web_estimate_template($first_name, $lead_data);
            case 2: // LTR Form
                return $this->ltr_form_template($first_name, $lead_data);
            case 3: // Contact Form
                return $this->contact_form_template($first_name, $lead_data);
            case 4: // Phone Inquiry
                return $this->phone_inquiry_template($first_name, $lead_data);
            case 5: // Cold Call
                return $this->cold_call_template($first_name, $lead_data);
            case 6: // In Person
                return $this->in_person_template($first_name, $lead_data);
            default:
                return $this->generic_template($first_name, $lead_data);
        }
    }
    
    /**
     * Extract first name from full name
     * 
     * @param string $full_name Full name
     * @return string First name
     */
    private function get_first_name($full_name)
    {
        $parts = explode(' ', trim($full_name));
        return $parts[0] ?? $this->lang['email_dear_customer'];
    }
    
    /**
     * Web Estimate template
     */
    private function web_estimate_template($first_name, $lead_data)
    {
        $subject = $this->lang['email_subject_web_estimate'];
        
        $html = $this->get_email_header();
        $html .= "<h2>{$this->lang['email_greeting']} {$first_name},</h2>";
        $html .= "<p>{$this->lang['email_web_estimate_intro']}</p>";
        $html .= "<p>{$this->lang['email_web_estimate_body']}</p>";
        $html .= $this->get_download_section();
        $html .= "<p>{$this->lang['email_next_steps']}</p>";
        $html .= $this->get_contact_info();
        $html .= $this->get_email_footer();
        
        $text = $this->html_to_text($html);
        
        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text
        ];
    }
    
    /**
     * LTR Form template
     */
    private function ltr_form_template($first_name, $lead_data)
    {
        $subject = $this->lang['email_subject_ltr_form'];
        
        $html = $this->get_email_header();
        $html .= "<h2>{$this->lang['email_greeting']} {$first_name},</h2>";
        $html .= "<p>{$this->lang['email_ltr_form_intro']}</p>";
        $html .= "<p>{$this->lang['email_ltr_form_body']}</p>";
        $html .= $this->get_download_section();
        $html .= "<p>{$this->lang['email_next_steps']}</p>";
        $html .= $this->get_contact_info();
        $html .= $this->get_email_footer();
        
        $text = $this->html_to_text($html);
        
        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text
        ];
    }
    
    /**
     * Contact Form template
     */
    private function contact_form_template($first_name, $lead_data)
    {
        $subject = $this->lang['email_subject_contact_form'];
        
        $html = $this->get_email_header();
        $html .= "<h2>{$this->lang['email_greeting']} {$first_name},</h2>";
        $html .= "<p>{$this->lang['email_contact_form_intro']}</p>";
        $html .= "<p>{$this->lang['email_contact_form_body']}</p>";
        $html .= $this->get_download_section();
        $html .= "<p>{$this->lang['email_next_steps']}</p>";
        $html .= $this->get_contact_info();
        $html .= $this->get_email_footer();
        
        $text = $this->html_to_text($html);
        
        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text
        ];
    }
    
    /**
     * Phone Inquiry template
     */
    private function phone_inquiry_template($first_name, $lead_data)
    {
        $subject = $this->lang['email_subject_phone_inquiry'];
        
        $html = $this->get_email_header();
        $html .= "<h2>{$this->lang['email_greeting']} {$first_name},</h2>";
        $html .= "<p>{$this->lang['email_phone_inquiry_intro']}</p>";
        $html .= "<p>{$this->lang['email_phone_inquiry_body']}</p>";
        $html .= $this->get_download_section();
        $html .= "<p>{$this->lang['email_next_steps']}</p>";
        $html .= $this->get_contact_info();
        $html .= $this->get_email_footer();
        
        $text = $this->html_to_text($html);
        
        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text
        ];
    }
    
    /**
     * Cold Call template
     */
    private function cold_call_template($first_name, $lead_data)
    {
        $subject = $this->lang['email_subject_cold_call'];
        
        $html = $this->get_email_header();
        $html .= "<h2>{$this->lang['email_greeting']} {$first_name},</h2>";
        $html .= "<p>{$this->lang['email_cold_call_intro']}</p>";
        $html .= "<p>{$this->lang['email_cold_call_body']}</p>";
        $html .= $this->get_download_section();
        $html .= "<p>{$this->lang['email_next_steps']}</p>";
        $html .= $this->get_contact_info();
        $html .= $this->get_email_footer();
        
        $text = $this->html_to_text($html);
        
        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text
        ];
    }
    
    /**
     * In Person template
     */
    private function in_person_template($first_name, $lead_data)
    {
        $subject = $this->lang['email_subject_in_person'];
        
        $html = $this->get_email_header();
        $html .= "<h2>{$this->lang['email_greeting']} {$first_name},</h2>";
        $html .= "<p>{$this->lang['email_in_person_intro']}</p>";
        $html .= "<p>{$this->lang['email_in_person_body']}</p>";
        $html .= $this->get_download_section();
        $html .= "<p>{$this->lang['email_next_steps']}</p>";
        $html .= $this->get_contact_info();
        $html .= $this->get_email_footer();
        
        $text = $this->html_to_text($html);
        
        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text
        ];
    }
    
    /**
     * Generic template (fallback)
     */
    private function generic_template($first_name, $lead_data)
    {
        $subject = $this->lang['email_subject_generic'];
        
        $html = $this->get_email_header();
        $html .= "<h2>{$this->lang['email_greeting']} {$first_name},</h2>";
        $html .= "<p>{$this->lang['email_generic_intro']}</p>";
        $html .= "<p>{$this->lang['email_generic_body']}</p>";
        $html .= $this->get_download_section();
        $html .= "<p>{$this->lang['email_next_steps']}</p>";
        $html .= $this->get_contact_info();
        $html .= $this->get_email_footer();
        
        $text = $this->html_to_text($html);
        
        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text
        ];
    }
    
    /**
     * Get email header HTML
     */
    private function get_email_header()
    {
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #0066cc;
            margin-bottom: 20px;
        }
        .download-section {
            background-color: #f5f5f5;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 20px 0;
        }
        .download-link {
            display: inline-block;
            background-color: #0066cc;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .contact-info {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
';
    }
    
    /**
     * Get download section HTML
     */
    private function get_download_section()
    {
        $html = '<div class="download-section">';
        $html .= "<h3>{$this->lang['email_download_title']}</h3>";
        $html .= "<p>{$this->lang['email_download_description']}</p>";
        
        // Add NextCloud download links (these should be configured in settings)
        if (!empty($this->lang['email_nextcloud_link'])) {
            $html .= '<a href="' . htmlspecialchars($this->lang['email_nextcloud_link']) . '" class="download-link">';
            $html .= $this->lang['email_download_button'];
            $html .= '</a>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Get contact info section HTML
     */
    private function get_contact_info()
    {
        $html = '<div class="contact-info">';
        $html .= "<h3>{$this->lang['email_contact_title']}</h3>";
        $html .= "<p>{$this->lang['email_contact_description']}</p>";
        
        if (!empty($this->lang['email_contact_phone'])) {
            $html .= "<p><strong>{$this->lang['email_phone_label']}:</strong> {$this->lang['email_contact_phone']}</p>";
        }
        
        if (!empty($this->lang['email_contact_email'])) {
            $html .= "<p><strong>{$this->lang['email_email_label']}:</strong> {$this->lang['email_contact_email']}</p>";
        }
        
        if (!empty($this->lang['email_contact_website'])) {
            $html .= "<p><strong>{$this->lang['email_website_label']}:</strong> <a href=\"{$this->lang['email_contact_website']}\">{$this->lang['email_contact_website']}</a></p>";
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Get email footer HTML
     */
    private function get_email_footer()
    {
        $html = '<div class="footer">';
        $html .= "<p>{$this->lang['email_footer_text']}</p>";
        $html .= "<p>{$this->lang['email_footer_copyright']}</p>";
        $html .= '</div>';
        $html .= '</body></html>';
        return $html;
    }
    
    /**
     * Convert HTML to plain text
     * 
     * @param string $html HTML content
     * @return string Plain text content
     */
    private function html_to_text($html)
    {
        // Remove HTML tags
        $text = strip_tags($html);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Add line breaks for readability
        $text = str_replace('</p>', "\n\n", $html);
        $text = str_replace('<br>', "\n", $text);
        $text = str_replace('<br/>', "\n", $text);
        $text = str_replace('<br />', "\n", $text);
        $text = strip_tags($text);
        
        // Trim and return
        return trim($text);
    }
}