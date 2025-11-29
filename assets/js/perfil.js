document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form[action="index.php?a=perfil"]');
  if (!form) { return; }
  const cancelLink = form.querySelector('a.btn.btn-outline-secondary[href="index.php?a=home"]');
  if (!cancelLink) { return; }
  cancelLink.addEventListener('click', function(e) {
    e.preventDefault();
    if (window.history.length > 1) {
      window.history.back();
    } else {
      window.location = 'index.php?a=home';
    }
  });

  // Si el perfil se actualizó, refleja el nuevo nombre en el navbar sin recargar sesión
  var success = document.querySelector('.alert-success');
  var nameInput = document.getElementById('ope_nombre');
  var navName = document.getElementById('navUserName');
  if (success && nameInput && navName) {
    var newName = nameInput.value.trim();
    navName.textContent = newName || 'Usuario';
  }
});
