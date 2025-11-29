document.addEventListener('DOMContentLoaded', function() {
  const deactivateLinks = document.querySelectorAll('a[href*="a=operador_toggle"][href*="&b=0"]');
  deactivateLinks.forEach(function(link) {
    link.addEventListener('click', function(e) {
      if (!window.confirm('¿Desactivar operador?')) {
        e.preventDefault();
      }
    });
  });
});
