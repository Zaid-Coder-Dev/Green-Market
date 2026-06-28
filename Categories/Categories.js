// ===== NAVBAR : OUVERTURE DU MENU (cluster d'icônes) =====
const navToggleBtn = document.getElementById("navToggleBtn");
const navExpand = document.getElementById("navExpand");
const masterDot = document.getElementById("masterDot");

if (navToggleBtn) {
    navToggleBtn.addEventListener("click", function(e) {
        e.stopPropagation();
        navExpand.classList.toggle("open");
        const isOpen = navExpand.classList.contains("open");
        navToggleBtn.setAttribute("aria-expanded", isOpen);
        if (masterDot) {
            masterDot.style.display = isOpen ? "none" : "block";
        }
    });

    document.addEventListener("click", function(e) {
        if (!navExpand.contains(e.target) && !navToggleBtn.contains(e.target)) {
            navExpand.classList.remove("open");
            navToggleBtn.setAttribute("aria-expanded", false);
            if (masterDot) {
                masterDot.style.display = "block";
            }
        }
    });
}

// ===== NAVBAR : TOGGLE LANGUE FR/EN =====
const langToggle = document.getElementById("langToggle");

if (langToggle) {
    langToggle.addEventListener("click", function() {
        if (langToggle.innerText == "FR") {
            langToggle.innerText = "EN";
            document.cookie = "lang=EN; path=/; max-age=2592000";
        } else {
            langToggle.innerText = "FR";
            document.cookie = "lang=FR; path=/; max-age=2592000";
        }
    });
}

// ===== ANIMATION D'APPARITION DES CARTES =====
const cartes = document.querySelectorAll('.categ-card');
cartes.forEach(function(carte, index) {
    carte.style.opacity = '0';
    carte.style.transform = 'translateY(16px)';
    carte.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
    setTimeout(function() {
        carte.style.opacity = '1';
        carte.style.transform = 'translateY(0)';
    }, index * 60);
});