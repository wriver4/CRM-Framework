<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * Admin-specific leads list view
 * 
 * Extends the regular LeadsList but only shows edit button (no view, no delete)
 * Used in admin interface where administrators can edit leads but not view/delete them
 */
class AdminLeadsList extends LeadsList
{
    /**
     * Override to provide only Edit button (no View, no Delete)
     */
    protected function getButtonsConfig($value)
    {
        return [
            'edit' => [
                'class' => $this->row_nav_button_edit_class_enabled,
                'href_open' => $this->row_nav_button_href_edit_open,
                'href_close' => $this->row_nav_button_href_close,
                'icon' => $this->row_nav_button_edit_icon
            ]
        ];
    }
}