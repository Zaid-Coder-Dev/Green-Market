// Changer de section quand on clique un lien sidebar
document.querySelectorAll('.sidebar-link').forEach(function(link) {
  link.addEventListener('click', function(e) {
    e.preventDefault();

    // Cacher toutes les sections
    document.querySelectorAll('.section').forEach(function(section) {
      section.classList.add('d-none');
    });

    // Enlever active de tous les liens
    document.querySelectorAll('.sidebar-link').forEach(function(l) {
      l.classList.remove('active');
    });

    // Montrer la section cliquée
    let sectionId = this.getAttribute('data-section');
    if (sectionId) {
      document.getElementById(sectionId).classList.remove('d-none');
      this.classList.add('active');}
  });
});