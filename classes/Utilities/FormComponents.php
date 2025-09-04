<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * FormComponents class for building form elements
 * 
 * This class provides utilities for generating form components
 * with multilingual support and consistent styling.
 */
class FormComponents extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate a basic input field
     */
    public function input($type, $name, $value = '', $attributes = [])
    {
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
        }
        
        return '<input type="' . $type . '" name="' . $name . '" value="' . htmlspecialchars($value) . '"' . $attrs . '>';
    }

    /**
     * Generate a select dropdown
     */
    public function select($name, $options = [], $selected = '', $attributes = [])
    {
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
        }
        
        $html = '<select name="' . $name . '"' . $attrs . '>';
        
        foreach ($options as $value => $label) {
            $selectedAttr = ($value == $selected) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $selectedAttr . '>' . htmlspecialchars($label) . '</option>';
        }
        
        $html .= '</select>';
        
        return $html;
    }

    /**
     * Generate a textarea
     */
    public function textarea($name, $value = '', $attributes = [])
    {
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
        }
        
        return '<textarea name="' . $name . '"' . $attrs . '>' . htmlspecialchars($value) . '</textarea>';
    }
}