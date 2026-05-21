// ===== PANIER =====
let cartCount = 0;
const cartBadge = document.querySelector('.cart-badge');

document.querySelectorAll('.btn-add').forEach(btn => {
  btn.addEventListener('click', () => {
    cartCount++;
    if (cartBadge) cartBadge.textContent = cartCount;
  });
});