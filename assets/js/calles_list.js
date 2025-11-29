document.addEventListener('DOMContentLoaded', function() {
  const deleteLinks = document.querySelectorAll('a[href*="a=calle_delete"]');
  deleteLinks.forEach(function(link) {
    link.addEventListener('click', function(e) {
      if (!window.confirm('¿Eliminar calle?')) {
        e.preventDefault();
      }
    });
  });
});
