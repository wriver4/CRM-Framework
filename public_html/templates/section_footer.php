<?php
/**
 * Section Footer Template
 * 
 * Optional template to be included before section_close
 * Can display footer information, additional actions, or metadata
 * 
 * Variables that can be set before including this template:
 * $section_footer = true/false - Enable/disable section footer
 * $section_footer_content - Main footer content/HTML
 * $section_footer_actions - Array of footer action buttons
 * $section_footer_info - Array of footer info items
 * $section_footer_class - Additional CSS classes for footer container
 */

// Only render if section_footer is enabled
if (!isset($section_footer) || !$section_footer) {
    return;
}
?>

<div class="section-footer mt-4 <?= $section_footer_class ?? '' ?>">
    <?php if (!empty($section_footer_content)): ?>
    <div class="card">
        <div class="card-body">
            <?= $section_footer_content ?>
            
            <!-- Footer Actions -->
            <?php if (!empty($section_footer_actions) && is_array($section_footer_actions)): ?>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <?php foreach ($section_footer_actions as $action): ?>
                <a href="<?= htmlspecialchars($action['href']) ?>" 
                   class="btn <?= $action['class'] ?? 'btn-secondary' ?> <?= $action['size'] ?? 'btn-sm' ?>">
                    <?php if (!empty($action['icon'])): ?>
                    <i class="<?= $action['icon'] ?> me-1"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($action['text']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Footer Info -->
    <?php if (!empty($section_footer_info) && is_array($section_footer_info)): ?>
    <div class="text-muted text-center mt-3">
        <small>
            <?php foreach ($section_footer_info as $label => $value): ?>
            <span class="me-3">
                <strong><?= htmlspecialchars($label) ?>:</strong> 
                <?= is_string($value) ? htmlspecialchars($value) : $value ?>
            </span>
            <?php endforeach; ?>
        </small>
    </div>
    <?php endif; ?>
</div>