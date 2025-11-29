document.addEventListener('DOMContentLoaded', function() {
  const deleteLinks = document.querySelectorAll('a[href*="a=inmueble_delete"]');
  deleteLinks.forEach(function(link) {
    link.addEventListener('click', function(e) {
      const ok = window.confirm('¿Eliminar inmueble? Se marcará como inactivo.');
      if (!ok) {
        e.preventDefault();
      }
    });
  });
});
