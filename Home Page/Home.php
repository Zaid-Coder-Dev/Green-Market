<?php
session_start();
require_once '../connexion.php';
require_once '../functions.php';

// BOUTIQUES CAROUSEL
try {
  $req = $pdo->prepare("SELECT * FROM Boutique ORDER BY RAND() LIMIT 3");
  $req->execute();
  $boutiques = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erreur : " . $e->getMessage());
}

// PRODUITS PHARES (les plus achetés)
try {
  $req2 = $pdo->prepare("SELECT p.*, c.nom_Categ, SUM(lc.Quantite) AS total_vendu
                          FROM Produit p
                          JOIN Categorie c ON p.ID_Categ = c.ID_Categ
                          JOIN Ligne_commande lc ON lc.ID_Prod = p.ID_Prod
                          JOIN Commande co ON co.ID_Com = lc.ID_Com
                          WHERE co.status_com != 'annulé'
                          GROUP BY p.ID_Prod
                          ORDER BY total_vendu DESC
                          LIMIT 3");
  $req2->execute();
  $produits = $req2->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erreur : " . $e->getMessage());
}

// ROLE CHECK
if (isset($_SESSION['id_utili']) && $_SESSION['role'] == 'client') {
  $btn_class = '';
} else {
  $btn_class = 'd-none';
}

// CAROUSEL INDICATORS
$indicators = '';
$i = 0;
foreach ($boutiques as $b) {
  $active = '';
  if ($i == 0) {
    $active = 'class="active"';
  }
  $indicators .= '<button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="' . $i . '" ' . $active . '></button>';
  $i++;
}

// CAROUSEL SLIDES
$slides = '';
$i = 0;
foreach ($boutiques as $b) {
  $active = '';
  if ($i == 0) {
    $active = 'active';
  }
  $img = '../uploads/boutiques_images/banner_default.jpg';
  if ($b['image_banner']) {
    $img = $b['image_banner'];
  }
  $slides .= '<div class="carousel-item ' . $active . ' h-100">
      <img src="' . $img . '" class="d-block w-100 hero-bg" alt="Boutique">
      <div class="hero-overlay"></div>
      <div class="container h-100 relative-container">
          <div class="hero-content">
              <span class="hero-label">' . $b['ville'] . '</span>
              <h1 class="hero-title">' . $b['nom_boutique'] . '</h1>
              <p class="hero-subtitle">' . $b['description_boutique'] . '</p>
              <div class="hero-buttons">
                  <a href="../Cooperatives/Cooperatives.php" class="btn btn-terracotta">Voir la Coopérative</a>
              </div>
          </div>
      </div>
  </div>';
  $i++;
}

// PRODUITS PHARES CARDS
$classes = ['card-bg-top', 'card-foreground', 'card-bg-bottom'];
$cards = '';
$i = 0;
foreach ($produits as $p) {
  $cards .= '<div class="stack-card ' . $classes[$i] . '">
      <div class="stack-img-wrap">
          <span class="stack-badge badge-sale">Top Vente</span>
          <img src="' . $p['Prod_img'] . '" alt="Produit" class="stack-img">
      </div>
      <div class="stack-body">
          <span class="stack-cat">' . $p['nom_Categ'] . '</span>
          <h3 class="stack-name">' . $p['nom_Prod'] . '</h3>
          <div class="stack-footer">
              <span class="stack-price">' . number_format($p['Prix'], 2) . ' MAD</span>
              <div class="stack-actions-group">
                  <a href="../Produit details/Produit details.php?id=' . $p['ID_Prod'] . '" class="btn-circle-action btn-view">
                      <i class="bi bi-arrow-up-right"></i>
                  </a>
                  <button class="btn-circle-action btn-add ' . $btn_class . '" data-id="' . $p['ID_Prod'] . '">
    <i class="bi bi-cart3"></i>
</button>
    
                </form>
              </div>
          </div>
      </div>
  </div>';
  $i++;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Green Market</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="Home.css">
</head>

<body>

  <!-- NAVBAR -->
  <?php render_navbar('nologo'); ?>

  <!-- HERO CAROUSEL -->
  <header class="hero">
    <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="6000" style="position:absolute;inset:0;width:100%;">

      <div class="carousel-indicators" style="margin-bottom:30px;">
        <?= $indicators ?>
      </div>

      <div class="carousel-inner h-100">
        <?= $slides ?>
      </div>

      <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>

    </div>
  </header>

  <!-- PRODUITS PHARES -->
  <section class="products-section" id="products">
    <div class="container">
      <div class="row align-items-center g-5">

        <div class="col-12 col-xl-5">
          <span class="section-label">NOS MEILLEURES VENTES</span>
          <h2 class="section-title-stack">
            <span>Produits</span>
            <span>Préférés.</span>
            <span>Nos Top</span>
            <span>Créations.</span>
          </h2>
          <p class="section-sub-stack mt-4">
            Explorez un aperçu exclusif des trésors les plus aimés par notre communauté.
          </p>
          <div class="mt-4 pt-2">
            <a href="../Produits/Produits.php" class="btn-discover-more">
              Voir Toute la Boutique <i class="bi bi-arrow-right ms-2"></i>
            </a>
          </div>
        </div>

        <div class="col-12 col-xl-7">
          <div class="card-interactive-stack">
            <?= $cards ?>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ABOUT -->
  <section class="about-section" id="about">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-12 col-md-6">
          <img src="../uploads/about_us.png" class="about-img">
        </div>
        <div class="col-12 col-md-6">
          <span class="section-label d-block mb-2">NOS RACINES</span>
          <h2 class="mb-4">Préserver la Culture, Soutenir les Artisans</h2>
          <p class="about-text">Green Market repose sur une collaboration directe avec les coopératives rurales à travers tout le Maroc.</p>
          <div class="row stats mt-4 g-3">
            <div class="col-6 col-sm-4">
              <div class="stat-num">12+</div>
              <div class="stat-label">Coopératives Actives</div>
            </div>
            <div class="col-6 col-sm-4">
              <div class="stat-num">100%</div>
              <div class="stat-label">Bio & Fait Maison</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <?php render_footer(); ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../Panier_handler.js"></script>
  <script src="Home.js"></script>
  
</body>

</html>