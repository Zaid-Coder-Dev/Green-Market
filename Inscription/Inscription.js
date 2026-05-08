const page = document.getElementById('page');
const switchBtn = document.getElementById('switchBtn');
const connexionForm = document.getElementById('connexionForm');
const inscriptionForm = document.getElementById('inscriptionForm');
const leftTitle = document.getElementById('left-title');
const leftText = document.getElementById('left-text');

// État actuel
let isConnexion = true;

switchBtn.addEventListener('click', function() {

  if (isConnexion) {
    // Passer à l'inscription
    page.classList.add('reversed');
    connexionForm.classList.add('d-none');
    inscriptionForm.classList.remove('d-none');
    leftTitle.textContent = 'Déjà un compte?';
    leftText.textContent = 'Connectez-vous pour accéder à votre espace.';
    switchBtn.textContent = 'Se connecter';
    isConnexion = false;

  } else {
    // Revenir à la connexion
    page.classList.remove('reversed');
    inscriptionForm.classList.add('d-none');
    connexionForm.classList.remove('d-none');
    leftTitle.textContent = 'Bienvenue!';
    leftText.textContent = 'Connectez-vous pour accéder à votre compte.';
    switchBtn.textContent = "S'inscrire";
    isConnexion = true;
  }

});