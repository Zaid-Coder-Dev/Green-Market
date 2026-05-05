// ===== COMPTEUR DU PANIER =====


let cartCount = 0;
    document.querySelectorAll('.btn-add').forEach(function(btn) {
    btn.addEventListener('click', function() {
    cartCount++;
    document.querySelector('.cart-badge').textContent = cartCount;});
});


// ===== NEWSLETTER =====


document.querySelector('.btn-subscribe').addEventListener('click', function() {
  
  let email = document.querySelector('#newsletter-form input').value;
  
  if (email === '') {
    alert('Veuillez entrer votre email!');
    return; }

  // cache le formulaire
  document.querySelector('#newsletter-form').classList.add('d-none');

  // affiche le message de succes
  document.querySelector('#newsletter-success').classList.remove('d-none'); } );