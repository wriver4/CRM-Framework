<?php
require_once '../../classes/Leads.php';

if (isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');
    $leads = new Leads();

    // Skip the header row
    fgetcsv($handle);

    while (($data = fgetcsv($handle)) !== false) {
        $leadData = [
            'name' => $data[0],
            'phone' => $data[1],
            'email' => $data[2],
            'address' => $data[3],
            'estimate_number' => $data[4],
            'stage' => $data[5],
            'proposal_sent_date' => $data[6],
            'scheduled_date' => $data[7],
            'structure_type' => $data[8],
            'lead_source' => $data[9],
            'lead_lost_notes' => $data[10],
            'plans_submitted' => $data[11],
            'structure_description' => $data[12],
            'structure_other' => $data[13],
            'site_visit_by' => $data[14],
            'picture_submitted' => $data[15],
            'referred_to' => $data[16],
            'picture_upload_link' => $data[17],
            'plans_upload_link' => $data[18],
            'existing_client' => $data[19],
            'get_updates' => $data[20],
            'hear_about' => $data[21],
            'hear_about_other' => $data[22],
            'structure_additional' => $data[23],
            'lead_notes' => $data[24],
            'prospect_notes' => $data[25],
            'lead_lost' => $data[26],
            'site_visit_completed' => $data[27],
            'closer' => $data[28],
            'referred_services' => $data[29],
            'assigned_to' => $data[30],
            'referred' => $data[31],
            'site_visit_date' => $data[32],
            'date_qualified' => $data[33],
            'contacted_date' => $data[34],
            'referral_done' => $data[35],
            'jd_referral_notes' => $data[36],
            'closing_notes' => $data[37],
            'prospect_lost' => $data[38],
            'to_contracting' => $data[39],
            'plans_and_pics' => $data[40]
        ];

        $leads->createLead($leadData);
    }

    fclose($handle);
    echo "CSV data imported successfully.";
} else {
    echo "No file uploaded.";
}
?>
