<?php // if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="contacts"
    class="nav-item">
  <a class="btn nav-link link-light<?php echo ($dir == "contacts") ? ' active' : ''; ?>"
     href="<?php echo CONTACTS; ?>/list"
     tabindex="0">
    <?= $lang['navbar_contacts']; ?></a>
</li>
<?php // endif; ?>