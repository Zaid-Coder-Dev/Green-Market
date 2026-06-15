// Fade-in des produits au chargement
window.addEventListener('load', function () {
    var grid = document.getElementById('products-grid');
    if (grid) {
        grid.style.opacity = '0';
        grid.style.transition = 'opacity 0.4s ease';
        setTimeout(function () {
            grid.style.opacity = '1';
        }, 80);
    }
});

// Filtrage par catégorie (pills)
var pills = document.querySelectorAll('.pill-btn');
var prodCols = document.querySelectorAll('.product-col');
var resultCount = document.getElementById('result-count');

pills.forEach(function (pill) {
    pill.addEventListener('click', function () {
        // Retirer active de tous les pills
        pills.forEach(function (p) { p.classList.remove('active'); });
        this.classList.add('active');

        var categ = this.getAttribute('data-categ');
        var visible = 0;

        prodCols.forEach(function (col) {
            if (categ == 'tous' || col.getAttribute('data-categ') == categ) {
                col.classList.remove('hidden');
                visible++;
            } else {
                col.classList.add('hidden');
            }
        });

        // Mettre à jour le compteur
        if (resultCount) {
            if (visible == 1) {
                resultCount.textContent = '1 produit';
            } else {
                resultCount.textContent = visible + ' produits';
            }
        }
    });
});

// Animation du bouton panier
var cartBtns = document.querySelectorAll('.btn-add');

cartBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
        this.classList.add('btn-clicked');
        var self = this;
        setTimeout(function () {
            self.classList.remove('btn-clicked');
        }, 200);
    });
});
