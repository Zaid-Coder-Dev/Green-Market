// ===== COMPTEUR PANIER =====
document.querySelectorAll('.btn-add').forEach(function(btn) {
  btn.addEventListener('click', function() {
    // Lire et incrémenter le compteur du panier
    let badge = document.getElementById('cart-count');
    let count = parseInt(badge.textContent) || 0;
    badge.textContent = count + 1;
  });
});