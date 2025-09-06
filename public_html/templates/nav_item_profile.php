<div class="dropdown text-end">
  <a href="#"
     class="d-block link-light text-decoration-none dropdown-toggle"
     data-bs-toggle="dropdown"
     aria-expanded="false"
     tabindex="-1">
    <i class="fa fa-user"
       aria-hidden="true"></i>&ensp; <?php echo $_SESSION['full_name'] ?? 'User'; ?></a>
  <ul class="dropdown-menu text-small"
      aria-labelledby="dropdownMenuLink">
    <?php // if (in_array(8, $_SESSION['permissions'])) : ?>
    <li><a class="dropdown-item"
         href="#"
         tabindex="0">Installer add - edit- list</a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/admin/logs/internal' ?>"
         tabindex="0"><?= $lang['internal_error_log']; ?></a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/admin/logs/phperror' ?>"
         tabindex="0"><?= $lang['php_error_log']; ?></a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/admin/logs/audit' ?>"
         tabindex="0"><?= $lang['audit_log']; ?></a>
    </li>
    <li><a class="dropdown-item<?php echo ($dir == "admin/leads") ? ' active' : ''; ?>"
         href="<?php echo URL . '/admin/leads/list' ?>"
         tabindex="0">
         <i class="fa-solid fa-user-shield me-2"></i><?= $lang['navbar_admin_leads'] ?? 'Admin Leads'; ?>
       </a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/maintenance/list' ?>"
         tabindex="0"><?= $lang['navbar_maintenance']; ?></a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/profile/cleanup_duplicate_notes' ?>"
         tabindex="0">
         <i class="fa-solid fa-broom me-2"></i>Cleanup Duplicate Notes
       </a>
    </li>
    <div class="dropdown dropdown-hover-all">
      <div class="dropdown-item dropdown-toggle"
           href="#"
           role="button"
           data-bs-toggle="dropdown"
           aria-haspopup="true"
           aria-expanded="false">
        <i class="fa fa-envelope me-2"></i>Email Processing
      </div>
      <ul class="dropdown-menu text-small">
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/email/processing_log' ?>"
             tabindex="0">
             <i class="fa fa-list me-2"></i>Processing Log
           </a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/email/accounts_config' ?>"
             tabindex="0">
             <i class="fa fa-cog me-2"></i>Email Accounts
           </a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/email/sync_queue' ?>"
             tabindex="0">
             <i class="fa fa-sync me-2"></i>CRM Sync Queue
           </a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/admin/email/system_status' ?>"
             tabindex="0">
             <i class="fa fa-heartbeat me-2"></i>System Status
           </a>
        </li>
      </ul>
    </div>
    <li><a class="dropdown-item<?php echo ($dir == "users") ? ' active' : ''; ?>"
         href="<?php echo URL . '/users/list' ?>"
         tabindex="0"><?= $lang['navbar_users']; ?></a>
    </li>
    <div class="dropdown dropdown-hover-all">
      <div class="dropdown-item dropdown-toggle"
           href="#"
           role="button"
           data-bs-toggle="dropdown"
           aria-haspopup="true"
           aria-expanded="false">
        <?= $lang['navbar_security']; ?>
      </div>
      <ul class="dropdown-menu text-small">
        <li><a class="dropdown-item"
             href="<?php echo URL . '/security/roles/list' ?>"
             tabindex="0"><?= $lang['roles']; ?></a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/security/permissions/list' ?>"
             tabindex="0"><?= $lang['permissions']; ?></a>
        </li>
        <li><a class="dropdown-item"
             href="<?php echo URL . '/security/roles_permissions/list' ?>"
             tabindex="0"><?= $lang['roles_permissions']; ?></a>
        </li>
      </ul>
    </div>
    <?php // endif; ?>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/help' ?>"
         tabindex="0"><?= $lang['navbar_help']; ?></a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/logout' ?>"
         tabindex="0"><?= $lang['navbar_logout']; ?></a>
    </li>
  </ul>
</div>