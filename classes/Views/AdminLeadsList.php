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
     * Override row navigation to show only edit button
     * 
     * @param mixed $value The lead ID value
     * @param mixed $rid The row ID (unused in this implementation)
     */
    public function row_nav($value, $rid)
    {
        echo $this->row_nav_open;

        // Only edit button - no view, no delete
        echo $this->row_nav_button_open;
        echo $this->row_nav_button_edit_class_enabled;
        echo $this->row_nav_button_href_edit_open
            . urlencode($value)
            . $this->row_nav_button_href_close
            . $this->row_nav_button_edit_icon
            . $this->row_nav_button_close;

        echo $this->row_nav_close;
    }
}