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

// --- TOGGLE AFFICHAGE / ÉDITION PROFIL ---

function ouvrirEditionProfil() {
    document.getElementById('profil-display').classList.add('d-none');
    document.getElementById('profil-form').classList.remove('d-none');
    document.getElementById('btn-edit-profil').classList.add('d-none');
}

function fermerEditionProfil() {
    document.getElementById('profil-display').classList.remove('d-none');
    document.getElementById('profil-form').classList.add('d-none');
    document.getElementById('btn-edit-profil').classList.remove('d-none');
}

var btnEdit = document.getElementById('btn-edit-profil');
if (btnEdit) {
    // Ouvrir automatiquement si erreurs de validation
    if (btnEdit.getAttribute('data-errors') == '1') {
        ouvrirEditionProfil();
    }
    btnEdit.addEventListener('click', ouvrirEditionProfil);
}

var btnCancel = document.getElementById('btn-cancel-profil');
if (btnCancel) {
    btnCancel.addEventListener('click', fermerEditionProfil);
}

// --- MODAL MODIFIER AVIS ---

// Sélecteur d'étoiles dans le modal
var noteSelectionnee = 0;

function mettreAJourEtoiles(valeur) {
    noteSelectionnee = valeur;
    document.getElementById('input-note-new').value = valeur;
    document.querySelectorAll('.star-btn').forEach(function(star) {
        var v = parseInt(star.getAttribute('data-val'));
        if (v <= valeur) {
            star.classList.remove('bi-star');
            star.classList.add('bi-star-fill');
        } else {
            star.classList.remove('bi-star-fill');
            star.classList.add('bi-star');
        }
    });
}

document.querySelectorAll('.star-btn').forEach(function(star) {
    star.addEventListener('click', function() {
        mettreAJourEtoiles(parseInt(this.getAttribute('data-val')));
    });
    star.addEventListener('mouseover', function() {
        var val = parseInt(this.getAttribute('data-val'));
        document.querySelectorAll('.star-btn').forEach(function(s) {
            var v = parseInt(s.getAttribute('data-val'));
            if (v <= val) {
                s.classList.remove('bi-star');
                s.classList.add('bi-star-fill');
            } else {
                s.classList.remove('bi-star-fill');
                s.classList.add('bi-star');
            }
        });
    });
    star.addEventListener('mouseout', function() {
        mettreAJourEtoiles(noteSelectionnee);
    });
});

// Ouvrir le modal modifier avec les données de l'avis cliqué
document.querySelectorAll('.btn-modifier-avis').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id        = this.getAttribute('data-id');
        var note      = parseInt(this.getAttribute('data-note'));
        var commentaire = this.getAttribute('data-commentaire');
        var produit   = this.getAttribute('data-produit');

        document.getElementById('input-id-avis').value          = id;
        document.getElementById('input-commentaire-new').value  = commentaire;
        document.getElementById('modal-avis-produit').textContent = produit;

        mettreAJourEtoiles(note);

        var modal = new bootstrap.Modal(document.getElementById('modalModifierAvis'));
        modal.show();
    });
});

// --- MODAL SUPPRIMER AVIS ---
document.querySelectorAll('.btn-supprimer-avis').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id      = this.getAttribute('data-id');
        var produit = this.getAttribute('data-produit');

        document.getElementById('input-suppr-id-avis').value      = id;
        document.getElementById('modal-suppr-produit').textContent = produit;

        var modal = new bootstrap.Modal(document.getElementById('modalSupprimerAvis'));
        modal.show();
    });
});

// --- MODAL RÉCLAMATION ---
document.querySelectorAll('.btn-reclam').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var idCom = this.getAttribute('data-id');
        var num   = this.getAttribute('data-num');

        document.getElementById('input-reclam-id-com').value   = idCom;
        document.getElementById('modal-reclam-num').textContent = num;

        var modal = new bootstrap.Modal(document.getElementById('modalReclam'));
        modal.show();
    });
});