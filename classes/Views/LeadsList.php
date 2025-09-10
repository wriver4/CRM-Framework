<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class LeadsList extends EditDeleteTable
{
    public function __construct($results, $lang)
    {
        // Reorder columns: move project_name to the end for more space
        $this->column_names = [
            'action' => $lang['action'] ?? 'Action',
            'lead_id' => $lang['lead_id'] ?? 'Lead #',
            'stage' => $lang['lead_stage'] ?? 'Stage',
            'full_name' => $lang['full_name'] ?? 'Full Name',
            'cell_phone' => $lang['lead_cell_phone'] ?? 'Phone',
            'email' => $lang['lead_email'] ?? 'Email',
            'full_address' => $lang['full_address'] ?? 'Address',
            'project_name' => $lang['project_name'] ?? 'Project Name'
        ];
        parent::__construct($results, $this->column_names, "leads-list");
        $this->lang = $lang;
        $this->leads = new Leads();
        $this->users = new Users();
        
        // Add view button properties that are missing from parent class
        $this->row_nav_button_view_class_enabled = 'class="btn nav-link btn-info link-light" ';
        $this->row_nav_button_href_view_open = 'href="view?id=';
        $this->row_nav_button_href_close = '" tabindex="0" role="button" aria-pressed="false">';
        $this->row_nav_button_view_icon = '<i class="far fa-eye my-3" aria-hidden="true"></i>';
    }

    /**
     * Override table header to give project name column more space
     */
    public function table_header($column_names = null)
    {
        echo $this->header_open;
        foreach ($this->column_names as $key => $value) {
            if ($key == 'action') {
                echo '<th class="col-2 text-center" scope="col">' . $value . '</th>';
            } elseif ($key == 'project_name') {
                // Give project name column extra space (col-2 instead of default)
                echo '<th class="col-2 text-center" scope="col">' . $value . '</th>';
            } else {
                echo '<th class="text-center" scope="col">' . $value . '</th>';
            }
        }
        echo $this->header_close;
    }

    public function table_row_columns($results)
    {
        foreach ($this->column_names as $column_key => $column_title) {
            switch ($column_key) {
                case 'action':
                    echo '<td>';
                    $this->row_nav($results['lead_id'] ?? null, $this->row_id = null);
                    echo '</td>';
                    break;
                
                case 'lead_id':
                    echo '<td>';
                    $value = $results['lead_id'] ?? '';
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
                
                case 'project_name':
                    echo '<td>';
                    $value = $results['project_name'] ?? '';
                    echo htmlspecialchars($value ?: '-');
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
                
                
                default:
                    echo '<td>-</td>';
                    break;
            }
        }
    }

    /**
     * Override to provide only View and Edit buttons (no Delete)
     */
    protected function getButtonsConfig($value)
    {
        return [
            'view' => [
                'class' => $this->row_nav_button_view_class_enabled,
                'href_open' => $this->row_nav_button_href_view_open,
                'href_close' => $this->row_nav_button_href_close,
                'icon' => $this->row_nav_button_view_icon
            ],
            'edit' => [
                'class' => $this->row_nav_button_edit_class_enabled,
                'href_open' => $this->row_nav_button_href_edit_open,
                'href_close' => $this->row_nav_button_href_close,
                'icon' => $this->row_nav_button_edit_icon
            ]
        ];
    }
}