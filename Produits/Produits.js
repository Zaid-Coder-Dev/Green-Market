// ===== TRI PAR SÉLECTION =====
document.getElementById('sort-select').addEventListener('change', function () {
    const url = new URL(window.location.href);
    url.searchParams.set('tri', this.value);
    window.location.href = url.toString();
});

// ===== APPARITION DOUCE DE LA GRILLE =====
const grille = document.querySelector('.product-grid');
if (grille) {
    grille.style.opacity = '0';
    grille.style.transition = 'opacity 0.4s ease';
    window.addEventListener('load', function () {
        grille.style.opacity = '1';
    });
}

// ===== PANIER (visuel uniquement pour l'instant) =====
let cartCount = 0;

document.querySelectorAll('.btn-add').forEach(function (btn) {
    btn.addEventListener('click', function () {
        cartCount++;
        document.getElementById('cart-count').textContent = cartCount;
    });
});