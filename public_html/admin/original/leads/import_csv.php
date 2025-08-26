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
            'full_address' => $data[3],
            'estimate_number' => $data[4],
            'stage' => $data[5],
            'structure_type' => $data[8],
            'lead_source' => $data[9],
            'plans_submitted' => $data[11],
            'structure_description' => $data[12],
            'structure_other' => $data[13],
            'picture_submitted' => $data[15],
            'picture_upload_link' => $data[17],
            'plans_upload_link' => $data[18],
            'get_updates' => $data[20],
            'hear_about' => $data[21],
            'hear_about_other' => $data[22],
            'structure_additional' => $data[23],
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
