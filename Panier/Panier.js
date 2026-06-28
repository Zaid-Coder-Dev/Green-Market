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