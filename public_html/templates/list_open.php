<section class="pt-5 mt-4">
  <div class="container-lg">
    <div class="row">
      <div class="col">
        <h2><?= $title_icon; ?> <?= $title; ?></h2>
      </div>
    </div>
  </div>
  <?php if ($table_header) { ?>
  <div class="container d-flex flex-wrap justify-content-center">
    <form class="col-12 col-lg-auto mb-2 mb-lg-0 me-lg-auto">
      <?php if ($search == true) { ?>
      <input type="text"
             id="search"
             class="form-control"
             placeholder="<?= $lang['search_placeholder']; ?>"
             name="search">
      <?php } ?>
    </form>
    <div class="text-end">
      <?php require LISTBUTTONS; ?>
    </div>
  </div>
  <br>
  <?php } ?>
