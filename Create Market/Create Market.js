// ===== NAVBAR TOGGLE =====
var navToggleBtn = document.getElementById('navToggleBtn');
var navExpand    = document.getElementById('navExpand');
var masterDot    = document.getElementById('masterDot');

if (navToggleBtn) {
    navToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        navExpand.classList.toggle('open');
        var estOuvert = navExpand.classList.contains('open');
        navToggleBtn.setAttribute('aria-expanded', estOuvert);
        if (masterDot) {
            masterDot.style.display = estOuvert ? 'none' : 'block';
        }
    });
    document.addEventListener('click', function(e) {
        if (!navExpand.contains(e.target) && !navToggleBtn.contains(e.target)) {
            navExpand.classList.remove('open');
            navToggleBtn.setAttribute('aria-expanded', 'false');
        }
    });
}

// ===== TOGGLE LANGUE FR/EN =====
var langToggle = document.getElementById('langToggle');
if (langToggle) {
    langToggle.addEventListener('click', function() {
        if (langToggle.textContent.trim() == 'FR') {
            langToggle.textContent = 'EN';
            document.cookie = 'lang=EN; path=/; max-age=2592000';
        } else {
            langToggle.textContent = 'FR';
            document.cookie = 'lang=FR; path=/; max-age=2592000';
        }
    });
}

// ===== LOGO UPLOAD (petit aperçu) =====
var uploadAreaLogo    = document.getElementById('uploadAreaLogo');
var logoInput         = document.getElementById('logoInput');
var browseBtnLogo     = document.getElementById('browseBtnLogo');
var previewLogo       = document.getElementById('previewLogo');
var uploadContentLogo = document.getElementById('uploadContentLogo');

if (uploadAreaLogo) {
    uploadAreaLogo.addEventListener('click', function() { logoInput.click(); });
    browseBtnLogo.addEventListener('click', function(e) { e.stopPropagation(); logoInput.click(); });

    logoInput.addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewLogo.src = e.target.result;
                previewLogo.style.display = 'block';
                uploadContentLogo.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
}

// ===== BANNIÈRE UPLOAD (grand aperçu) =====
var uploadAreaBanner    = document.getElementById('uploadAreaBanner');
var bannerInput         = document.getElementById('bannerInput');
var browseBtnBanner     = document.getElementById('browseBtnBanner');
var previewBanner       = document.getElementById('previewBanner');
var uploadContentBanner = document.getElementById('uploadContentBanner');

if (uploadAreaBanner) {
    uploadAreaBanner.addEventListener('click', function() { bannerInput.click(); });
    browseBtnBanner.addEventListener('click', function(e) { e.stopPropagation(); bannerInput.click(); });

    bannerInput.addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewBanner.src = e.target.result;
                previewBanner.style.display = 'block';
                uploadContentBanner.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
}
