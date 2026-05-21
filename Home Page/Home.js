// ===== COMPTEUR DU PANIER =====


let cartCount = 0;
    document.querySelectorAll('.btn-add').forEach(function(btn) {
    btn.addEventListener('click', function() {
    cartCount++;
    document.querySelector('.cart-badge').textContent = cartCount;});
});


// ===== 1. SMOOTH SCROLL for anchor links (#products, #about) =====
document.querySelectorAll('a[href^="#"]').forEach(link => {
  link.addEventListener('click', function (e) {
    const targetId = this.getAttribute('href');
    if (targetId.length > 1) {
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
      }
    }
  });
});

// ===== 2. REVEAL ELEMENTS ON SCROLL =====
// Any element with class "reveal" fades up when it enters the screen.
const revealItems = document.querySelectorAll('.reveal');

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry, index) => {
    if (entry.isIntersecting) {
      // Small stagger so cards appear one after the other
      setTimeout(() => {
        entry.target.classList.add('visible');
      }, index * 120);
      observer.unobserve(entry.target); // animate only once
    }
  });
}, { threshold: 0.15 });

revealItems.forEach(item => observer.observe(item));
