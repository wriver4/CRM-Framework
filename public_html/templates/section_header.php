<?php
/**
 * Section Header Template
 * 
 * Optional template to be included after section_open
 * Displays record information and action buttons
 * 
 * Variables that can be set before including this template:
 * $section_header = true/false - Enable/disable section header
 * $section_header_title - Main title text
 * $section_header_subtitle - Subtitle text
 * $section_header_icon - Font Awesome icon class
 * $section_header_badge - Badge text/content
 * $section_header_badge_class - Badge CSS class
 * $section_header_actions - Array of action buttons
 * $section_header_info - Array of info items (label => value)
 */

// Only render if section_header is enabled
if (!isset($section_header) || !$section_header) {
    return;
}
?>

<div class="section-header mb-4">
    <div class="card">
        <div class="card-body">
            <!-- Single Row Layout -->
            <div class="d-flex justify-content-between align-items-center">
                <!-- Updated Info -->
                <div class="d-flex align-items-center">
                    <?php if (!empty($section_header_info) && is_array($section_header_info)): ?>
                    <span class="fs-6">
                        <span class="text-muted">Updated</span> <?= htmlspecialchars($section_header_info['Updated'] ?? '-') ?> 
                        <span class="text-muted">by</span> <?= htmlspecialchars($section_header_info['by'] ?? '-') ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- Action Buttons -->
                <?php if (!empty($section_header_actions) && is_array($section_header_actions)): ?>
                <div class="d-flex gap-2">
                    <?php foreach ($section_header_actions as $action): ?>
                    <a href="<?= htmlspecialchars($action['href']) ?>" 
                       class="btn <?= $action['class'] ?? 'btn-primary' ?> <?= $action['size'] ?? 'btn-sm' ?>">
                        <?php if (!empty($action['icon'])): ?>
                        <i class="<?= $action['icon'] ?> me-1"></i>
                        <?php endif; ?>
                        <?= $action['text'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>