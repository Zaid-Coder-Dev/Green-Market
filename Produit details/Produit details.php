<?php
session_start();
require_once '../connexion.php';

$id_prod = 0;
if (isset($_GET['id'])) {
    $id_prod = $_GET['id'];
}

if ($id_prod == 0) {
    echo "<b>ERREUR :</b> Aucun ID produit dans l'URL. Exemple correct : <code>Produit details.php?id=1</code>";
}

$req = $pdo->prepare("
    SELECT p.*, c.nom_Categ, b.nom_boutique
    FROM Produit p
    JOIN Categorie c ON p.ID_Categ = c.ID_Categ
    JOIN Boutique b ON p.ID_boutique = b.ID_boutique
    WHERE p.ID_Prod = ?
");
$req->execute([$id_prod]);
$produit = $req->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    echo "<b>ERREUR :</b> Aucun produit trouvé avec ID = <b>" . $id_prod . "</b>. Vérifie que cet ID existe dans la table Produit.";
}

$req2 = $pdo->prepare("
    SELECT a.*, u.nom, u.prenom
    FROM Avis a
    JOIN Utilisateur u ON a.ID_utili = u.ID_utili
    WHERE a.ID_Prod = ?
    ORDER BY a.date_avis DESC
");
$req2->execute([$id_prod]);
$avis = $req2->fetchAll(PDO::FETCH_ASSOC);

$note_moyenne = 0;
if (count($avis) > 0) {
    $total = 0;
    foreach ($avis as $av) {
        $total += $av['note'];
    }
    $note_moyenne = $total / count($avis);
}

$req3 = $pdo->prepare("
    SELECT p.*, c.nom_Categ, b.nom_boutique
    FROM Produit p
    JOIN Categorie c ON p.ID_Categ = c.ID_Categ
    JOIN Boutique b ON p.ID_boutique = b.ID_boutique
    WHERE p.ID_Categ = ? AND p.ID_Prod != ?
    ORDER BY RAND()
    LIMIT 3
");
$req3->execute([$produit['ID_Categ'], $id_prod]);
$similaires = $req3->fetchAll(PDO::FETCH_ASSOC);

$est_nouveau = false;
if (isset($produit['date_ajout_Prod'])) {
    $dateAjout = new DateTime($produit['date_ajout_Prod']);
    $maintenant = new DateTime();
    
    $diff = date_diff($dateAjout, $maintenant)->days;
    if ($diff < 30) {
        $est_nouveau = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Green Market - <?= $produit['nom_Prod'] ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="Produit details.css">
</head>

<body>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark navbar-home">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="../Home Page/Home.php">
        <img src="../logo_Qofa.png" alt="" class="navbar-logo">Green Market
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav mx-auto nav-links">
          <li class="nav-item"><a class="nav-link" href="../Home Page/Home.php">Accueil</a></li>
          <li class="nav-item"><a class="nav-link" href="../Cooperatives/Cooperatives.php">Coopératives</a></li>
          <li class="nav-item"><a class="nav-link" href="../Categories/Categories.php">Catégories</a></li>
          <li class="nav-item"><a class="nav-link active" href="../Produits/Produits.php">Boutique</a></li>
        </ul>
        <div class="d-flex align-items-center gap-3">
          <a href="../Panier/Panier.php" class="position-relative text-decoration-none nav-icon">
            <i class="bi bi-cart3"></i>
            <span class="cart-badge" id="cart-count">0</span>
          </a>
          <a href="#" class="position-relative text-decoration-none nav-icon">
            <i class="bi bi-bell"></i>
            <span class="cart-badge" id="bell-count">0</span>
          </a>
          <a href="../Profile-client/Profile-client.php" class="position-relative text-decoration-none nav-icon">
            <i class="bi bi-person"></i>
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- PRODUCT -->
  <section class="container py-5">
    <div class="row g-5">

      <!-- IMAGE -->
      <div class="col-lg-5">
        <div class="product-image position-relative">
          <?php if ($est_nouveau) { ?>
            <span class="sale-badge" style="background-color: var(--terracotta);">Nouveau</span>
          <?php } ?>
          <?php if ($produit['Stock'] == 0) { ?>
            <span class="sale-badge" style="background-color: var(--ink); top: <?= $est_nouveau ? '55px' : '15px' ?>;">Épuisé</span>
          <?php } ?>
          <img id="mainImage" class="img-fluid rounded-4" src="../uploads/produits/<?= $produit['Prod_img'] ?>" alt="<?= $produit['nom_Prod'] ?>">
        </div>
        <div id="SMI" class="small-images mt-3">
          <img class="small-img active-img" src="../uploads/produits/<?= $produit['Prod_img'] ?>" alt="<?= $produit['nom_Prod'] ?>">
        </div>
      </div>

      <!-- DETAILS -->
      <div class="col-lg-7">
        <p id="ctg" class="category"><?= $produit['nom_Categ'] ?></p>
        <h1 id="pro-tit" class="product-title"><?= $produit['nom_Prod'] ?></h1>

        <!-- ÉTOILES -->
        <div id="product-stars" class="stars mb-2">
          <?php
          $note = round($note_moyenne * 2) / 2;
          for ($i = 1; $i <= 5; $i++) {
              if ($i <= floor($note)) {
                  echo '<i class="bi bi-star-fill text-warning"></i>';
              } elseif ($i - $note < 1) {
                  echo '<i class="bi bi-star-half text-warning"></i>';
              } else {
                  echo '<i class="bi bi-star text-warning"></i>';
              }
          }
          ?>
          <small class="text-muted-ink ms-1">(<?= count($avis) ?> avis)</small>
        </div>

        <h2 id="prix-p" class="price"><?= number_format($produit['Prix'], 2) ?> MAD</h2>
        <p id="des-p" class="product-desc"><?= $produit['description_Prod'] ?></p>
        <p class="mb-3"><small class="text-muted-ink"><i class="bi bi-shop me-1"></i><?= $produit['nom_boutique'] ?></small></p>

        <!-- QUANTITY -->
        <div class="quantity-box mb-4">
          <button id="minusBtn">-</button>
          <span id="quantity">1</span>
          <button id="plusBtn">+</button>
        </div>

        <!-- BUTTONS -->
        <div class="product-actions mb-4">
          <?php if ($produit['Stock'] == 0) { ?>
            <button class="btn-add" disabled style="opacity:0.5;cursor:not-allowed;">
              <i class="bi bi-cart3"></i> Stock épuisé
            </button>
          <?php } else { ?>
            <button class="btn-add" data-id="<?= $produit['ID_Prod'] ?>">
              <i class="bi bi-cart3"></i> Ajouter au panier
            </button>
          <?php } ?>
          <button class="buy-btn">Acheter maintenant</button>
          <button class="wishlist-btn">
            <i class="bi bi-heart"></i>
          </button>
        </div>

        <!-- FEATURES -->
        <div class="features">
          <div class="feature-item">
            <i class="bi bi-truck"></i>
            <div>
              <h6>Livraison gratuite</h6>
              <p>dès 500 MAD</p>
            </div>
          </div>
          <div class="feature-item">
            <i class="bi bi-arrow-counterclockwise"></i>
            <div>
              <h6>Retour sous 14 jours</h6>
              <p>Échange garanti</p>
            </div>
          </div>
          <div class="feature-item">
            <i class="bi bi-shield-check"></i>
            <div>
              <h6>Paiement sécurisé</h6>
              <p>100% protégé</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TRACEABILITE -->
  <section class="container mb-5">
    <div class="trace-wrapper">
      <div class="trace-box">
        <h3 class="mb-4">
          <i class="bi bi-shield-check"></i>
          Traçabilité — de l'artisan vers vous
        </h3>
      </div>
      <div class="trace-boxx">

        <div class="trace-item">
          <div class="trace-dot"><i class="bi bi-person"></i></div>
          <div class="trace-card">
            <img src="../uploads/categories/step-1.png" onerror="this.style.display='none'">
            <div>
              <div class="trace-meta">
                <i class="bi bi-calendar"></i> 15 Mars 2024
                <i class="bi bi-geo-alt"></i> Atlas, Maroc
              </div>
              <h5>Récolte / Production</h5>
              <p>Les matières premières ont été récoltées par la coopérative partenaire.</p>
            </div>
          </div>
        </div>

        <div class="trace-item">
          <div class="trace-dot"><i class="bi bi-gear"></i></div>
          <div class="trace-card">
            <img src="../uploads/categories/step_2.jpeg" onerror="this.style.display='none'">
            <div>
              <div class="trace-meta">
                <i class="bi bi-calendar"></i> 20 Mars 2024
                <i class="bi bi-geo-alt"></i> Fès, Maroc
              </div>
              <h5>Fabrication Artisanale</h5>
              <p>Création par les maîtres artisans selon les techniques traditionnelles.</p>
            </div>
          </div>
        </div>

        <div class="trace-item">
          <div class="trace-dot"><i class="bi bi-box-seam"></i></div>
          <div class="trace-card">
            <img src="../uploads/categories/step_3.jpeg" onerror="this.style.display='none'">
            <div>
              <div class="trace-meta">
                <i class="bi bi-calendar"></i> 28 Mars 2024
                <i class="bi bi-geo-alt"></i> Casablanca, Maroc
              </div>
              <h5>Contrôle & Emballage</h5>
              <p>Vérification qualité et conditionnement avec code QR traçabilité.</p>
            </div>
          </div>
        </div>

        <div class="trace-item">
          <div class="trace-dot"><i class="bi bi-truck"></i></div>
          <div class="trace-card">
            <img src="../uploads/categories/step-4.jpeg" onerror="this.style.display='none'">
            <div>
              <div class="trace-meta">
                <i class="bi bi-calendar"></i> En attente
                <i class="bi bi-geo-alt"></i> Votre adresse
              </div>
              <h5>Livraison</h5>
              <p>Expédié avec soin jusqu'à votre domicile.</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- TABS -->
  <section class="container mb-5">
    <ul class="nav nav-tabs custom-tabs">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#caracteristiques">Caractéristiques</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#description">Description</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#livraison">Livraison</button>
      </li>
    </ul>
    <div class="tab-content custom-tab-content">
      <div class="tab-pane fade show active" id="caracteristiques">
        <h6>Caractéristiques du produit</h6>
        <ul>
          <li>Boutique : <?= $produit['nom_boutique'] ?></li>
          <li>Catégorie : <?= $produit['nom_Categ'] ?></li>
          <li>Stock disponible : <?= $produit['Stock'] ?></li>
          <li>Ajouté le : <?= date('d/m/Y', strtotime($produit['date_ajout_Prod'])) ?></li>
        </ul>
      </div>
      <div class="tab-pane fade" id="description">
        <p><?= $produit['description_Prod'] ?></p>
      </div>
      <div class="tab-pane fade" id="livraison">
        <h6>Livraison & Retours</h6>
        <ul>
          <li>Livraison gratuite dès 500 MAD</li>
          <li>Délai : 2 à 5 jours ouvrés</li>
          <li>Retour accepté sous 14 jours</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- AVIS CLIENTS -->
  <section class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Avis clients</h4>
      <div class="d-flex align-items-center">
        <span class="me-2 fs-5 fw-semibold"><?= number_format($note_moyenne, 1) ?></span>
        <div class="review-stars">
          <?php
          $note = round($note_moyenne * 2) / 2;
          for ($i = 1; $i <= 5; $i++) {
              if ($i <= floor($note)) {
                  echo '<i class="bi bi-star-fill text-warning"></i>';
              } elseif ($i - $note < 1) {
                  echo '<i class="bi bi-star-half text-warning"></i>';
              } else {
                  echo '<i class="bi bi-star text-warning"></i>';
              }
          }
          ?>
        </div>
      </div>
    </div>

    <?php foreach ($avis as $av) { ?>
      <div class="review-box d-flex align-items-start mb-3">
        <div class="review-avatar me-3"><?= strtoupper(substr($av['prenom'], 0, 1)) ?></div>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div>
              <h6 class="mb-0"><?= $av['prenom'] ?> <?= strtoupper(substr($av['nom'], 0, 1)) ?>.</h6>
              <small class="text-muted-ink"><?= date('d/m/Y', strtotime($av['date_avis'])) ?></small>
            </div>
            <div class="review-stars">
              <?php for ($i = 1; $i <= 5; $i++) { ?>
                <?php if ($i <= $av['note']) { ?>
                  <i class="bi bi-star-fill"></i>
                <?php } else { ?>
                  <i class="bi bi-star"></i>
                <?php } ?>
              <?php } ?>
            </div>
          </div>
          <p class="mb-0"><?= $av['commentaire'] ?></p>
        </div>
      </div>
    <?php } ?>

    <?php if (count($avis) == 0) { ?>
      <p class="text-muted-ink">Aucun avis pour ce produit.</p>
    <?php } ?>
  </section>

  <!-- COMMENT SECTION -->
  <section class="container mb-5">
    <h4 class="fw-bold mb-3">Ajouter un avis</h4>
    <div class="card comment-form-card mb-4">
      <form id="commentForm">
        <input type="hidden" name="id_prod" value="<?= $produit['ID_Prod'] ?>">
        <div class="mb-3">
          <label class="form-label">Nom</label>
          <input type="text" class="form-control" id="commentName" placeholder="Votre nom">
        </div>
        <div class="mb-3">
          <label class="form-label">Note</label>
          <div id="starRating" class="star-rating">
            <i class="bi bi-star" data-value="1"></i>
            <i class="bi bi-star" data-value="2"></i>
            <i class="bi bi-star" data-value="3"></i>
            <i class="bi bi-star" data-value="4"></i>
            <i class="bi bi-star" data-value="5"></i>
          </div>
          <input type="hidden" id="commentRating" value="0">
        </div>
        <div class="mb-3">
          <label class="form-label">Commentaire</label>
          <textarea class="form-control" id="commentText" rows="3" placeholder="Écrivez votre avis..."></textarea>
        </div>
        <button type="submit" class="comment-btn">Publier l'avis</button>
      </form>
    </div>
    <div id="newComments"></div>
  </section>

  <!-- VOUS AIMEREZ AUSSI -->
  <?php if (count($similaires) > 0) { ?>
  <section class="container mt-5 mb-5">
    <h4 class="fw-bold mb-4">Vous aimerez aussi</h4>
    <div class="row g-4">
      <?php foreach ($similaires as $s) { ?>
        <?php
        $est_nouveau_s = false;
        if ($s['date_ajout_Prod']) {
            $diff_s = (time() - strtotime($s['date_ajout_Prod'])) / 86400;
            if ($diff_s < 30) {
                $est_nouveau_s = true;
            }
        }
        ?>
        <div class="col-6 col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="badges-wrapper">
              <?php if ($est_nouveau_s) { ?>
                <span class="badge badge-nouveau">Nouveau</span>
              <?php } ?>
              <?php if ($s['Stock'] == 0) { ?>
                <span class="badge badge-epuise">Épuisé</span>
              <?php } ?>
            </div>
            <div class="img-wrapper">
              <img src="../uploads/produits/<?= $s['Prod_img'] ?>" class="card-img-top product-img" alt="<?= $s['nom_Prod'] ?>">
            </div>
            <div class="card-body d-flex flex-column">
              <small class="text-muted-ink"><?= $s['nom_Categ'] ?></small>
              <h6 class="card-title mt-1 mb-1"><?= $s['nom_Prod'] ?></h6>
              <small class="boutique-name mb-2"><?= $s['nom_boutique'] ?></small>
              <p class="fw-bold price-text mb-3 mt-auto"><?= number_format($s['Prix'], 2) ?> MAD</p>
              <div class="d-flex gap-2">
                <a href="Produit details.php?id=<?= $s['ID_Prod'] ?>" class="btn btn-sm w-75 btn-voir">Voir</a>
                <button class="btn btn-sm btn-add w-25" data-id="<?= $s['ID_Prod'] ?>"><i class="bi bi-cart"></i></button>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </section>
  <?php } ?>

  <!-- FOOTER -->
  <footer>
    <div class="footer-top pt-4">
      <div class="footer-stripe"></div>
      <div class="container">
        <div class="row g-4">
          <div class="col-12 col-md-3">
            <h5 class="text-white fw-bold mb-2" style="font-family:'Playfair Display',serif;">Green Market</h5>
            <p class="footer-text">Votre marketplace de produits artisanaux marocains, directs des coopératives.</p>
            <div class="footer-socials">
              <a href="#" class="footer-social"><i class="bi bi-facebook"></i></a>
              <a href="#" class="footer-social"><i class="bi bi-instagram"></i></a>
              <a href="#" class="footer-social"><i class="bi bi-twitter-x"></i></a>
              <a href="#" class="footer-social"><i class="bi bi-youtube"></i></a>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <h6 class="footer-title">Liens utiles</h6>
            <a href="../Home Page/Home.php" class="footer-link">Accueil</a>
            <a href="../Produits/Produits.php" class="footer-link">Boutique</a>
            <a href="../Categories/Categories.php" class="footer-link">Catégories</a>
            <a href="#contact" class="footer-link">Contact</a>
          </div>
          <div class="col-6 col-md-3">
            <h6 class="footer-title">Catégories</h6>
            <a href="#" class="footer-link">Produits Bio</a>
            <a href="#" class="footer-link">Cosmétiques</a>
            <a href="#" class="footer-link">Artisanat</a>
            <a href="#" class="footer-link">Mode Traditionnelle</a>
          </div>
          <div class="col-12 col-md-3" id="contact">
            <h6 class="footer-title">Contact</h6>
            <div class="footer-contact-item"><i class="bi bi-envelope"></i><span>contact@greenmarket.ma</span></div>
            <div class="footer-contact-item"><i class="bi bi-telephone"></i><span>+212 6 00 00 00 00</span></div>
            <div class="footer-contact-item"><i class="bi bi-geo-alt"></i><span>Marrakech, Maroc</span></div>
          </div>
        </div>
        <div class="footer-divider"></div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="container">
        <p class="footer-bottom-text">&copy; 2026 Green Market. Tous droits réservés.</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="Produit details.js"></script>
</body>

</html>