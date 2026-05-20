// ===== DONNÉES PRODUITS =====
const products = [
  {id: 1,
    name: "Savon Beldi",
    categorie: "Cosmétiques",
    price: "45 MAD",
    star: 4,
    images: [
      "Produit details images/Savon_beldi/SB_akarFasi.jpeg",
      "Produit details images/Savon_beldi/SB_flowers.jpeg",
      "Produit details images/Savon_beldi/SB_herbs.jpeg",
      "Produit details images/Savon_beldi/SB_nila.jpeg",
      "Produit details images/Savon_beldi/SB_souffre.jpeg",
      "Produit details images/Savon_beldi/Savon beldi.png"
    ],
    cara: [
      "Recette traditionnelle marocaine",
      "À base d'huile d'olive pure",
      "Texture pâteuse pour hammam",
      "Nettoyage doux et exfoliation naturelle"
    ],
    options: [{label:"Size", values: ["Small","Medium","Large"]}, {label: "Mix with", values: ["BLUE NILLA","Akar FASI","Herbs","Soufre","Fleur","Pure"]}],
    des: "Savon Beldi traditionnel marocain, enrichi à l'huile d'olive naturelle. Il nettoie la peau en profondeur, élimine les cellules mortes et laisse la peau douce, lisse et éclatante. Idéal pour le hammam et les soins du corps.",
    image: "Produit details images/Savon_beldi/Savon beldi.png"},
  {id: 2,
    name: "Huile d'Argan",
    categorie: "Cosmétiques",
    price: "120 MAD",
    star: 5,
    images: [
      "Produit details images/Argan_oil/AO_cosmetics.jpg"
    ],
    cara: [
      "Pressée à froid à partir d'amandons d'argan",
      "100% pure et biologique",
      "Hydratation intense pour peau et cheveux",
      "Riche en vitamine E et acides gras essentiels"
    ],
    options: [{label:"Size", values: ["Small","Medium","Large"]}],
    des: "Huile d'argan pure du Maroc, riche en vitamine E et en antioxydants. Elle hydrate intensément la peau et les cheveux, aide à réparer les pointes abîmées et lutte contre le dessèchement et le vieillissement cutané",
    image: "Produit details images/Argan_oil/Huile d'Argan Bio.png"},
  {id: 3,
    name: "Tapis Azilal Indigo",
    categorie: "Artisanat",
    price: "2800 MAD",
    images: [
      "Produit details images/Tapis AI/TAI_ALL.jpg",
      "Produit details images/Tapis AI/TAI_Details.jpg"
    ],
    cara: [
      "Laine naturelle de haute qualité",
      "Motifs berbères traditionnels",
      "Teinture indigo artisanale",
      "Pièce unique faite à la main"
    ],
    options: [{label:"Size", values: ["Small","Medium","Large"]}, {label: "COLOR", values: ["Rouge","Beige","Brown","Blue"]}],
    des: "Tapis berbère artisanal fabriqué à la main dans la région d'Azilal. Il se distingue par ses motifs uniques et ses couleurs vibrantes avec une touche d'indigo. Parfait pour apporter une touche artistique et authentique à votre intérieur.",
    star: 3.5,
    image: "Produit details images/Tapis AI/Tapis Azilal Indigo.png"},
  {id: 4,
    name: "Huile d'Olive Vierge",
    categorie: "Artisanat",
    price: "80 MAD",
    options: [{label:"Size", values: ["1L","1/2 L"]}],
    des: "Huile d'olive vierge extra 100% naturelle, obtenue par pression traditionnelle. Elle est reconnue pour ses bienfaits pour la santé, la cuisine et les soins cosmétiques grâce à ses propriétés nourrissantes et protectrices.",
    star: 4.5,
    images: [
      "Produit details images/Olive_oil/OH_bottle.jpeg"
    ],
    cara: [
      "Pressée à froid",
      "100% olives locales",
      "Saveur fruitée et équilibrée",
      "Riche en antioxydants et vitamines"
    ],
    image: "Produit details images/Olive_oil/Huile d'Olive Vierge.png"},
  {id: 5,
    name: "Tagine Terracotta",
    categorie: "Artisanat",
    price: "280 MAD",
    star: 4,
    images: [
      "Produit details images/Tagine Terracotta/TT-Inside.jpg",
      "Produit details images/Tagine Terracotta/TT_Details.jpg",
      "Produit details images/Tagine Terracotta/TT-Size.jpg"
    ],
    cara: [
      "Terre cuite naturelle",
      "Fabrication artisanale à la main",
      "Résistant à la chaleur et durable",
      "Idéal pour cuisson lente et saveurs authentiques"
    ],
    options: [{label:"Size", values: ["Small","Medium","Large"]}, {label: "COLOR", values: ["Rouge","Vert","Blue"]}],
    des: "Tagine marocain traditionnel en terre cuite (terracotta), idéal pour une cuisson lente et savoureuse. Il permet de préserver toutes les saveurs et l'authenticité des plats marocains comme le poulet ou l'agneau au tajine.",
    image: "Produit details images/Tagine Terracotta/Tagine Terracotta.png"},
  {id: 6,
    name: "Kaftan Bleu Indigo",
    categorie: "Mode Traditionnelle",
    price: "850 MAD",
    star: 5,
    images: [
      "Produit details images/kaftan/KBI_back.jpeg",
      "Produit details images/kaftan/KBI1.jpeg",
      "Produit details images/kaftan/KBI_style.jpeg",
      "Produit details images/kaftan/KBI.jpeg",
    ],
    cara: [
      "Tissu 100% coton léger",
      "Broderies artisanales indigo",
      "Coupe traditionnelle marocaine",
      "Confort et élégance pour occasions spéciales"
    ],
    options: [{label:"Size", values: ["Small","Medium","Large"]}, {label: "COLOR", values: ["Rouge","beige","Blue"]}],
    des: "Kaftan marocain élégant de couleur bleu indigo, alliant tradition et modernité. Parfait pour les occasions spéciales et les fêtes, il se distingue par ses finitions artisanales et son style raffiné.",
    image: "Produit details images/kaftan/Kaftan Bleu Indigo.png"}
];

// ===== CHARGEMENT DU PRODUIT DEPUIS L'URL =====
const info = new URLSearchParams(window.location.search);
const idp = Number(info.get("id"));
const product = products.find(p => p.id == idp);
const smallImg = document.getElementById("SMI");
const optionP = document.getElementById("optionP");
optionP.innerHTML = "";

console.log("ID:", idp);
console.log("PRODUCT:", product);

if (product) {

  // ===== GÉNÉRATION DES ÉTOILES =====
  let starsHTML = "";
  for (let i = 1; i <= 5; i++) {
    if (i <= Math.floor(product.star)) {
      starsHTML += '<i class="bi bi-star-fill text-warning"></i>';
    } else if (i - product.star < 1) {
      starsHTML += '<i class="bi bi-star-half text-warning"></i>';
    } else {
      starsHTML += '<i class="bi bi-star text-warning"></i>';
    }
  }

  // ===== PETITES IMAGES =====
  smallImg.innerHTML = "";
  product.images.forEach(function(img, index) {
    smallImg.innerHTML += `
      <img 
        class="small-img ${index === 0 ? "active-img" : ""}"
        src="${img}"
      >
    `;
  });

  // ===== OPTIONS (SELECT) =====
  product.options.forEach(function(op) {
    let valuesHTML = "";
    op.values.forEach(function(value) {
      valuesHTML += `<option>${value}</option>`;
    });
    optionP.innerHTML += `
      <div class="mb-4">
        <label class="form-label fw-bold">${op.label}</label>
        <select class="form-select custom-select">
          ${valuesHTML}
        </select>
      </div>
    `;
  });

  // ===== NOTE GLOBALE =====
  const globalRating = document.getElementById("global-rating");
  const globalStars = document.getElementById("global-stars");
  globalRating.textContent = product.star.toFixed(1);
  let globalStarsHTML = "";
  for (let i = 1; i <= 5; i++) {
    if (i <= Math.floor(product.star)) {
      globalStarsHTML += '<i class="bi bi-star-fill text-warning"></i>';
    } else if (i - product.star < 1) {
      globalStarsHTML += '<i class="bi bi-star-half text-warning"></i>';
    } else {
      globalStarsHTML += '<i class="bi bi-star text-warning"></i>';
    }
  }
  globalStars.innerHTML = globalStarsHTML;

  // ===== REMPLISSAGE DES CHAMPS =====
  document.getElementById("ctg").innerHTML = product.categorie;
  document.getElementById("pro-tit").innerHTML = product.name;
  document.getElementById("product-stars").innerHTML = starsHTML;
  document.getElementById("prix-p").innerHTML = product.price;
  document.getElementById("des-p").innerHTML = product.des;
  document.getElementById("mainImage").src = product.image;
  document.getElementById("des-tab").innerHTML = product.des;
  const caraList = document.getElementById("cara-list");
  caraList.innerHTML = product.cara.map(function(item) {
    return `<li>${item}</li>`;
  }).join("");
}

// ===== BOUTON WISHLIST (COEUR) =====
const heartBtn = document.querySelector(".wishlist-btn");
heartBtn.addEventListener("click", function() {
  const icon = heartBtn.querySelector("i");
  icon.classList.toggle("bi-heart");
  icon.classList.toggle("bi-heart-fill");
});

// ===== QUANTITÉ =====
let quantity = 1;
const quantityText = document.getElementById("quantity");
const plusBtn = document.getElementById("plusBtn");
const minusBtn = document.getElementById("minusBtn");

plusBtn.addEventListener("click", function() {
  quantity++;
  quantityText.innerText = quantity;
});

minusBtn.addEventListener("click", function() {
  if (quantity > 1) {
    quantity--;
    quantityText.innerText = quantity;
  }
});

// ===== CHANGEMENT D'IMAGE =====
const mainImage = document.getElementById("mainImage");

document.addEventListener("click", function(e) {
  if (e.target.classList.contains("small-img")) {
    mainImage.src = e.target.src;
    document.querySelectorAll(".small-img").forEach(function(item) {
      item.classList.remove("active-img");
    });
    e.target.classList.add("active-img");
  }
});

// ===== COMPTEUR PANIER =====
document.querySelectorAll('.cart-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    let badge = document.getElementById('cart-count');
    let count = parseInt(badge.textContent) || 0;
    badge.textContent = count + 1;
  });
});

// ===== STAR RATING =====
const stars = document.querySelectorAll("#starRating i");
const ratingInput = document.getElementById("commentRating");

stars.forEach(star => {
  star.addEventListener("click", function () {
    const value = this.dataset.value;
    ratingInput.value = value;

    stars.forEach(s => {
      if (s.dataset.value <= value) {
        s.classList.add("bi-star-fill");
        s.classList.remove("bi-star");
        s.classList.add("text-warning");
      } else {
        s.classList.add("bi-star");
        s.classList.remove("bi-star-fill");
        s.classList.remove("text-warning");
      }
    });
  });
});