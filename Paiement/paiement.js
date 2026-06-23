// ══════════════════════════════════════════
// main.js — Paiement — Green Market
// ══════════════════════════════════════════

document.addEventListener("DOMContentLoaded", function () {

  // ── Choix du mode de paiement (carte / livraison) ──
  var methodButtons = document.querySelectorAll(".method");
  var cardForm = document.getElementById("card-form");
  var codForm = document.getElementById("cod-form");

  methodButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      // on retire la classe active de tous les boutons
      methodButtons.forEach(function (b) {
        b.classList.remove("active");
      });
      // puis on l'ajoute seulement au bouton cliqué
      btn.classList.add("active");

      var methode = btn.getAttribute("data-method");

      if (methode == "card") {
        cardForm.style.display = "block";
        codForm.style.display = "none";
      } else {
        cardForm.style.display = "none";
        codForm.style.display = "block";
      }
    });
  });

  // ── Choix de l'adresse de facturation (même / différente) ──
  var billingButtons = document.querySelectorAll(".radio-row");
  var billingExtra = document.getElementById("billing-extra");

  billingButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      billingButtons.forEach(function (b) {
        b.classList.remove("active");
      });
      btn.classList.add("active");

      var choix = btn.getAttribute("data-billing");

      if (choix == "different") {
        billingExtra.classList.add("show");
      } else {
        billingExtra.classList.remove("show");
      }
    });
  });

  // ── Formatage du numéro de carte (groupes de 4 chiffres) ──
  var ccInput = document.getElementById("cc");

  ccInput.addEventListener("input", function () {
    var chiffres = ccInput.value.replace(/\D/g, "").substring(0, 16);
    var groupes = [];

    for (var i = 0; i < chiffres.length; i += 4) {
      groupes.push(chiffres.substring(i, i + 4));
    }

    ccInput.value = groupes.join(" ");
  });

  // ── Formatage de la date d'expiration (MM / AA) ──
  var expInput = document.getElementById("exp");

  expInput.addEventListener("input", function () {
    var chiffres = expInput.value.replace(/\D/g, "").substring(0, 4);

    if (chiffres.length > 2) {
      expInput.value = chiffres.substring(0, 2) + " / " + chiffres.substring(2);
    } else {
      expInput.value = chiffres;
    }
  });

  // ── CVV : chiffres uniquement ──
  var cvvInput = document.getElementById("cvv");

  cvvInput.addEventListener("input", function () {
    cvvInput.value = cvvInput.value.replace(/\D/g, "");
  });

  // ── Bouton de confirmation du paiement ──
  var payBtn = document.getElementById("pay-btn");

  payBtn.addEventListener("click", function () {
    // à brancher plus tard sur le traitement PHP du paiement
    alert("Paiement confirmé (démo front-end).");
  });

});
