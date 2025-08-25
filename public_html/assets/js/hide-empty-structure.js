// Hides the Structure Other and Additional sections on the edit page when empty
(function () {
  function hideIfEmpty(hiddenName) {
    var hidden = document.querySelector('input[name="' + hiddenName + '"]');
    if (!hidden) return;
    var val = (hidden.value || '').trim();
    if (!val) {
      var group = hidden.closest('.form-group');
      if (group) {
        group.style.display = 'none';
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      hideIfEmpty('structure_other');
      hideIfEmpty('structure_additional');
    });
  } else {
    hideIfEmpty('structure_other');
    hideIfEmpty('structure_additional');
  }
})();
