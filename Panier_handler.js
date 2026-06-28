// --- PANIER AJAX (partagé sur toutes les pages) ---
document.querySelectorAll('.btn-add').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var self = this;
        var idProd = this.getAttribute('data-id');

        // Désactiver le bouton pendant la requête
        self.disabled = true;
        self.innerHTML = '<i class="bi bi-hourglass-split"></i>';

        fetch('../ajouter_panier.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'id_prod=' + encodeURIComponent(idProd) + '&quantite=1'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                // Bouton vert avec coche
                self.innerHTML = '<i class="bi bi-check-lg"></i>';
                self.style.backgroundColor = '#2d4a2d';

                // Mettre à jour le badge panier dans la navbar
                var badge = document.getElementById('cart-count');
                if (badge) {
                    badge.textContent = data.nb_panier;
                }
            } else {
                // Erreur
                self.innerHTML = '<i class="bi bi-exclamation-triangle"></i>';
                self.style.backgroundColor = '#c62828';
            }

            // Remettre le bouton à la normale après 1.8s
            setTimeout(function() {
                self.innerHTML = '<i class="bi bi-cart"></i>';
                self.style.backgroundColor = '';
                self.disabled = false;
            }, 1800);
        })
        .catch(function() {
            // Erreur réseau — remettre à la normale
            self.innerHTML = '<i class="bi bi-cart"></i>';
            self.style.backgroundColor = '';
            self.disabled = false;
        });
    });
});
