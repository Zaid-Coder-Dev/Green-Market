// ===== TRI : change l'URL quand on sélectionne une option =====
document.getElementById('sort-select').addEventListener('change', function () {
    const url = new URL(window.location.href);
    url.searchParams.set('tri', this.value);
    url.searchParams.set('page', '1'); // retour à la page 1 si on change le tri
    window.location.href = url.toString();
});

// ===== APPARITION DOUCE DE LA GRILLE AU CHARGEMENT =====
const grille = document.querySelector('.product-grid');
if (grille) {
    grille.style.opacity = '0';
    grille.style.transition = 'opacity 0.45s ease';
    window.addEventListener('load', function () {
        grille.style.opacity = '1';
    });
}

// ===== PANIER : visuel uniquement pour l'instant =====
let cartCount = 0;

document.querySelectorAll('.btn-add').forEach(function (btn) {
    btn.addEventListener('click', function () {
        cartCount++;
        document.getElementById('cart-count').textContent = cartCount;

        // Animation rapide sur le bouton
        btn.classList.add('btn-clicked');
        setTimeout(function () {
            btn.classList.remove('btn-clicked');
        }, 200);
    });
});
