// 1. Compteur panier (same as home)
let cartCount = 0;
document.querySelectorAll('.btn-add').forEach(function(btn) {
  btn.addEventListener('click', function() {
    cartCount++;
    document.querySelector('.cart-badge').textContent = cartCount;
  });
});