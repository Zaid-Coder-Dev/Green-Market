// ===== COMPTEUR DU PANIER =====
document.addEventListener('DOMContentLoaded', function () {

  // ===== COMPTEUR PANIER =====
  var cartCount = localStorage.getItem('cartCount') || 0;
  var cartBadge = document.getElementById('cart-count');
  if (cartBadge) cartBadge.textContent = cartCount;

  // ===== COMPTEUR NOTIFICATIONS =====
  var bellCount = localStorage.getItem('bellCount') || 0;
  var bellBadge = document.getElementById('bell-count');
  if (bellBadge) bellBadge.textContent = bellCount;

});