<?php
session_start();
require_once '../connexion.php';

// BOUTIQUES CAROUSEL
try {
    $req = $pdo->prepare("SELECT * FROM Boutique ORDER BY RAND() LIMIT 3");
    $req->execute();
    $boutiques = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// PRODUITS PHARES
try {
    $req2 = $pdo->prepare("SELECT p.*, c.nom_Categ FROM Produit p JOIN Categorie c ON p.ID_Categ = c.ID_Categ LIMIT 3");
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
  <nav class="navbar navbar-expand-lg fixed-top navbar-dark navbar-home">
    <div class="container">
      <a class="navbar-brand" href="#">Green Market</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav mx-auto nav-links">
          <li class="nav-item"><a class="nav-link active" href="#">Accueil</a></li>
          <li class="nav-item"><a class="nav-link" href="../Cooperatives/Cooperatives.php">Coopératives</a></li>
          <li class="nav-item"><a class="nav-link" href="../Categories/Categories.php">Catégories</a></li>
          <li class="nav-item"><a class="nav-link" href="../Produits/Produits.php">Boutique</a></li>
        </ul>
        <div class="d-flex align-items-center gap-3">
                         <?php
                if (isset($_SESSION['id_utili']) && $_SESSION['role'] == 'client') {
                    echo '
                    <a href="../Panier/Panier.php" class="position-relative text-decoration-none nav-icon">
                        <i class="bi bi-cart3"></i>
                        <span class="cart-badge" id="cart-count">0</span>
                    </a>';
                }
                
                if (isset($_SESSION['id_utili'])) {
                    echo '<a href="../Profile-client/Profile-client.php" class="position-relative text-decoration-none nav-icon"><i class="bi bi-person"></i></a>
                    <a href="#" class="position-relative text-decoration-none nav-icon"><i class="bi bi-bell"></i><span class="cart-badge" id="bell-count">0</span></a>';
                } else {
                    echo '<a href="../Inscription/Inscription.php" class="position-relative text-decoration-none nav-icon"><i class="bi bi-person"></i></a>';
                }
                ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- HERO CAROUSEL -->
  <header class="hero">
    <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="6000" style="position:absolute;inset:0;width:100%;">

      <div class="carousel-indicators" style="margin-bottom:30px;">
        <?php for ($i = 0; $i < count($boutiques); $i++) { ?>
          <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" <?php if ($i == 0) echo 'class="active"'; ?>></button>
        <?php } ?>
      </div>

      <div class="carousel-inner h-100">
        <?php
        $i = 0;
        foreach ($boutiques as $b) {
        ?>
          <div class="carousel-item <?php if ($i == 0) echo 'active'; ?> h-100">
            <img src="<?=$b['image_banner'] ? $b['image_banner'] : '../uploads/boutiques_images/banner_default.jpg' ?>" class="d-block w-100 hero-bg" alt="Boutique">
            <div class="hero-overlay"></div>
            <div class="container h-100 relative-container">
              <div class="hero-content">
                <span class="hero-label"><?= $b['ville'] ?></span>
                <h1 class="hero-title"><?= $b['nom_boutique'] ?></h1>
                <p class="hero-subtitle"><?= $b['description_boutique'] ?></p>
                <div class="hero-buttons">
                  <a href="../Cooperatives/Cooperatives.php" class="btn btn-terracotta">Voir la Coopérative</a>
                </div>
              </div>
            </div>
          </div>
        <?php $i++; } ?>
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
            <?php
            $classes = ['card-bg-top', 'card-foreground', 'card-bg-bottom'];
            $i = 0;
            foreach ($produits as $p) {
            ?>
              <div class="stack-card <?= $classes[$i] ?>">
                <div class="stack-img-wrap">
                  <span class="stack-badge badge-sale">Top Vente</span>
                  <img src="<?= $p['Prod_img'] ?>" alt="Produit" class="stack-img">
                </div>
                <div class="stack-body">
                  <span class="stack-cat"><?= $p['nom_Categ'] ?></span>
                  <h3 class="stack-name"><?= $p['nom_Prod'] ?></h3>
                  <div class="stack-footer">
                    <span class="stack-price"><?= number_format($p['Prix'], 2) ?> MAD</span>
                    <div class="stack-actions-group">
                      <a href="../Produit details/Produit details.php?id=<?= $p['ID_Prod'] ?>" class="btn-circle-action btn-view">
                        <i class="bi bi-arrow-up-right"></i>
                      </a>
                      <button class="btn-circle-action btn-cart <?= $btn_class ?>" data-id="<?= $p['ID_Prod'] ?>">
                        <i class="bi bi-cart3"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php $i++; } ?>
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
            <a href="#" class="footer-link">Accueil</a>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="Home.js"></script>
</body>

</html>