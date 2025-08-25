<?php
// Example: How other forms can integrate with the notes system

// 1. From a contact form submission
function add_contact_note_to_lead($lead_id, $contact_info) {
    $notes = new Notes();
    $note_text = "New contact added: " . $contact_info['full_name'] . " (" . $contact_info['email'] . ")";
    return $notes->create_system_note($lead_id, $note_text, 'contacts');
}

// 2. From a system status change
function add_system_status_note($lead_id, $old_status, $new_status) {
    $notes = new Notes();
    $note_text = "System status changed from '{$old_status}' to '{$new_status}'";
    return $notes->create_system_note($lead_id, $note_text, 'systems');
}

// 3. From an email interaction
function add_email_note($lead_id, $email_subject, $direction = 'sent') {
    $notes = new Notes();
    $note_text = "Email {$direction}: {$email_subject}";
    $note_data = [
        'source' => 3, // Email
        'note_text' => $note_text,
        'user_id' => $_SESSION['user_id'] ?? 1,
        'form_source' => 'email_system'
    ];
    return $notes->create_note_for_lead($lead_id, $note_data);
}

// 4. From a phone call log
function add_phone_call_note($lead_id, $duration, $outcome) {
    $notes = new Notes();
    $note_text = "Phone call completed ({$duration} min) - {$outcome}";
    $note_data = [
        'source' => 2, // Phone Call
        'note_text' => $note_text,
        'user_id' => $_SESSION['user_id'] ?? 1,
        'form_source' => 'call_log'
    ];
    return $notes->create_note_for_lead($lead_id, $note_data);
}

// 5. From a meeting scheduler
function add_meeting_note($lead_id, $meeting_type, $date_time) {
    $notes = new Notes();
    $note_text = "{$meeting_type} scheduled for " . date('M d, Y g:i A', strtotime($date_time));
    $note_data = [
        'source' => 4, // Meeting
        'note_text' => $note_text,
        'user_id' => $_SESSION['user_id'] ?? 1,
        'form_source' => 'scheduler'
    ];
    return $notes->create_note_for_lead($lead_id, $note_data);
}

// 6. From a site visit form
function add_site_visit_note($lead_id, $visit_details) {
    $notes = new Notes();
    $note_text = "Site visit completed: {$visit_details}";
    $note_data = [
        'source' => 5, // Site Visit
        'note_text' => $note_text,
        'user_id' => $_SESSION['user_id'] ?? 1,
        'form_source' => 'site_visits'
    ];
    return $notes->create_note_for_lead($lead_id, $note_data);
}

// Example usage in other forms:
/*
// In contacts/post.php after creating a contact
if ($contact_created && !empty($lead_id)) {
    add_contact_note_to_lead($lead_id, $contact_data);
}

// In systems/post.php after status change  
if ($status_updated && !empty($lead_id)) {
    add_system_status_note($lead_id, $old_status, $new_status);
}

// In email handler after sending email
if ($email_sent && !empty($lead_id)) {
    add_email_note($lead_id, $email_subject, 'sent');
}
*/
?>