const formLogin = document.getElementById('form-login');
const formRegister = document.getElementById('form-register');

function showLogin() {
  formLogin.classList.remove('d-none');
  formRegister.classList.add('d-none');
}

function showRegister() {
  formRegister.classList.remove('d-none');
  formLogin.classList.add('d-none');
}

document.querySelectorAll('[data-go="login"]').forEach(el =>
  el.addEventListener('click', function(e) {
    e.preventDefault();
    showLogin();
  })
);
document.querySelectorAll('[data-go="register"]').forEach(el =>
  el.addEventListener('click', function(e) {
    e.preventDefault();
    showRegister();
  })
);

