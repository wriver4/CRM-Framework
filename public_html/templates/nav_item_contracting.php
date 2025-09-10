<?php // if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="contracting"
    class="nav-item">
  <a class="btn nav-link link-light<?php echo ($dir == "contracting") ? ' active' : ''; ?>"
     href="<?php echo CONTRACTING; ?>/list"
     tabindex="0">
    <?= $lang['navbar_contracting'] ?? 'Contracting'; ?></a>
</li>
<?php // endif; ?>