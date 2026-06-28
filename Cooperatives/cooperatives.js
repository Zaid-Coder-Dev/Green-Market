// ===== MENU NAVBAR (icônes panier / profil / langue) =====
const navToggleBtn = document.getElementById('navToggleBtn');
const navExpand = document.getElementById('navExpand');
const masterDot = document.getElementById('masterDot');

if (navToggleBtn && navExpand) {
  navToggleBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    navExpand.classList.toggle('open');
    const isOpen = navExpand.classList.contains('open');
    navToggleBtn.setAttribute('aria-expanded', isOpen);
    if (masterDot) {
      if (isOpen) {
        masterDot.style.display = 'none';
      } else {
        masterDot.style.display = 'block';
      }
    }
  });

  document.addEventListener('click', function(e) {
    if (!navExpand.contains(e.target) && !navToggleBtn.contains(e.target)) {
      navExpand.classList.remove('open');
      navToggleBtn.setAttribute('aria-expanded', 'false');
    }
  });
}

// ===== TOGGLE LANGUE (FR/EN) =====
const langToggle = document.getElementById('langToggle');
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


(function () {
  var searchInput  = document.getElementById('searchInput');
  var villeFilter  = document.getElementById('villeFilter');
  var resultCount  = document.getElementById('resultCount');
  var emptyState   = document.getElementById('emptyState');
  var cards        = document.querySelectorAll('.gm-card-wrapper');

  function filterCards() {
    var search = searchInput.value.toLowerCase().trim();
    var ville  = villeFilter.value;
    var visible = 0;

    cards.forEach(function (card) {
      var nomMatch   = card.dataset.nom.indexOf(search) !== -1;
      var villeMatch = ville == '' || card.dataset.ville == ville;

      if (nomMatch && villeMatch) {
        card.style.display = '';
        visible++;
      } else {
        card.style.display = 'none';
      }
    });

    resultCount.textContent = visible + ' coopérative' + (visible > 1 ? 's' : '');
    emptyState.classList.toggle('d-none', visible !== 0);
  }

  searchInput.addEventListener('input', filterCards);
  villeFilter.addEventListener('change', filterCards);
})();