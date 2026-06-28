// ===== NAVBAR TOGGLE =====
(function () {
  var navToggleBtn = document.getElementById('navToggleBtn');
  var navExpand = document.getElementById('navExpand');
  var masterDot = document.getElementById('masterDot');

  if (navToggleBtn && navExpand) {
    navToggleBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      navExpand.classList.toggle('open');
      var isOpen = navExpand.classList.contains('open');
      navToggleBtn.setAttribute('aria-expanded', isOpen);
      if (masterDot) {
        masterDot.style.display = isOpen ? 'none' : '';
      }
    });

    document.addEventListener('click', function (e) {
      if (!navExpand.contains(e.target) && e.target !== navToggleBtn) {
        navExpand.classList.remove('open');
        navToggleBtn.setAttribute('aria-expanded', false);
      }
    });
  }

  // ===== LANG TOGGLE =====
  var langToggle = document.getElementById('langToggle');
  if (langToggle) {
    langToggle.addEventListener('click', function () {
      var current = langToggle.textContent.trim();
      var next = current == 'FR' ? 'EN' : 'FR';
      langToggle.textContent = next;
      document.cookie = 'lang=' + next + '; path=/; max-age=2592000';
    });
  }
})();

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
