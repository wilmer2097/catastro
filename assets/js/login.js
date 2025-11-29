(function() {
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    if (!form) { return; }

    const usuario = document.getElementById('usuario');
    const password = document.getElementById('password');

    form.addEventListener('submit', function(e) {
      let isValid = true;
      if (usuario && usuario.value.trim() === '') {
        usuario.classList.add('error');
        isValid = false;
      } else if (usuario) {
        usuario.classList.remove('error');
      }
      if (password && password.value.trim() === '') {
        password.classList.add('error');
        isValid = false;
      } else if (password) {
        password.classList.remove('error');
      }
      if (!isValid) {
        e.preventDefault();
      }
    });

    if (usuario) {
      usuario.addEventListener('input', function() {
        this.classList.remove('error');
      });
    }
    if (password) {
      password.addEventListener('input', function() {
        this.classList.remove('error');
      });
    }
  });
})();
