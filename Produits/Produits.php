<?php
session_start();
require_once '../connexion.php';

// ===== FILTRES =====
$categ = 0;
if (isset($_GET['categ'])) {
    $categ = (int)$_GET['categ'];
}

$boutique = 0;
if (isset($_GET['boutique'])) {
    $boutique = (int)$_GET['boutique'];
}

$tri = '';
if (isset($_GET['tri'])) {
    $tri = $_GET['tri'];
}

$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

$prix_range = '';
if (isset($_GET['prix'])) {
    $prix_range = $_GET['prix'];
}

$page = 1;
if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
}
if ($page < 1) {
    $page = 1;
}

$par_page = 6;

// ===== REQUÊTE PRODUITS =====
$sql = "SELECT p.*, c.nom_Categ, b.nom_boutique
        FROM Produit p
        JOIN Categorie c ON p.ID_Categ = c.ID_Categ
        JOIN Boutique b ON p.ID_boutique = b.ID_boutique
        WHERE 1=1";

$params = [];

if ($categ > 0) {
    $sql .= " AND p.ID_Categ = ?";
    $params[] = $categ;
}

if ($boutique > 0) {
    $sql .= " AND p.ID_boutique = ?";
    $params[] = $boutique;
}

if ($search != '') {
    $sql .= " AND p.nom_Prod LIKE ?";
    $params[] = '%' . $search . '%';
}

if ($prix_range != '') {
    $range = explode('-', $prix_range);
    $sql .= " AND p.Prix >= ? AND p.Prix <= ?";
    $params[] = (float)$range[0];
    $params[] = (float)$range[1];
}

if ($tri == 'prix_asc') {
    $sql .= " ORDER BY p.Prix ASC";
} else if ($tri == 'prix_desc') {
    $sql .= " ORDER BY p.Prix DESC";
} else {
    $sql .= " ORDER BY p.date_ajout_Prod DESC";
}

$req_total = $pdo->prepare($sql);
$req_total->execute($params);
$total = $req_total->rowCount();

$nb_pages = ceil($total / $par_page);
if ($nb_pages < 1) {
    $nb_pages = 1;
}
if ($page > $nb_pages) {
    $page = $nb_pages;
}

$offset = ($page - 1) * $par_page;
$sql .= " LIMIT ? OFFSET ?";
$params[] = $par_page;
$params[] = $offset;

$req = $pdo->prepare($sql);
$req->execute($params);
$produits = $req->fetchAll(PDO::FETCH_ASSOC);

// ===== CATÉGORIES SIDEBAR =====
$req_cats = $pdo->prepare("SELECT * FROM Categorie ORDER BY nom_Categ ASC");
$req_cats->execute();
$categories = $req_cats->fetchAll(PDO::FETCH_ASSOC);

// ===== BOUTIQUES SIDEBAR =====
$req_bouts = $pdo->prepare("SELECT ID_boutique, nom_boutique FROM Boutique ORDER BY nom_boutique ASC");
$req_bouts->execute();
$boutiques = $req_bouts->fetchAll(PDO::FETCH_ASSOC);

// ===== HELPER PAGINATION =====
function build_url($p) {
    $_GET['page'] = $p;
    return 'Produits.php?' . http_build_query($_GET);
}

// ===== ROLE CHECK =====
if (isset($_SESSION['id_utili']) && $_SESSION['role'] == 'client') {
    $btn_class = '';
} else {
    $btn_class = 'd-none';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Green Market – Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="Produits.css" rel="stylesheet" />
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
                <li class="nav-item"><a class="nav-link active" href="Produits.php">Boutique</a></li>
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
                ?>
                <a href="#" class="position-relative text-decoration-none nav-icon">
                    <i class="bi bi-bell"></i>
                    <span class="cart-badge" id="bell-count">0</span>
                </a>
                <?php
                if (isset($_SESSION['id_utili'])) {
                    echo '<a href="../Profile-client/Profile-client.php" class="position-relative text-decoration-none nav-icon"><i class="bi bi-person"></i></a>';
                } else {
                    echo '<a href="../Inscription/Inscription.php" class="position-relative text-decoration-none nav-icon"><i class="bi bi-person"></i></a>';
                }
                ?>
            </div>
        </div>
    </div>
</nav>

<!-- PAGE HEADER -->
<section class="py-4 page-header">
    <div class="container">
        <small class="text-uppercase label-orange">Boutique</small>
        <h2 class="mb-0">Tous nos produits</h2>
        <small class="text-muted-ink">
            <?php
            if ($total > 1) {
                echo $total . " produits trouvés";
            } else {
                echo $total . " produit trouvé";
            }
            if ($search != '') {
                echo ' pour "<strong>' . $search . '</strong>"';
            }
            ?>
        </small>
    </div>
</section>

<!-- CONTENU -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">

            <!-- SIDEBAR -->
            <div class="col-12 col-lg-3">
                <form class="sidebar p-4" method="GET" action="Produits.php">

                    <!-- Recherche -->
                    <div class="mb-4">
                        <h6 class="fw-bold sidebar-title">Rechercher</h6>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control search-input" name="search" placeholder="Nom du produit..." value="<?= $search ?>">
                            <button class="btn btn-search" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>

                    <hr class="sidebar-divider">

                    <!-- Catégories -->
                    <div class="mb-4">
                        <h6 class="fw-bold sidebar-title">Catégories</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="categ" id="categ0" value="0" <?php if ($categ == 0) echo 'checked'; ?>>
                            <label class="form-check-label small" for="categ0">Toutes</label>
                        </div>
                        <?php foreach ($categories as $c) { ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="categ" id="categ<?= $c['ID_Categ'] ?>" value="<?= $c['ID_Categ'] ?>" <?php if ($categ == $c['ID_Categ']) echo 'checked'; ?>>
                                <label class="form-check-label small" for="categ<?= $c['ID_Categ'] ?>"><?= $c['nom_Categ'] ?></label>
                            </div>
                        <?php } ?>
                    </div>

                    <hr class="sidebar-divider">

                    <!-- Prix -->
                    <div class="mb-4">
                        <h6 class="fw-bold sidebar-title">Prix</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prix" id="prix0" value="" <?php if ($prix_range == '') echo 'checked'; ?>>
                            <label class="form-check-label small" for="prix0">Tous les prix</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prix" id="prix1" value="0-50" <?php if ($prix_range == '0-50') echo 'checked'; ?>>
                            <label class="form-check-label small" for="prix1">Moins de 50 MAD</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prix" id="prix2" value="50-150" <?php if ($prix_range == '50-150') echo 'checked'; ?>>
                            <label class="form-check-label small" for="prix2">50 – 150 MAD</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prix" id="prix3" value="150-300" <?php if ($prix_range == '150-300') echo 'checked'; ?>>
                            <label class="form-check-label small" for="prix3">150 – 300 MAD</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prix" id="prix4" value="300-99999" <?php if ($prix_range == '300-99999') echo 'checked'; ?>>
                            <label class="form-check-label small" for="prix4">Plus de 300 MAD</label>
                        </div>
                    </div>

                    <hr class="sidebar-divider">

                    <!-- Coopératives -->
                    <div class="mb-4">
                        <h6 class="fw-bold sidebar-title">Coopérative</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="boutique" id="bout0" value="0" <?php if ($boutique == 0) echo 'checked'; ?>>
                            <label class="form-check-label small" for="bout0">Toutes</label>
                        </div>
                        <?php foreach ($boutiques as $b) { ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="boutique" id="bout<?= $b['ID_boutique'] ?>" value="<?= $b['ID_boutique'] ?>" <?php if ($boutique == $b['ID_boutique']) echo 'checked'; ?>>
                                <label class="form-check-label small" for="bout<?= $b['ID_boutique'] ?>"><?= $b['nom_boutique'] ?></label>
                            </div>
                        <?php } ?>
                    </div>

                    <input type="hidden" name="tri" value="<?= $tri ?>">

                    <button type="submit" class="btn btn-appliquer w-100 mb-2">
                        <i class="bi bi-funnel me-1"></i>Appliquer
                    </button>
                    <a href="Produits.php" class="btn btn-reset w-100">
                        <i class="bi bi-x-circle me-1"></i>Réinitialiser
                    </a>

                </form>
            </div>

            <!-- GRILLE PRODUITS -->
            <div class="col-12 col-lg-9">

                <!-- Barre du haut -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="small text-muted-ink">
                        <?php
                        if ($total > 1) {
                            echo $total . " produits";
                        } else {
                            echo $total . " produit";
                        }
                        if ($nb_pages > 1) {
                            echo " &nbsp;·&nbsp; Page " . $page . " / " . $nb_pages;
                        }
                        ?>
                    </span>
                    <select class="form-select form-select-sm w-auto trier" id="sort-select">
                        <option value=""            <?php if ($tri == '') echo 'selected'; ?>>Les plus récents</option>
                        <option value="prix_asc"    <?php if ($tri == 'prix_asc') echo 'selected'; ?>>Prix croissant</option>
                        <option value="prix_desc"   <?php if ($tri == 'prix_desc') echo 'selected'; ?>>Prix décroissant</option>
                    </select>
                </div>

                <!-- GRILLE -->
                <div class="row g-3 product-grid">

                    <?php if (count($produits) == 0) { ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-search display-4 mb-3 d-block" style="color: var(--sand);"></i>
                            <p class="text-muted-ink">Aucun produit trouvé pour ces filtres.</p>
                            <a href="Produits.php" class="btn btn-voir btn-sm mt-2">Voir tous les produits</a>
                        </div>
                    <?php } ?>

                    <?php foreach ($produits as $p) { ?>
                        <div class="col-6 col-md-4">
                            <div class="card border-0 shadow-sm h-100">

                                <!-- BADGES -->
                                <div class="badges-wrapper">
                                    <?php if (strtotime($p['date_ajout_Prod']) > strtotime('-30 days')) { ?>
                                        <span class="badge badge-nouveau">Nouveau</span>
                                    <?php } ?>
                                    <?php if ($p['Stock'] == 0) { ?>
                                        <span class="badge badge-epuise">Épuisé</span>
                                    <?php } ?>
                                </div>

                                <div class="img-wrapper">
                                    <img src="<?= $p['Prod_img'] ?>" class="card-img-top product-img" alt="<?= $p['nom_Prod'] ?>">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <small class="text-muted-ink"><?= $p['nom_Categ'] ?></small>
                                    <h6 class="card-title mt-1 mb-1"><?= $p['nom_Prod'] ?></h6>
                                    <small class="boutique-name mb-2"><?= $p['nom_boutique'] ?></small>
                                    <p class="fw-bold price-text mb-3 mt-auto"><?= number_format($p['Prix'], 2) ?> MAD</p>
                                    <div class="d-flex gap-2">
                                        <a href="../Produit details/Produit details.php?id=<?= $p['ID_Prod'] ?>" class="btn btn-sm btn-voir <?php if ($btn_class == 'd-none') echo 'w-100'; else echo 'w-75'; ?>">Voir</a>
                                        <button class="btn btn-sm btn-add w-25 <?= $btn_class ?>" data-id="<?= $p['ID_Prod'] ?>">
                                            <i class="bi bi-cart"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php } ?>

                </div>

                <!-- PAGINATION -->
                <?php if ($nb_pages > 1) { ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">

                            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="<?= build_url($page - 1) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $nb_pages; $i++) { ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                    <a class="page-link" href="<?= build_url($i) ?>"><?= $i ?></a>
                                </li>
                            <?php } ?>

                            <li class="page-item <?php if ($page >= $nb_pages) echo 'disabled'; ?>">
                                <a class="page-link" href="<?= build_url($page + 1) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>

                        </ul>
                    </nav>
                <?php } ?>

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
                    <a href="../Home Page/Home.php" class="footer-link">Accueil</a>
                    <a href="Produits.php" class="footer-link">Boutique</a>
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
<script src="Produits.js"></script>
</body>
</html>