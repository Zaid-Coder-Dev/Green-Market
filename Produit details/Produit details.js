// ===== BOUTON WISHLIST (COEUR) =====
const heartBtn = document.querySelector(".wishlist-btn");
if (heartBtn) {
    heartBtn.addEventListener("click", function() {
        const icon = heartBtn.querySelector("i");
        icon.classList.toggle("bi-heart");
        icon.classList.toggle("bi-heart-fill");
    });
}

// ===== QUANTITÉ =====
let quantity = 1;
const quantityText = document.getElementById("quantity");
const plusBtn = document.getElementById("plusBtn");
const minusBtn = document.getElementById("minusBtn");

if (plusBtn) {
    plusBtn.addEventListener("click", function() {
        quantity++;
        quantityText.innerText = quantity;
    });
}

if (minusBtn) {
    minusBtn.addEventListener("click", function() {
        if (quantity > 1) {
            quantity--;
            quantityText.innerText = quantity;
        }
    });
}

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

// ===== PANIER =====
document.querySelectorAll('.btn-add').forEach(function(btn) {
    btn.addEventListener('click', function() {
        let badge = document.getElementById('cart-count');
        let count = parseInt(badge.textContent) || 0;
        badge.textContent = count + 1;

        // Animation clic
        btn.classList.add('btn-clicked');
        setTimeout(function() {
            btn.classList.remove('btn-clicked');
        }, 200);
    });
});

// ===== STAR RATING (formulaire avis) =====
const stars = document.querySelectorAll("#starRating i");
const ratingInput = document.getElementById("commentRating");

stars.forEach(function(star) {
    star.addEventListener("click", function() {
        const value = this.dataset.value;
        ratingInput.value = value;

        stars.forEach(function(s) {
            if (s.dataset.value <= value) {
                s.classList.add("bi-star-fill", "text-warning");
                s.classList.remove("bi-star");
            } else {
                s.classList.add("bi-star");
                s.classList.remove("bi-star-fill", "text-warning");
            }
        });
    });
});
