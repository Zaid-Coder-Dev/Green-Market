// Changer de section quand on clique un lien sidebar
document.querySelectorAll('.sidebar-link').forEach(function(btn) {
    btn.addEventListener('click', function() {

        // Cacher toutes les sections
        document.querySelectorAll('.section').forEach(function(section) {
            section.classList.add('d-none');
        });

        // Enlever active de tous les liens
        document.querySelectorAll('.sidebar-link').forEach(function(l) {
            l.classList.remove('active');
        });

        // Montrer la section cliquée
        var sectionId = this.getAttribute('data-section');
        if (sectionId) {
            document.getElementById(sectionId).classList.remove('d-none');
            this.classList.add('active');
        }
    });
});

// Animation clic sur bouton panier (favoris)
document.querySelectorAll('.btn-add').forEach(function(btn) {
    btn.addEventListener('click', function() {
        btn.classList.add('btn-clicked');
        setTimeout(function() {
            btn.classList.remove('btn-clicked');
        }, 200);
    });
});