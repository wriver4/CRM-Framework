<?php // if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="prospecting"
    class="nav-item">
  <a class="btn nav-link link-light<?php echo ($dir == "prospecting") ? ' active' : ''; ?>"
     href="<?php echo PROSPECTING; ?>/list"
     tabindex="0">
    <?= $lang['navbar_prospecting'] ?? 'Prospecting'; ?></a>
</li>
<?php // endif; ?>