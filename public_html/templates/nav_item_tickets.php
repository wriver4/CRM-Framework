<?php // if (in_array(8, $_SESSION['permissions'])) : ?>
<li id="ticket"
    class="nav-item">
  <div class="btn-group"
       role="group"
       aria-label="Button group with nested dropdown">
    <a class="btn nav-link link-light<?php echo ($dir == "tickets") ? ' active' : ''; ?>"
       href="/tickets/list"
       tabindex="0">
      <?= $lang['navbar_tickets']; ?></a>
    <div class="btn-group dropdown"
         role="group">
      <button type="button"
              class="btn btn-primary dropdown-toggle"
              data-bs-toggle="dropdown"
              aria-expanded="false">
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item"
             href="/systems/manufacturing"><?= $lang['navbar_tickets_my_tickets']; ?></a></li>
        <?php // if (in_array('8', $_SESSION['permissions'])) : ?>
        <li><a class="dropdown-item"
             href="#"><?= $lang['navbar_tickets_closed_tickets']; ?></a></li>
        <li><a class="dropdown-item"
             href="#"><?= $lang['navbar_tickets_service_contract_management']; ?></a></li>
        <?php // endif; ?>
      </ul>
    </div>
  </div>
</li>
<?php // endif; ?>
