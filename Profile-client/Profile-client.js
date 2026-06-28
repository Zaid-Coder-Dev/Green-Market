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
        }
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
            // Mémoriser pour retrouver après un POST
            sessionStorage.setItem('clientSection', sectionId);
        }
    });
});

// Rouvrir la bonne section après un POST redirect
var urlParams = new URLSearchParams(window.location.search);
var ok = urlParams.get('ok');
if (ok == 'profil' || ok == 'mdp') {
    document.querySelectorAll('.section').forEach(function(s) { s.classList.add('d-none'); });
    document.getElementById('profil').classList.remove('d-none');
    document.querySelectorAll('.sidebar-link').forEach(function(l) { l.classList.remove('active'); });
    document.querySelector('.sidebar-link[data-section="profil"]').classList.add('active');
} else if (ok == 'avis_modifie' || ok == 'avis_supprime') {
    document.querySelectorAll('.section').forEach(function(s) { s.classList.add('d-none'); });
    document.getElementById('avis').classList.remove('d-none');
    document.querySelectorAll('.sidebar-link').forEach(function(l) { l.classList.remove('active'); });
    document.querySelector('.sidebar-link[data-section="avis"]').classList.add('active');
} else if (ok == 'reclam') {
    document.querySelectorAll('.section').forEach(function(s) { s.classList.add('d-none'); });
    document.getElementById('commandes').classList.remove('d-none');
    document.querySelectorAll('.sidebar-link').forEach(function(l) { l.classList.remove('active'); });
    document.querySelector('.sidebar-link[data-section="commandes"]').classList.add('active');
}

// --- TOGGLE PROFIL MODIFIER / ANNULER ---
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

// Animation clic sur bouton panier (favoris)
document.querySelectorAll('.btn-add').forEach(function(btn) {
    btn.addEventListener('click', function() {
        btn.classList.add('btn-clicked');
        setTimeout(function() {
            btn.classList.remove('btn-clicked');
        }, 200);
    });
});

// --- ÉTOILES (modal modifier) ---
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

// --- MODAL MODIFIER AVIS ---
document.querySelectorAll('.btn-modifier-avis').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id          = this.getAttribute('data-id');
        var note        = parseInt(this.getAttribute('data-note'));
        var commentaire = this.getAttribute('data-commentaire');
        var produit     = this.getAttribute('data-produit');

        document.getElementById('input-id-avis').value          = id;
        document.getElementById('input-commentaire-new').value  = commentaire;
        document.getElementById('modal-avis-produit').textContent = produit;
        mettreAJourEtoiles(note);

        new bootstrap.Modal(document.getElementById('modalModifierAvis')).show();
    });
});

// --- MODAL SUPPRIMER AVIS ---
document.querySelectorAll('.btn-supprimer-avis').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id      = this.getAttribute('data-id');
        var produit = this.getAttribute('data-produit');

        document.getElementById('input-suppr-id-avis').value      = id;
        document.getElementById('modal-suppr-produit').textContent = produit;

        new bootstrap.Modal(document.getElementById('modalSupprimerAvis')).show();
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