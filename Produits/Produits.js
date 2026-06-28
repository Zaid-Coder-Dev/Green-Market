

// --- MENU NAVBAR (cart/profil/langue) ---
var navToggleBtn = document.getElementById('navToggleBtn');
var navExpand = document.getElementById('navExpand');
var masterDot = document.getElementById('masterDot');

if (navToggleBtn) {
    navToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        navExpand.classList.toggle('open');
        var estOuvert = navExpand.classList.contains('open');
        navToggleBtn.setAttribute('aria-expanded', estOuvert);
        if (masterDot) {
            if (estOuvert) {
                masterDot.style.display = 'none';
            } else {
                masterDot.style.display = 'block';
            }
        }a
    });

    document.addEventListener('click', function(e) {
        if (!navExpand.contains(e.target) && !navToggleBtn.contains(e.target)) {
            navExpand.classList.remove('open');
            navToggleBtn.setAttribute('aria-expanded', 'false');
        }
    });
}

// --- TOGGLE LANGUE FR/EN ---
var langToggle = document.getElementById('langToggle');

if (langToggle) {
    langToggle.addEventListener('click', function() {
        if (langToggle.textContent == 'FR') {
            langToggle.textContent = 'EN';
            document.cookie = 'lang=EN; path=/; max-age=2592000';
        } else {
            langToggle.textContent = 'FR';
            document.cookie = 'lang=FR; path=/; max-age=2592000';
        }
    });
}

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
