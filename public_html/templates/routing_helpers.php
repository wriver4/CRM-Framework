<?php

/**
 * Routing Helper Functions
 * Multi-level URL generation for the direct routing system
 */

/**
 * Build multi-level URLs supporting up to 4 directory levels
 * 
 * @param string $dir Primary directory
 * @param string $subdir Secondary directory (optional)
 * @param string $sub_subdir Third level directory (optional)
 * @param string $sub_sub_subdir Fourth level directory (optional)
 * @param string $page Page name (optional)
 * @return string Complete URL path
 */
function buildUrl($dir, $subdir = '', $sub_subdir = '', $sub_sub_subdir = '', $page = '') {
    $url = '/' . $dir;
    
    if (!empty($subdir)) {
        $url .= '/' . $subdir;
    }
    
    if (!empty($sub_subdir)) {
        $url .= '/' . $sub_subdir;
    }
    
    if (!empty($sub_sub_subdir)) {
        $url .= '/' . $sub_sub_subdir;
    }
    
    if (!empty($page)) {
        $url .= '/' . $page;
    }
    
    return $url;
}

/**
 * Generate navigation breadcrumb from routing variables
 * 
 * @param string $dir Primary directory
 * @param string $subdir Secondary directory (optional)
 * @param string $sub_subdir Third level directory (optional)
 * @param string $sub_sub_subdir Fourth level directory (optional)
 * @param string $page Current page
 * @return array Breadcrumb array with labels and URLs
 */
function buildBreadcrumb($dir, $subdir = '', $sub_subdir = '', $sub_sub_subdir = '', $page = '') {
    $breadcrumb = [];
    
    // Add primary directory
    $breadcrumb[] = [
        'label' => ucfirst($dir),
        'url' => '/' . $dir,
        'active' => empty($subdir) && empty($page)
    ];
    
    // Add subdirectory if present
    if (!empty($subdir)) {
        $breadcrumb[] = [
            'label' => ucfirst($subdir),
            'url' => buildUrl($dir, $subdir),
            'active' => empty($sub_subdir) && empty($page)
        ];
    }
    
    // Add sub-subdirectory if present
    if (!empty($sub_subdir)) {
        $breadcrumb[] = [
            'label' => ucfirst($sub_subdir),
            'url' => buildUrl($dir, $subdir, $sub_subdir),
            'active' => empty($sub_sub_subdir) && empty($page)
        ];
    }
    
    // Add sub-sub-subdirectory if present
    if (!empty($sub_sub_subdir)) {
        $breadcrumb[] = [
            'label' => ucfirst($sub_sub_subdir),
            'url' => buildUrl($dir, $subdir, $sub_subdir, $sub_sub_subdir),
            'active' => empty($page)
        ];
    }
    
    // Add current page if present
    if (!empty($page)) {
        $breadcrumb[] = [
            'label' => ucfirst(str_replace('_', ' ', $page)),
            'url' => buildUrl($dir, $subdir, $sub_subdir, $sub_sub_subdir, $page),
            'active' => true
        ];
    }
    
    return $breadcrumb;
}

/**
 * Get the parent URL for back navigation
 * 
 * @param string $dir Primary directory
 * @param string $subdir Secondary directory (optional)
 * @param string $sub_subdir Third level directory (optional)
 * @param string $sub_sub_subdir Fourth level directory (optional)
 * @return string Parent URL for back navigation
 */
function getParentUrl($dir, $subdir = '', $sub_subdir = '', $sub_sub_subdir = '') {
    if (!empty($sub_sub_subdir)) {
        return buildUrl($dir, $subdir, $sub_subdir, '', 'list');
    } elseif (!empty($sub_subdir)) {
        return buildUrl($dir, $subdir, '', '', 'list');
    } elseif (!empty($subdir)) {
        return buildUrl($dir, '', '', '', 'list');
    } else {
        return '/dashboard';
    }
}

/**
 * Generate action URLs for CRUD operations
 * 
 * @param string $action Action type (new, edit, view, delete, list)
 * @param string $dir Primary directory
 * @param string $subdir Secondary directory (optional)
 * @param string $sub_subdir Third level directory (optional)
 * @param string $sub_sub_subdir Fourth level directory (optional)
 * @param int $id Record ID for edit/view/delete actions (optional)
 * @return string Complete action URL
 */
function buildActionUrl($action, $dir, $subdir = '', $sub_subdir = '', $sub_sub_subdir = '', $id = null) {
    $url = buildUrl($dir, $subdir, $sub_subdir, $sub_sub_subdir, $action);
    
    if ($id !== null && in_array($action, ['edit', 'view', 'delete'])) {
        $url .= '?id=' . (int)$id;
    }
    
    return $url;
}