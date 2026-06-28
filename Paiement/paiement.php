<?php
session_start();
require_once '../connexion.php';
require_once '../functions.php';

require_login();

// SUCCÈS APRÈS REDIRECTION (POST -> GET)
$succes = false;
$id_com_succes = 0;

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $succes = true;
    if (isset($_GET['id_com'])) {
        $id_com_succes = (int)$_GET['id_com'];
    }
}

// MODE ACHAT DIRECT (depuis "Acheter maintenant") OU PANIER
$mode_direct = false;
$id_prod_direct = 0;
$qte_direct = 1;

if (!$succes && isset($_GET['id_prod']) && isset($_GET['quantite'])) {
    $mode_direct = true;
    $id_prod_direct = (int)$_GET['id_prod'];
    $qte_direct = (int)$_GET['quantite'];
    if ($qte_direct < 1) {
        $qte_direct = 1;
    }
}

// CONSTRUCTION DES LIGNES DE COMMANDE
$lignes = [];
$sous_total = 0;
$livraison = 25;
$total = 0;
$user_addr = ['rue' => '', 'ville' => ''];
$adresse_actuelle = '';

if (!$succes) {
    if ($mode_direct) {
        try {
            $req1 = $pdo->prepare("SELECT * FROM Produit WHERE ID_Prod = ?");
            $req1->execute([$id_prod_direct]);
            $produit_direct = $req1->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }

        if (!$produit_direct) {
            die("Produit introuvable.");
        }

        if ($qte_direct > $produit_direct['Stock']) {
            $qte_direct = $produit_direct['Stock'];
        }

        $lignes[] = [
            'id_prod'  => $produit_direct['ID_Prod'],
            'nom'      => $produit_direct['nom_Prod'],
            'prix'     => $produit_direct['Prix'],
            'quantite' => $qte_direct,
            'img'      => $produit_direct['Prod_img']
        ];
    } else {
        try {
            $req2 = $pdo->prepare("
                SELECT lp.ID_Prod, lp.Quantite, p.nom_Prod, p.Prix, p.Stock, p.Prod_img
                FROM Ligne_panier lp
                JOIN Panier pa ON lp.ID_Panier = pa.ID_Panier
                JOIN Produit p ON lp.ID_Prod = p.ID_Prod
                WHERE pa.ID_utili = ?
            ");
            $req2->execute([$_SESSION['id_utili']]);
            $lignes_panier = $req2->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }

        foreach ($lignes_panier as $lp) {
            $lignes[] = [
                'id_prod'  => $lp['ID_Prod'],
                'nom'      => $lp['nom_Prod'],
                'prix'     => $lp['Prix'],
                'quantite' => $lp['Quantite'],
                'img'      => $lp['Prod_img']
            ];
        }
    }

    if (count($lignes) == 0) {
        die("Aucun produit à payer.");
    }

    foreach ($lignes as $l) {
        $sous_total += $l['prix'] * $l['quantite'];
    }

    if ($sous_total >= 500) {
        $livraison = 0;
    }

    $total = $sous_total + $livraison;

    // ADRESSE ENREGISTRÉE DU CLIENT (affichage + valeur par défaut)
    try {
        $req_user = $pdo->prepare("SELECT rue, ville FROM Utilisateur WHERE id_utili = ?");
        $req_user->execute([$_SESSION['id_utili']]);
        $user_addr = $req_user->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }

    $adresse_actuelle = $user_addr['rue'] . ', ' . $user_addr['ville'];
}

// TRAITEMENT DU PAIEMENT
if (!$succes && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmer_paiement'])) {
    $mode_pay = 'carte bancaire';
    if (isset($_POST['mode_pay']) && $_POST['mode_pay'] == 'cod') {
        $mode_pay = 'paiement à la livraison';
    }

    // ADRESSE DE LIVRAISON : adresse enregistrée par défaut, sauf si le client a choisi une adresse différente
    $rue_livraison = $user_addr['rue'];
    $ville_livraison = $user_addr['ville'];

    if (isset($_POST['billing']) && $_POST['billing'] == 'different') {
        if (!empty($_POST['b_rue'])) {
            $rue_livraison = $_POST['b_rue'];
        }
        if (!empty($_POST['b_ville'])) {
            $ville_livraison = $_POST['b_ville'];
        }
    }

    try {
        $req3 = $pdo->prepare("INSERT INTO Commande (status_com, prix_total, adresse_livraison, ville_livraison, ID_utili) VALUES ('en attente', ?, ?, ?, ?)");
        $req3->execute([$total, $rue_livraison, $ville_livraison, $_SESSION['id_utili']]);
        $id_com = $pdo->lastInsertId();
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }

    foreach ($lignes as $l) {
        try {
            $req_ligne = $pdo->prepare("INSERT INTO Ligne_commande (ID_Com, ID_Prod, Quantite, Prix_Unitaire) VALUES (?, ?, ?, ?)");
            $req_ligne->execute([$id_com, $l['id_prod'], $l['quantite'], $l['prix']]);

            $req_stock = $pdo->prepare("UPDATE Produit SET Stock = Stock - ? WHERE ID_Prod = ?");
            $req_stock->execute([$l['quantite'], $l['id_prod']]);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    if ($mode_pay == 'carte bancaire') {
        try {
            $req_pay = $pdo->prepare("INSERT INTO Paiement (mode_pay, montant, ID_Com) VALUES (?, ?, ?)");
            $req_pay->execute([$mode_pay, $total, $id_com]);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    if (!$mode_direct) {
        try {
            $req_vider = $pdo->prepare("DELETE lp FROM Ligne_panier lp JOIN Panier pa ON lp.ID_Panier = pa.ID_Panier WHERE pa.ID_utili = ?");
            $req_vider->execute([$_SESSION['id_utili']]);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    header('Location: Paiement.php?success=1&id_com=' . $id_com);
    exit();
}

// URL DU FORMULAIRE (garde id_prod/quantite en mode achat direct)
$action_form = 'Paiement.php';
if ($mode_direct) {
    $action_form = 'Paiement.php?id_prod=' . $id_prod_direct . '&quantite=' . $qte_direct;
}

// NOMBRE DE PRODUITS (texte sous "Votre commande")
$texte_nb_produits = count($lignes) . ' produit';
if (count($lignes) > 1) {
    $texte_nb_produits .= 's';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Paiement — Green Market</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lato:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link rel="stylesheet" href="paiement.css" />
</head>
<body>

<?php
// TOAST DE SUCCÈS
if ($succes) {
    echo '
<div class="toast-wrap">
    <div class="toast align-items-center toast-success" id="toastSucces" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle-fill me-2"></i>
                Paiement confirmé ! Commande #' . $id_com_succes . ' enregistrée.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>';
}
?>

<!-- HEADER (pas de navbar/footer sur Paiement) -->
<header class="checkout-header">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="../Home Page/Home.php" class="brand">
      <span class="brand-name">Green Market</span>
    </a>
    <nav class="step-trail">
      <a href="../Panier/Panier.php" class="step-trail-done">
        <i class="bi bi-check-circle-fill"></i> Panier
      </a>
      <i class="bi bi-chevron-right step-trail-arrow"></i>
      <span class="step-trail-current">Paiement</span>
    </nav>
    <div class="ssl-pill">
      <i class="bi bi-shield-lock-fill"></i>
      <span>Paiement sécurisé</span>
    </div>
  </div>
</header>

<?php
// MAIN — soit l'écran de succès, soit le formulaire de paiement
if ($succes) {
    echo <<<HTML
<main class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="gm-card text-center py-5">
                <i class="bi bi-check-circle-fill" style="font-size:48px;color:var(--green);"></i>
                <h1 class="mt-3">Merci pour votre commande</h1>
                <p class="lead-text">Votre commande #{$id_com_succes} a bien été enregistrée.</p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="../Home Page/Home.php" class="cta" style="display:inline-flex;width:auto;padding:13px 24px;">
                        <i class="bi bi-house"></i><span>Retour à l'accueil</span>
                    </a>
                    <a href="../Profile-client/Profile-client.php" class="back-link" style="margin-top:0;">
                        Voir mes commandes
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
HTML;
} else {

    // LIGNES DE PRODUITS DU RÉCAPITULATIF
    $items_html = '';
    foreach ($lignes as $l) {
        $sous_ligne = $l['prix'] * $l['quantite'];
        $img_path = !empty($l['img']) ? $l['img'] : '../uploads/produits_images/default.jpg';
        $items_html .= '
          <li class="item">
            <span class="thumb">
                <img src="' . $img_path . '" alt="' . $l['nom'] . '" class="thumb-img">
                <span class="qty">' . $l['quantite'] . '</span>
            </span>
            <span class="item-info">
              <span class="item-name">' . $l['nom'] . '</span>
              <span class="item-sub">Qté ' . $l['quantite'] . ' · ' . number_format($l['prix'], 2) . ' MAD</span>
            </span>
            <span class="item-price">' . number_format($sous_ligne, 2) . ' MAD</span>
          </li>';
    }

    $texte_livraison = number_format($livraison, 2) . ' MAD';
    if ($livraison == 0) {
        $texte_livraison = 'Gratuite';
    }

    $texte_sous_total = number_format($sous_total, 2) . ' MAD';
    $texte_total = number_format($total, 2) . ' MAD';

    echo <<<HTML
<main class="container">
  <form method="POST" action="{$action_form}" id="formPaiement">
  <input type="hidden" name="confirmer_paiement" value="1">
  <input type="hidden" name="mode_pay" id="mode_pay" value="card">
  <input type="hidden" name="billing" id="billing_choice" value="same">

  <div class="row g-2">

    <section class="col-lg-7">

      <div class="section-head">
        <div>
          <h1>Paiement</h1>
          <p class="lead-text">Choisissez votre mode de paiement préféré.</p>
        </div>
      </div>

      <div class="row g-2 methods-row">
        <div class="col-12 col-sm-6">
          <button type="button" class="method active w-100" data-method="card">
            <span class="method-ico"><i class="bi bi-credit-card-2-front"></i></span>
            <span class="method-text">
              <span class="method-title">Carte bancaire</span>
              <span class="method-sub">Visa, Mastercard</span>
            </span>
            <span class="method-radio"><i class="bi bi-check-lg"></i></span>
          </button>
        </div>
        <div class="col-12 col-sm-6">
          <button type="button" class="method w-100" data-method="cod">
            <span class="method-ico"><i class="bi bi-cash-coin"></i></span>
            <span class="method-text">
              <span class="method-title">Paiement à la livraison</span>
              <span class="method-sub">Payez à réception</span>
            </span>
            <span class="method-radio"><i class="bi bi-check-lg"></i></span>
          </button>
        </div>
      </div>

      <div class="gm-card" id="card-form">
        <h2>
          <span>Informations de la carte</span>
          <span class="brands">
            <span class="brand-chip">VISA</span>
            <span class="brand-chip">MC</span>
          </span>
        </h2>

        <div class="field">
          <label for="cc">Numéro de carte</label>
          <input class="gm-input" type="text" id="cc" inputmode="numeric" maxlength="19" placeholder="1234 5678 9012 3456" />
        </div>

        <div class="field">
          <label for="cc-name">Nom sur la carte</label>
          <input class="gm-input" type="text" id="cc-name" placeholder="Jean Dupont" />
        </div>

        <div class="row g-2">
          <div class="col-7">
            <div class="field">
              <label for="exp">Date d'expiration</label>
              <input class="gm-input" type="text" id="exp" maxlength="5" placeholder="MM / AA" />
            </div>
          </div>
          <div class="col-5">
            <div class="field">
              <label for="cvv">CVV</label>
              <input class="gm-input" type="text" id="cvv" inputmode="numeric" maxlength="4" placeholder="•••" />
            </div>
          </div>
        </div>
      </div>

      <div class="gm-card" id="cod-form" style="display:none">
        <div class="cod-block">
          <i class="bi bi-cash-coin"></i>
          <div>
            <p class="cod-title">Paiement à la livraison</p>
            <p class="cod-sub">Réglez le livreur en espèces ou par carte à la réception de votre commande.</p>
          </div>
        </div>
      </div>

      <div class="gm-card">
        <h2><i class="bi bi-geo-alt"></i> Adresse de livraison</h2>

        <button type="button" class="radio-row active w-100" data-billing="same">
          <span class="radio-dot"></span>
          <span class="radio-text">
            <span class="radio-title">Utiliser mon adresse enregistrée</span>
            <span class="radio-sub">{$adresse_actuelle}</span>
          </span>
        </button>

        <button type="button" class="radio-row w-100" data-billing="different">
          <span class="radio-dot"></span>
          <span class="radio-text">
            <span class="radio-title">Utiliser une adresse différente</span>
            <span class="radio-sub">Saisir une nouvelle adresse de livraison</span>
          </span>
        </button>

        <div class="billing-extra" id="billing-extra">
          <div class="field">
            <label for="b-rue">Rue</label>
            <input class="gm-input" type="text" name="b_rue" id="b-rue" placeholder="12 rue des Lilas">
          </div>
          <div class="field">
            <label for="b-ville">Ville</label>
            <input class="gm-input" type="text" name="b_ville" id="b-ville" list="villesMaroc" placeholder="Marrakech">
            <datalist id="villesMaroc">
              <option value="Casablanca">
              <option value="Rabat">
              <option value="Marrakech">
              <option value="Fès">
              <option value="Tanger">
              <option value="Agadir">
              <option value="Meknès">
              <option value="Oujda">
              <option value="Kénitra">
              <option value="Tétouan">
              <option value="Safi">
              <option value="El Jadida">
              <option value="Béni Mellal">
              <option value="Nador">
              <option value="Khouribga">
              <option value="Settat">
              <option value="Mohammedia">
              <option value="Salé">
              <option value="Témara">
              <option value="Essaouira">
              <option value="Laâyoune">
            </datalist>
          </div>
        </div>
      </div>

      <a href="../Panier/Panier.php" class="back-link">
        <i class="bi bi-arrow-left"></i>
        Retour au panier
      </a>

    </section>

    <aside class="col-lg-5">
      <div class="gm-summary">

        <div class="sum-head">
          <h2>Votre commande</h2>
          <p>{$texte_nb_produits}</p>
        </div>

        <ul class="items">{$items_html}
        </ul>

        <dl class="totals">
          <div class="row"><dt>Sous-total</dt><dd>{$texte_sous_total}</dd></div>
          <div class="row"><dt>Livraison</dt><dd>{$texte_livraison}</dd></div>
        </dl>

        <div class="divider"></div>

        <div class="grand">
          <span class="grand-label">Total à payer</span>
          <span class="grand-amt">{$texte_total}</span>
        </div>

        <div class="summary-cta-wrap">
          <button type="submit" class="cta w-100" id="pay-btn">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Confirmer le paiement</span>
          </button>
        </div>

      </div>

      <div class="trust-card">
        <div class="trust-items">
          <div class="trust-item">
            <span class="trust-icon"><i class="bi bi-shield-check"></i></span>
            <span>Paiement sécurisé</span>
          </div>
          <div class="trust-item">
            <span class="trust-icon"><i class="bi bi-arrow-counterclockwise"></i></span>
            <span>Retour 14 jours</span>
          </div>
          <div class="trust-item">
            <span class="trust-icon"><i class="bi bi-headset"></i></span>
            <span>Support 7j/7</span>
          </div>
        </div>
        <div class="trust-line"></div>
        <p class="trust-legal">
          En confirmant vous acceptez nos <a href="#">CGV</a> et notre <a href="#">Politique de confidentialité</a>.
        </p>
      </div>

    </aside>

  </div>
  </form>
</main>
HTML;
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="paiement.js"></script>
<?php
if ($succes) {
    echo '
<script>
var toastEl = document.getElementById("toastSucces");
if (toastEl) {
    var toast = new bootstrap.Toast(toastEl, { delay: 5000 });
    toast.show();
}
</script>';
}
?>
</body>
</html>