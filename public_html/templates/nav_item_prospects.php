<?php // if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="prospects"
    class="nav-item">
  <a class="btn nav-link link-light<?php echo ($dir == "prospects") ? ' active' : ''; ?>"
     href="<?php echo PROSPECTS; ?>/list"
     tabindex="0">
    <?= $lang['navbar_prospects'] ?? 'Prospects'; ?></a>
</li>
<?php // endif; ?>