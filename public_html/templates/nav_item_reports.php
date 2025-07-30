<?php // if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="reports"
    class="nav-item">
  <a class="btn nav-link link-light<?php  echo ($dir == "reports") ? ' active' : ''; ?>"
     href="/reports/index"
     tabindex="0">
    <?= $lang['navbar_reports']; ?></a>
</li>
<?php // endif; ?>
