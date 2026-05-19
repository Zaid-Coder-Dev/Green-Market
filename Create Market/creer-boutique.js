// ===== COMPTEUR PANIER =====
document.addEventListener('DOMContentLoaded', function () {
  var cartCount = localStorage.getItem('cartCount') || 0;
  var cartBadge = document.getElementById('cart-count');
  if (cartBadge) cartBadge.textContent = cartCount;

  var bellCount = localStorage.getItem('bellCount') || 0;
  var bellBadge = document.getElementById('bell-count');
  if (bellBadge) bellBadge.textContent = bellCount;
});

// ===== UPLOAD LOGO =====
const uploadArea = document.getElementById('uploadArea');
const logoInput = document.getElementById('logoInput');
const previewLogo = document.getElementById('previewLogo');
const browseBtn = document.getElementById('browseBtn');

// Cliquer sur la zone ou le bouton ouvre le sélecteur de fichier
uploadArea.addEventListener('click', function () {
  logoInput.click();
});

browseBtn.addEventListener('click', function (e) {
  e.stopPropagation();
  logoInput.click();
});

// Afficher l'aperçu du logo après sélection
logoInput.addEventListener('change', function () {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      previewLogo.src = e.target.result;
      previewLogo.style.display = 'block';
      document.getElementById('uploadContent').style.display = 'none';
    };
    reader.readAsDataURL(file);
  }
});

// ===== AJOUTER DES IMAGES (max 3) =====
const addImagesBtn = document.getElementById('addImagesBtn');
const imagesInput = document.getElementById('imagesInput');
const imagesGrid = document.getElementById('imagesGrid');
const previewImage = document.getElementById('previewImage');

addImagesBtn.addEventListener('click', function () {
  // Vérifier le nombre d'images déjà présentes
  if (document.querySelectorAll('.market-image').length >= 3) {
    alert('Maximum 3 images autorisées pour la boutique.');
    return;
  }
  imagesInput.click();
});

imagesInput.addEventListener('change', function () {
  const existing = document.querySelectorAll('.market-image').length;
  const remaining = 3 - existing;
  const files = Array.from(this.files).slice(0, remaining);

  if (files.length === 0) {
    alert('Maximum 3 images autorisées pour la boutique.');
    return;
  }

  files.forEach(function (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const div = document.createElement('div');
      div.classList.add('market-image');
      div.innerHTML = `
        <img src="${e.target.result}" alt="Image boutique">
        <div class="image-actions">
          <button class="img-action-btn edit-btn"><i class="bi bi-pencil"></i></button>
          <button class="img-action-btn delete-btn"><i class="bi bi-trash"></i></button>
        </div>
      `;
      imagesGrid.appendChild(div);

      // Supprimer
      div.querySelector('.delete-btn').addEventListener('click', function () {
        div.remove();
      });

      // Aperçu au clic
      div.querySelector('img').addEventListener('click', function () {
        previewImage.src = this.src;
        updateCurrentIndex();
      });
    };
    reader.readAsDataURL(file);
  });
});

// ===== SUPPRIMER UNE IMAGE =====
document.querySelectorAll('.delete-btn').forEach(function (btn) {
  btn.addEventListener('click', function () {
    this.closest('.market-image').remove();
  });
});

// ===== APERÇU AU CLIC SUR UNE IMAGE =====
document.querySelectorAll('.market-image img').forEach(function (img) {
  img.addEventListener('click', function () {
    previewImage.src = this.src;
    updateCurrentIndex();
  });
});

// ===== NAVIGATION APERÇU (FLÈCHES) =====
let current = 0;

function getImages() {
  return document.querySelectorAll('.market-image img');
}

function updateCurrentIndex() {
  const images = getImages();
  images.forEach(function (img, index) {
    if (img.src === previewImage.src) {
      current = index;
    }
  });
}

document.getElementById('nextBtn').addEventListener('click', function () {
  const images = getImages();
  current++;
  if (current >= images.length) current = 0;
  previewImage.src = images[current].src;
});

document.getElementById('prevBtn').addEventListener('click', function () {
  const images = getImages();
  current--;
  if (current < 0) current = images.length - 1;
  previewImage.src = images[current].src;
});

// ===== TOGGLE STATUT =====
const toggleStatus = document.getElementById('toggleStatus');
const statusText = document.getElementById('statusText');

toggleStatus.addEventListener('click', function () {
  toggleStatus.classList.toggle('active');
  if (toggleStatus.classList.contains('active')) {
    statusText.textContent = 'Active';
  } else {
    statusText.textContent = 'Non active';
  }
});

// ===== BOUTON CRÉER =====
document.getElementById('createBtn').addEventListener('click', function () {
  // Vérifier que le nom de la boutique est rempli
  const nomBoutique = document.querySelector('input[name="nom_boutique"]').value;
  if (nomBoutique.trim() === '') {
    alert('Veuillez entrer le nom de votre boutique.');
    return;
  }

  // Message de confirmation (sera remplacé par PHP plus tard)
  alert('Boutique créée avec succès ! Elle sera activée après validation par l\'administrateur.');
});