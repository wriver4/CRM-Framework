<?php // if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="referrals"
    class="nav-item">
  <a class="btn nav-link link-light<?php echo ($dir == "referrals") ? ' active' : ''; ?>"
     href="<?php echo REFERRALS; ?>/list"
     tabindex="0">
    <?= $lang['navbar_referrals'] ?? 'Referrals'; ?></a>
</li>
<?php // endif; ?>