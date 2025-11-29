document.addEventListener('DOMContentLoaded', function() {
  const deleteLinks = document.querySelectorAll('a[href*="a=negocio_delete"]');
  deleteLinks.forEach(function(link) {
    link.addEventListener('click', function(e) {
      if (!window.confirm('¿Eliminar negocio?')) {
        e.preventDefault();
      }
    });
  });

  // Dropdown con buscador para seleccionar inmueble
  var hidden = document.getElementById('inmuebleIdInput');
  var dropdown = document.querySelector('[data-search-dropdown]');
  if (hidden && dropdown) {
    var list = dropdown.querySelector('[data-search-list]');
    var search = dropdown.querySelector('[data-search-input]');
    var label = document.getElementById('inmuebleDropdownLabel');
    var btn = document.getElementById('inmuebleDropdownBtn');

    dropdown.addEventListener('click', function(e) {
      if (e.target.matches('[data-id]')) {
        e.preventDefault();
        var id = e.target.getAttribute('data-id') || '';
        var text = e.target.getAttribute('data-label') || 'Todos los inmuebles';
        hidden.value = id;
        if (label) { label.textContent = text; }
        list.querySelectorAll('.active').forEach(function(el) { el.classList.remove('active'); });
        e.target.classList.add('active');
        var dropdownEl = window.bootstrap ? window.bootstrap.Dropdown.getInstance(btn) : null;
        if (dropdownEl) { dropdownEl.hide(); }
      }
    });

    if (search && list) {
      search.addEventListener('input', function() {
        var term = search.value.toLowerCase();
        list.querySelectorAll('[data-id]').forEach(function(el) {
          var text = (el.getAttribute('data-label') || '').toLowerCase();
          el.style.display = text.indexOf(term) !== -1 ? '' : 'none';
        });
      });
    }
  }
});
