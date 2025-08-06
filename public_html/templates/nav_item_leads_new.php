<?php // if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="leads-new"
    class="nav-item">
  <a class="btn nav-link link-light<?php echo ($dir == "leads") ? ' active' : ''; ?>"
     href="/leads/new"
     tabindex="0">
    <?= $lang['navbar_leads_new']; ?></a>
</li>
<?php // endif; ?>