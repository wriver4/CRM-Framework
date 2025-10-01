<?php if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="nav-calendar"
    class="nav-item">
  <a class="btn nav-link link-light<?php echo ($dir == "calendar") ? ' active' : ''; ?>"
     href="<?php echo CALENDAR; ?>"
     tabindex="0">
    <?= $lang['navbar_calendar']; ?></a>
</li>
<?php endif; ?>