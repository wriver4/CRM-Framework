<?php
// Get current user's role name
$currentUserId = $_SESSION['user_id'] ?? 0;
$userRoleName = 'User'; // Default fallback
if ($currentUserId > 0) {
    $usersModel = new Users();
    $currentUser = $usersModel->get_by_id($currentUserId);
    $userRoleName = $currentUser['rname'] ?? 'User';
}
?>
<div class="dropdown text-end">
  <a href="#"
     id="dropdownMenuLink"
     class="d-block link-light text-decoration-none dropdown-toggle"
     data-bs-toggle="dropdown"
     aria-expanded="false"
     role="button">
    <i class="fa fa-user"
       aria-hidden="true"></i>&ensp; <?php echo $_SESSION['full_name'] ?? 'User'; ?></a>
  <ul class="dropdown-menu text-small"
      aria-labelledby="dropdownMenuLink">
    
    <!-- User Profile Section -->
    <li><h6 class="dropdown-header"><?= $lang['navbar_profile']; ?></h6></li>
    <li><span class="dropdown-item-text text-muted small">
        <i class="fa fa-id-badge me-2"></i><?= $userRoleName; ?>
    </span></li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/profile/language' ?>">
         <i class="fa fa-language me-2"></i><?= $lang['language_preferences']; ?>
       </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    
    <!-- Help & Support Section -->
    <li><h6 class="dropdown-header"><?= $lang['navbar_help_support']; ?></h6></li>
    <!-- TODO: Uncomment when Help module is built
    <li><a class="dropdown-item"
         href="<?php echo URL . '/help' ?>">
         <i class="fa fa-question-circle me-2"></i><?= $lang['navbar_help']; ?>
       </a>
    </li>
    -->
    <li><a class="dropdown-item"
         href="<?php echo URL . '/logout' ?>">
         <i class="fa fa-sign-out-alt me-2"></i><?= $lang['navbar_logout']; ?>
       </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    
    <!-- Administration Section -->
    <?php if (in_array(1030, $_SESSION['permissions'] ?? [])) : ?>
    <li><h6 class="dropdown-header"><?= $lang['navbar_administration']; ?></h6></li>
    <li><a class="dropdown-item<?php echo ($dir == "admin/leads") ? ' active' : ''; ?>"
         href="<?php echo URL . '/admin/leads/list' ?>">
         <i class="fa-solid fa-user-shield me-2"></i><?= $lang['navbar_admin_leads']; ?>
       </a>
    </li>
    <li class="dropdown dropend">
      <a class="dropdown-item dropdown-toggle"
         href="#"
         id="emailDropdown"
         role="button"
         data-bs-toggle="dropdown"
         aria-expanded="false">
        <i class="fa fa-envelope me-2"></i><?= $lang['email_processing']; ?>
      </a>
      <ul class="dropdown-menu" aria-labelledby="emailDropdown">
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/email/processing_log' ?>">
             <i class="fa fa-list me-2"></i><?= $lang['processing_log']; ?>
           </a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/email/accounts_config' ?>">
             <i class="fa fa-cog me-2"></i><?= $lang['email_accounts']; ?>
           </a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/email/smtp_config/list.php' ?>">
             <i class="fa fa-paper-plane me-2"></i><?= $lang['smtp_config'] ?? 'SMTP Configuration'; ?>
           </a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/email/sync_queue' ?>">
             <i class="fa fa-sync me-2"></i><?= $lang['crm_sync_queue']; ?>
           </a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/email/system_status' ?>">
             <i class="fa fa-heartbeat me-2"></i><?= $lang['system_status']; ?>
           </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/system_email_management' ?>">
             <i class="fa fa-file-text me-2"></i><?= $lang['email_templates']; ?>
           </a>
        </li>
      </ul>
    </li>
    <li class="dropdown dropend">
      <a class="dropdown-item dropdown-toggle"
         href="#"
         id="logsDropdown"
         role="button"
         data-bs-toggle="dropdown"
         aria-expanded="false">
        <i class="fa fa-file-text me-2"></i><?= $lang['navbar_logs']; ?>
      </a>
      <ul class="dropdown-menu"
          aria-labelledby="logsDropdown">
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/logs/internal' ?>">
             <i class="fa fa-exclamation-triangle me-2"></i><?= $lang['internal_error_log']; ?>
           </a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/logs/phperror' ?>">
             <i class="fa fa-bug me-2"></i><?= $lang['php_error_log']; ?>
           </a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/logs/audit' ?>">
             <i class="fa fa-history me-2"></i><?= $lang['audit_log']; ?>
           </a>
        </li>
      </ul>
    </li>
    <li class="dropdown dropend">
      <a class="dropdown-item dropdown-toggle"
         href="#"
         id="toolsDropdown"
         role="button"
         data-bs-toggle="dropdown"
         aria-expanded="false">
        <i class="fa fa-tools me-2"></i><?= $lang['navbar_tools']; ?>
      </a>
      <ul class="dropdown-menu" aria-labelledby="toolsDropdown">
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/tools/autoloaded_classes' ?>">
             <i class="fa fa-code me-2"></i><?= $lang['tools_autoloaded_classes']; ?>
           </a>
        </li>
      </ul>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/admin/languages/list' ?>">
         <i class="fa fa-globe me-2"></i><?= $lang['manage_languages']; ?>
       </a>
    </li>
    <li class="dropdown dropend">
      <a class="dropdown-item dropdown-toggle"
         href="#"
         id="securityDropdown"
         role="button"
         data-bs-toggle="dropdown"
         aria-expanded="false">
        <i class="fa fa-shield-alt me-2"></i><?= $lang['navbar_security']; ?>
      </a>
      <ul class="dropdown-menu" aria-labelledby="securityDropdown">
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/security/roles/list' ?>"><?= $lang['roles']; ?></a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/security/permissions/list' ?>"><?= $lang['permissions']; ?></a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/security/roles_permissions/list' ?>"><?= $lang['roles_permissions']; ?></a>
        </li>
      </ul>
    </li>
    <!-- TODO: Uncomment when System Messages module is built
    <li><a class="dropdown-item"
         href="<?php echo URL . '/admin/system_messages/list' ?>">
         <i class="fa fa-comments me-2"></i><?= $lang['navbar_system_messages']; ?>
       </a>
    </li>
    -->
    <li><a class="dropdown-item<?php echo ($dir == "users") ? ' active' : ''; ?>"
         href="<?php echo URL . '/users/list' ?>">
         <i class="fa fa-users me-2"></i><?= $lang['navbar_users']; ?>
       </a>
    </li>
    <?php endif; ?>
  </ul>
</div>