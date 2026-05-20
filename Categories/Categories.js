// ===== COMPTEUR DU PANIER =====
document.addEventListener('DOMContentLoaded', function () {

  // ===== COMPTEUR PANIER =====
  var cartCount = localStorage.getItem('cartCount') || 0;
  var cartBadge = document.getElementById('cart-count');
  if (cartBadge) cartBadge.textContent = cartCount;


});