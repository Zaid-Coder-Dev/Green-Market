// ===== BOUTONS QUANTITÉ =====

document.querySelectorAll('.btn-qty').forEach(function(btn) {
  btn.addEventListener('click', function() {

    // Trouver le span qty à côté du bouton
    let qtySpan = this.parentElement.querySelector('.qty');
    let qty = parseInt(qtySpan.textContent);

    // Si c'est + ajouter 1
    if (this.textContent === '+') {
      qtySpan.textContent = qty + 1;
    }

    // Si c'est − enlever 1 mais pas en dessous de 1
    if (this.textContent === '−' && qty > 1) {
      qtySpan.textContent = qty - 1;
    }

  });
});


// ===== SUPPRIMER UN ARTICLE =====

document.querySelectorAll('.btn-remove').forEach(function(btn) {
  btn.addEventListener('click', function() {

    // Trouver le cart-item parent et le supprimer
    let item = this.closest('.cart-item');
    let hr = item.nextElementSibling;

    item.remove();
    if (hr) hr.remove();

  });
});