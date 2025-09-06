<?php
/**
 * Navigation item for Email Import functionality
 * Follows existing CRM framework navigation patterns
 */

// Check if user has permission to access email import
$security = new Security();
if ($security->check_user_permissions('leads', 'read', false)) {
?>
<li class="nav-item">
    <a class="nav-link <?php echo ($dir == 'leads' && $page == 'email_import') ? 'active' : ''; ?>" 
       href="<?php echo LEADS; ?>/email_import.php" 
       title="<?php echo $lang['navbar_email_import_tooltip'] ?? 'Manage email form imports'; ?>">
        <i class="fa fa-envelope"></i>
        <span class="nav-text"><?php echo $lang['navbar_email_import'] ?? 'Email Import'; ?></span>
    </a>
</li>
<?php
}
?>