// --- MENU NAVBAR (cart/profil/langue) ---
var navToggleBtn = document.getElementById('navToggleBtn');
var navExpand = document.getElementById('navExpand');

if (navToggleBtn) {
    navToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        navExpand.classList.toggle('open');
        var estOuvert = navExpand.classList.contains('open');
        navToggleBtn.setAttribute('aria-expanded', estOuvert);
        var masterDot = document.getElementById('masterDot');
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

// ===== QUANTITÉ =====
var quantity = 1;
var quantityText = document.getElementById('quantity');
var plusBtn = document.getElementById('plusBtn');
var minusBtn = document.getElementById('minusBtn');
var inputQuantite = document.getElementById('inputQuantite');

if (plusBtn) {
    plusBtn.addEventListener('click', function() {
        quantity++;
        quantityText.innerText = quantity;
        if (inputQuantite) {
            inputQuantite.value = quantity;
        }
    });
}

if (minusBtn) {
    minusBtn.addEventListener('click', function() {
        if (quantity > 1) {
            quantity--;
            quantityText.innerText = quantity;
            if (inputQuantite) {
                inputQuantite.value = quantity;
            }
        }
    });
}

// ===== CHANGEMENT D'IMAGE =====
var mainImage = document.getElementById('mainImage');

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('small-img')) {
        mainImage.src = e.target.src;
        document.querySelectorAll('.small-img').forEach(function(item) {
            item.classList.remove('active-img');
        });
        e.target.classList.add('active-img');
    }
});

// ===== STAR RATING (formulaire avis) =====
var stars = document.querySelectorAll('#starRating i');
var ratingInput = document.getElementById('commentRating');

stars.forEach(function(star) {
    star.addEventListener('click', function() {
        var value = this.dataset.value;
        ratingInput.value = value;

        stars.forEach(function(s) {
            if (s.dataset.value <= value) {
                s.classList.add('bi-star-fill', 'text-warning');
                s.classList.remove('bi-star');
            } else {
                s.classList.add('bi-star');
                s.classList.remove('bi-star-fill', 'text-warning');
            }
        });
    });
});