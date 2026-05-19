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

<<<<<<< Updated upstream
// ===== UN SEUL LISTENER POUR TOUS LES ELEMENTS AVEC DATA-SECTION =====
=======
<<<<<<< HEAD
<<<<<<< HEAD
// ===== UN SEUL LISTENER POUR TOUS LES ÉLÉMENTS AVEC DATA-SECTION =====
=======
// ===== UN SEUL LISTENER POUR TOUS LES ELEMENTS AVEC DATA-SECTION =====
>>>>>>> 25612654b892024e35ca44556b50b5a97cdc5cbf
=======
// ===== UN SEUL LISTENER POUR TOUS LES ELEMENTS AVEC DATA-SECTION =====
>>>>>>> 25612654b892024e35ca44556b50b5a97cdc5cbf
>>>>>>> Stashed changes
document.querySelectorAll('[data-section]').forEach(function(el) {
  el.addEventListener('click', function(e) {
    e.preventDefault();
    let sectionId = this.getAttribute('data-section');
    if (sectionId) showSection(sectionId);
  });
});

<<<<<<< Updated upstream
=======
<<<<<<< HEAD
<<<<<<< HEAD
=======
=======
>>>>>>> 25612654b892024e35ca44556b50b5a97cdc5cbf
>>>>>>> Stashed changes
// ===== BELL ICON NAVBAR → NOTIFICATIONS =====
let bellBtn = document.querySelector('.bell-btn');
if (bellBtn) {
  bellBtn.addEventListener('click', function(e) {
    e.preventDefault();
    showSection('Notification');
  });
}

<<<<<<< Updated upstream
=======
<<<<<<< HEAD
>>>>>>> 25612654b892024e35ca44556b50b5a97cdc5cbf
=======
>>>>>>> 25612654b892024e35ca44556b50b5a97cdc5cbf
>>>>>>> Stashed changes
// ===== NOTIF TABS =====
document.querySelectorAll('.notif-tabs button').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.notif-tabs button').forEach(function(b) {
      b.classList.remove('active');
    });
    this.classList.add('active');
  });
});