<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class LeadsListTable extends ActionTable
{
    public function __construct($results, $lang)
    {
        $this->column_names = [
            'action' => $lang['action'] ?? 'Action',
            'estimate_number' => $lang['lead_number'] ?? 'Lead #',
            'stage' => $lang['lead_stage'] ?? 'Stage',
            'full_name' => $lang['full_name'] ?? 'Full Name',
            'cell_phone' => $lang['lead_cell_phone'] ?? 'Phone',
            'email' => $lang['lead_email'] ?? 'Email',
            'full_address' => $lang['full_address'] ?? 'Address',
            'updated_at' => $lang['updates'] ?? 'Updates'
        ];
        parent::__construct($results, $this->column_names, "leads-list");
        $this->lang = $lang;
        $this->leads = new Leads();
        $this->users = new Users();
    }

    public function table_row_columns($results)
    {
        foreach ($this->column_names as $column_key => $column_title) {
            switch ($column_key) {
                case 'action':
                    echo '<td>';
                    $this->row_nav($results['id'] ?? null, $this->row_id = null);
                    echo '</td>';
                    break;
                
                case 'estimate_number':
                    echo '<td>';
                    $value = $results['estimate_number'] ?? '';
                    echo htmlspecialchars($value ?: '-');
                    echo '</td>';
                    break;
                
                case 'stage':
                    echo '<td>';
                    $value = $results['stage'] ?? '';
                    $leads = new Leads();
                
                    if (is_numeric($value)) {
                        // New numbered system
                        $stage_number = (int)$value;
                        $badge_class = $leads->get_stage_badge_class($stage_number);
                        $stage_text = $leads->get_stage_display_name($stage_number, $this->lang);
                    } else {
                        $badge_class = 'badge bg-secondary';
                        $stage_text = '-';
                    }
                    
                    echo '<span class="' . $badge_class . '">' . htmlspecialchars($stage_text ?: '-') . '</span>';
                    echo '</td>';
                    break;
                
                case 'full_name':
                    echo '<td>';
                    $first_name = $results['first_name'] ?? '';
                    $family_name = $results['family_name'] ?? '';
                    $full_name = trim($first_name . ' ' . $family_name);
                    echo htmlspecialchars($full_name ?: '-');
                    echo '</td>';
                    break;
                
                case 'cell_phone':
                    echo '<td>';
                    $value = $results['cell_phone'] ?? '';
                    echo htmlspecialchars($value ?: '-');
                    echo '</td>';
                    break;
                
                case 'email':
                    echo '<td>';
                    $value = $results['email'] ?? '';
                    echo htmlspecialchars($value ?: '-');
                    echo '</td>';
                    break;
                
                case 'full_address':
                    echo '<td>';
                    $full_address = $results['full_address'] ?? '';
                    if ($full_address) {
                        // Display full address, splitting on common separators to show in 2 lines if needed
                        $address_parts = preg_split('/[,\n\r]+/', trim($full_address), 2);
                        echo htmlspecialchars($address_parts[0]);
                        if (isset($address_parts[1]) && trim($address_parts[1])) {
                            echo '<br>' . htmlspecialchars(trim($address_parts[1]));
                        }
                    } else {
                        echo '-';
                    }
                    echo '</td>';
                    break;
                
                case 'updated_at':
                    echo '<td>';
                    $value = $results['updated_at'] ?? '';
                    if ($value && $value != '0000-00-00 00:00:00') {
                        echo date('M j, Y g:i A', strtotime($value));
                    } else {
                        echo '-';
                    }
                    echo '</td>';
                    break;
                
                default:
                    echo '<td>-</td>';
                    break;
            }
        }
    }
}