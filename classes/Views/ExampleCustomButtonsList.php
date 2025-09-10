<?php

/**
 * Example class showing how to customize action buttons
 * This demonstrates the flexibility of the new button system
 */

class ExampleCustomButtonsList extends EditDeleteTable
{
    public function __construct($results, $lang)
    {
        $this->column_names = [
            'action' => $lang['action'] ?? 'Action',
            'name' => 'Name',
            'status' => 'Status'
        ];
        parent::__construct($results, $this->column_names, "example-list");
        $this->lang = $lang;
    }

    /**
     * Example 1: Custom 4-button configuration
     * Buttons will automatically use col-3 (25% width each)
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
            ],
            'copy' => [
                'class' => 'class="btn nav-link btn-secondary link-light" ',
                'href_open' => 'href="copy?id=',
                'href_close' => $this->row_nav_button_href_close,
                'icon' => '<i class="far fa-copy my-3" aria-hidden="true"></i>'
            ],
            'archive' => [
                'class' => 'class="btn nav-link btn-dark link-light" ',
                'href_open' => 'href="archive?id=',
                'href_close' => $this->row_nav_button_href_close,
                'icon' => '<i class="fas fa-archive my-3" aria-hidden="true"></i>'
            ]
        ];
    }

    public function table_row_columns($results)
    {
        foreach ($this->column_names as $column_key => $column_title) {
            switch ($column_key) {
                case 'action':
                    echo '<td>';
                    $this->row_nav($results['id'] ?? null, null);
                    echo '</td>';
                    break;
                
                default:
                    echo '<td>';
                    echo htmlspecialchars($results[$column_key] ?? '-');
                    echo '</td>';
                    break;
            }
        }
    }
}

/**
 * Example 2: Single action button list
 */
class ExampleSingleButtonList extends EditDeleteTable
{
    public function __construct($results, $lang)
    {
        $this->column_names = [
            'action' => $lang['action'] ?? 'Action',
            'name' => 'Name'
        ];
        parent::__construct($results, $this->column_names, "single-button-list");
        $this->lang = $lang;
    }

    /**
     * Single button configuration - will use col-12 (100% width)
     */
    protected function getButtonsConfig($value)
    {
        return [
            'download' => [
                'class' => 'class="btn nav-link btn-success link-light" ',
                'href_open' => 'href="download?id=',
                'href_close' => $this->row_nav_button_href_close,
                'icon' => '<i class="fas fa-download my-3" aria-hidden="true"></i>'
            ]
        ];
    }

    public function table_row_columns($results)
    {
        foreach ($this->column_names as $column_key => $column_title) {
            switch ($column_key) {
                case 'action':
                    echo '<td>';
                    $this->row_nav($results['id'] ?? null, null);
                    echo '</td>';
                    break;
                
                default:
                    echo '<td>';
                    echo htmlspecialchars($results[$column_key] ?? '-');
                    echo '</td>';
                    break;
            }
        }
    }
}