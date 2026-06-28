// ===== NAVBAR TOGGLE =====
var navToggleBtn = document.getElementById('navToggleBtn');
var navExpand    = document.getElementById('navExpand');
var masterDot    = document.getElementById('masterDot');

if (navToggleBtn) {
    navToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        navExpand.classList.toggle('open');
        var estOuvert = navExpand.classList.contains('open');
        navToggleBtn.setAttribute('aria-expanded', estOuvert);
        if (masterDot) {
            masterDot.style.display = estOuvert ? 'none' : 'block';
        }
    });
    document.addEventListener('click', function(e) {
        if (!navExpand.contains(e.target) && !navToggleBtn.contains(e.target)) {
            navExpand.classList.remove('open');
            navToggleBtn.setAttribute('aria-expanded', 'false');
        }
    });
}

// ===== TOGGLE LANGUE FR/EN =====
var langToggle = document.getElementById('langToggle');
if (langToggle) {
    langToggle.addEventListener('click', function() {
        if (langToggle.textContent.trim() == 'FR') {
            langToggle.textContent = 'EN';
            document.cookie = 'lang=EN; path=/; max-age=2592000';
        } else {
            langToggle.textContent = 'FR';
            document.cookie = 'lang=FR; path=/; max-age=2592000';
        }
    });
}


// ===== FONCTION CENTRALE DE NAVIGATION =====
function showSection(sectionId) {

  // Cacher toutes les sections
  document.querySelectorAll('.section').forEach(function(sec) {
    sec.classList.add('d-none');
  });

  // Montrer la section demandée
  let section = document.getElementById(sectionId);
  if (section) section.classList.remove('d-none');

  // Mettre à jour le lien actif dans la sidebar
  document.querySelectorAll('.sidebar-link').forEach(function(l) {
    l.classList.remove('active');
  });
  let activeLink = document.querySelector('.sidebar-link[data-section="' + sectionId + '"]');
  if (activeLink) activeLink.classList.add('active');
}

// ===== UN SEUL LISTENER POUR TOUS LES ELEMENTS AVEC DATA-SECTION =====
document.querySelectorAll('[data-section]').forEach(function(el) {
  el.addEventListener('click', function(e) {
    e.preventDefault();
    let sectionId = this.getAttribute('data-section');
    if (sectionId) showSection(sectionId);
  });
});

// ===== BELL ICON NAVBAR → NOTIFICATIONS =====
let bellBtn = document.querySelector('.bell-btn');
if (bellBtn) {
  bellBtn.addEventListener('click', function(e) {
    e.preventDefault();
    showSection('Notification');
  });
}

// ===== NOTIF TABS =====
document.querySelectorAll('.notif-tabs button').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.notif-tabs button').forEach(function(b) {
      b.classList.remove('active');
    });
    this.classList.add('active');
  });
});