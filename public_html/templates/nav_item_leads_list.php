<?php // if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="leads-list"
    class="nav-item">
  <a class="btn nav-link link-light<?php echo ($dir == "leads") ? ' active' : ''; ?>"
     href="/leads/list"
     tabindex="0">
    <?= $lang['navbar_leads_list']; ?></a>
</li>
<?php // endif; ?>