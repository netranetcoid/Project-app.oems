'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('#formAuthentication');

  if (!form) {
    return;
  }

  form.addEventListener('submit', function () {
    const btn = form.querySelector('button[type="submit"]');

    if (btn) {
      btn.disabled = true;
      btn.innerHTML = 'Memproses...';
    }
  });
});