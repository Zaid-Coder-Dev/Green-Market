// ===== NAVIGATION SECTIONS =====
function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(function(sec) {
        sec.classList.add('d-none');
    });
    var section = document.getElementById(sectionId);
    if (section) { section.classList.remove('d-none'); }

    document.querySelectorAll('.sidebar-link').forEach(function(l) {
        l.classList.remove('active');
    });
    var activeLink = document.querySelector('.sidebar-link[data-section="' + sectionId + '"]');
    if (activeLink) { activeLink.classList.add('active'); }

    // Mémoriser la section active pour la retrouver après un POST
    sessionStorage.setItem('adminSection', sectionId);
}

document.querySelectorAll('[data-section]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        var sectionId = this.getAttribute('data-section');
        if (sectionId) { showSection(sectionId); }
    });
});

// Rouvrir la section mémorisée après un rechargement (POST, filtre boutique avis, etc.)
var urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('boutique_id')) {
    showSection('avis');
} else if (urlParams.get('ok') == 'profil' || urlParams.get('ok') == 'mdp') {
    showSection('profil');
}

// ===== FILTRE RÔLE UTILISATEURS =====
document.querySelectorAll('.notif-tabs button[data-role]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        // Activer le bouton cliqué
        document.querySelectorAll('.notif-tabs button[data-role]').forEach(function(b) {
            b.classList.remove('active');
        });
        this.classList.add('active');

        var role = this.getAttribute('data-role');

        // Afficher/cacher les lignes selon le rôle
        document.querySelectorAll('tr[data-role]').forEach(function(row) {
            if (role == 'tous' || row.getAttribute('data-role') == role) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// ===== MODAL MODIFIER CATÉGORIE =====
document.querySelectorAll('.btn-modifier-categorie').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('input-id-categ').value   = this.getAttribute('data-id');
        document.getElementById('input-nom-categ').value  = this.getAttribute('data-nom');
        document.getElementById('input-desc-categ').value = this.getAttribute('data-desc') || '';
        new bootstrap.Modal(document.getElementById('modalModifierCategorie')).show();
    });
});

// ===== MODIFIER RÉPONSE (afficher/cacher le champ édition) =====
document.querySelectorAll('.btn-edit-reponse').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        document.getElementById('reponse-texte-' + id).classList.toggle('d-none');
        document.getElementById('reponse-edit-' + id).classList.toggle('d-none');
    });
});

document.querySelectorAll('.btn-annuler-reponse').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        document.getElementById('reponse-texte-' + id).classList.remove('d-none');
        document.getElementById('reponse-edit-' + id).classList.add('d-none');
    });
});
var btnModifier   = document.getElementById('btnModifierProfil');
var btnAnnuler    = document.getElementById('btnAnnulerProfil');
var profilDisplay = document.getElementById('profilDisplay');
var profilEdit    = document.getElementById('profilEdit');

if (btnModifier) {
    btnModifier.addEventListener('click', function() {
        profilDisplay.classList.add('d-none');
        profilEdit.classList.remove('d-none');
        btnModifier.classList.add('d-none');
    });
}

if (btnAnnuler) {
    btnAnnuler.addEventListener('click', function() {
        profilEdit.classList.add('d-none');
        profilDisplay.classList.remove('d-none');
        btnModifier.classList.remove('d-none');
    });
}
// ===== MODAL MODIFIER STOCK =====
document.querySelectorAll('.btn-modifier-stock').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('input-stock-id-prod').value   = this.getAttribute('data-id');
        document.getElementById('input-new-stock').value       = this.getAttribute('data-stock');
        document.getElementById('modal-stock-nom').textContent = this.getAttribute('data-nom');
        new bootstrap.Modal(document.getElementById('modalModifierStock')).show();
    });
});
