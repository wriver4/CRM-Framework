<div class="dropdown text-end">
  <a href="#"
     class="d-block link-light text-decoration-none dropdown-toggle"
     data-bs-toggle="dropdown"
     aria-expanded="false"
     tabindex="-1">
    <i class="fa fa-user"
       aria-hidden="true"></i>&ensp; <?php echo $_SESSION['full_name']; ?></a>
  <ul class="dropdown-menu text-small"
      aria-labelledby="dropdownMenuLink">
    <?php // if (in_array(8, $_SESSION['permissions'])) : ?>
    <li><a class="dropdown-item"
         href="#"
         tabindex="0">Installer add - edit- list</a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/logs/internal' ?>"
         tabindex="0"><?= $lang['internal_error_log']; ?></a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/logs/phperror' ?>"
         tabindex="0"><?= $lang['php_error_log']; ?></a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/logs/audit' ?>"
         tabindex="0"><?= $lang['audit_log']; ?></a>
    </li>
    <li><a class="dropdown-item"
         href="<?php echo URL . '/maintenance/list' ?>"
         tabindex="0"><?= $lang['navbar_maintenance']; ?></a>
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