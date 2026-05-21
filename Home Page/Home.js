// ===== PANIER =====
let cartCount = 0;
const cartBadge = document.querySelector('.cart-badge');

document.querySelectorAll('.btn-cart').forEach(btn => {
  btn.addEventListener('click', () => {
    cartCount++;
    if (cartBadge) cartBadge.textContent = cartCount;
  });
});

// ===== DEFILEMENT FLUIDE (SMOOTH SCROLL) =====
document.querySelectorAll('a[href^="#"]').forEach(link => {
  link.addEventListener('click', function(e) {
    const targetId = this.getAttribute('href');
    if (targetId === '#') return; // FIX: Prevents crash on empty anchor links

    const target = document.querySelector(targetId);
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

// ===== ANIMATIONS AU SCROLL =====
const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry, index) => {
    if (entry.isIntersecting) {
      setTimeout(() => entry.target.classList.add('visible'), index * 120);
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.15 });

document.querySelectorAll('.reveal').forEach(item => observer.observe(item));