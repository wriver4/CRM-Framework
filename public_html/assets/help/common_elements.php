<div class="row align-items-start">
  <p><?= $lang['help_design_feature'];?></p>
</div>
<div class="row align-items-start">
  <p><?= $lang['help_buttons'];?></p>
</div>
<div class="row align-items-start">
  <div class="col-2 py-1">
    <p class="my-3 pe-3"><?= $lang['help_view'];?></p>
  </div>
  <div class="col py-1">
    <a type="button"
       class="btn nav-link btn-info link-light help-button-custom"><i class="far fa-eye my-3"
         aria-hidden="true"></i></a>
  </div>
</div>
<div class="row align-items-start">
  <div class="col-2 py-1">
    <p class="my-3 pe-3"><?= $lang['help_edit'];?></p>
  </div>
  <div class="col py-1">
    <a type="button"
       class="btn nav-link btn-warning link-light help-button-custom"><i class="far fa-edit my-3"
         aria-hidden="true"></i></a>
  </div>
</div>
<div class="row align-items-start">
  <div class="col-2 py-1">
    <p class="my-3 pe-3"><?= $lang['help_delete'];?></p>
  </div>
  <div class="col py-1">
    <a type="button"
       class="btn nav-link btn-danger link-light help-button-custom"><i class="far fa-trash-alt my-3"
         aria-hidden="true"></i></a>
  </div>
</div>
<div class="row align-items-start">
  <div class="col-4 py-1">
    <form class="col mb-2 mb-lg-0 me-lg-auto">
      <input type="text"
             id="search"
             class="form-control"
             placeholder="Search"
             name="search">
    </form>
  </div>
  <div class="col py1">
    <p class="my-3"><?= $lang['help_search'];?></p>
  </div>
  <div class="border border-primary border-top-0">
    <img src="<?= IMG .'/help/entries.png'; ?>"
         class="img-fluid"
         alt="">
  </div>
</div>
<div class="row align-items-start">
  <div class="col-4 py-1">
    <p class="my-3"><?= $lang['help_showall'];?></p>
  </div>
  <div class="col py-1">
    <a href="#"
       class="btn btn-info my-3 help-showall-button-custom"
       tabindex="0"
       role="button"
       aria-pressed="false"><i class="fa fa-list"></i>&ensp;<?= $lang['showall']; ?></a>
  </div>

</div>
