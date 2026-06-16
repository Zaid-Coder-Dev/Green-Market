<?php
session_start();
require_once '../connexion.php';

// BOUTIQUE
if (!isset($_GET['id'])) {
    die("Boutique introuvable.");
}

$id_boutique = $_GET['id'];

try {
    $req = $pdo->prepare("SELECT * FROM Boutique WHERE ID_boutique = ?");
    $req->execute([$id_boutique]);
    $boutique = $req->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

if (!$boutique) {
    die("Boutique introuvable.");
}

// CATEGORIES
try {
    $req = $pdo->prepare("
        SELECT DISTINCT c.ID_Categ, c.nom_Categ
        FROM Categorie c
        JOIN Produit p ON p.ID_Categ = c.ID_Categ
        WHERE p.ID_boutique = ?
        ORDER BY c.nom_Categ ASC
    ");
    $req->execute([$id_boutique]);
    $categories = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// PRODUCTS
try {
    $req = $pdo->prepare("
        SELECT p.*, c.nom_Categ
        FROM Produit p
        JOIN Categorie c ON p.ID_Categ = c.ID_Categ
        WHERE p.ID_boutique = ?
        ORDER BY p.date_ajout_Prod DESC
    ");
    $req->execute([$id_boutique]);
    $produits = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

$nb_produits = count($produits);

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
    <title><?= $boutique['nom_boutique'] ?> — Green Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Cooperative detail.css">
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
                <li class="nav-item"><a class="nav-link active" href="../Cooperatives/Cooperatives.php">Coopératives</a></li>
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

<!-- BANNER -->
<div class="boutique-banner position-relative">
    <?php
    if ($boutique['image_banner']) {
        echo '<img src="../uploads/boutiques/' . $boutique['image_banner'] . '" alt="Bannière" class="banner-img">';
    } else {
        echo '<img src="../uploads/boutiques/default.png" alt="Bannière" class="banner-img">';
    }
    ?>
    <div class="banner-overlay"></div>

    <div class="container banner-content">

        <!-- LOGO -->
        <?php
        if ($boutique['logo']) {
            echo '<img src="../uploads/boutiques/' . $boutique['logo'] . '" alt="Logo" class="boutique-logo">';
        } else {
            echo '<div class="boutique-logo-placeholder d-flex align-items-center justify-content-center"><i class="bi bi-shop"></i></div>';
        }
        ?>

        <!-- BOUTIQUE INFO -->
        <div class="boutique-info">
            <h1 class="boutique-name"><?= $boutique['nom_boutique'] ?></h1>

            <div class="boutique-meta d-flex flex-wrap gap-2 mt-2">
                <?php
                if ($boutique['ville']) {
                    echo '<span class="meta-chip"><i class="bi bi-geo-alt-fill"></i> ' . $boutique['ville'] . '</span>';
                }
                if ($boutique['telephone']) {
                    echo '<span class="meta-chip"><i class="bi bi-telephone-fill"></i> ' . $boutique['telephone'] . '</span>';
                }
                echo '<span class="meta-chip"><i class="bi bi-box-seam"></i> ' . $nb_produits . ' produit' . ($nb_produits > 1 ? 's' : '') . '</span>';
                ?>
            </div>

            <div class="boutique-socials d-flex gap-2 mt-3">
                <?php
                if ($boutique['FB_link']) {
                    echo '<a href="' . $boutique['FB_link'] . '" target="_blank" class="social-btn"><i class="bi bi-facebook"></i></a>';
                }
                if ($boutique['Insta_link']) {
                    echo '<a href="' . $boutique['Insta_link'] . '" target="_blank" class="social-btn"><i class="bi bi-instagram"></i></a>';
                }
                ?>
            </div>
        </div>

    </div>
</div>

<!-- DESCRIPTION -->
<?php
if ($boutique['description_boutique']) {
    echo '
    <div class="boutique-desc-bar">
        <div class="container">
            <p class="boutique-desc-text">' . $boutique['description_boutique'] . '</p>
        </div>
    </div>';
}
?>

<!-- PRODUCTS -->
<div class="container py-5">

    <!-- CATEGORY PILLS -->
    <?php
    if (count($categories) > 0) {
        echo '<div class="d-flex align-items-center gap-2 flex-wrap mb-4" id="categ-pills">';
        echo '<button class="pill-btn active" data-categ="tous">Tous</button>';
        foreach ($categories as $cat) {
            echo '<button class="pill-btn" data-categ="' . $cat['ID_Categ'] . '">' . $cat['nom_Categ'] . '</button>';
        }
        echo '</div>';
    }
    ?>

    <!-- RESULT COUNT -->
    <p class="result-count mb-4" id="result-count">
        <?= $nb_produits ?> produit<?= $nb_produits > 1 ? 's' : '' ?>
    </p>

    <!-- PRODUCT GRID -->
    <?php
    if ($nb_produits == 0) {
        echo '
        <div class="text-center py-5">
            <i class="bi bi-box-seam empty-icon"></i>
            <p class="mt-3 text-muted-ink">Cette coopérative n\'a pas encore de produits.</p>
        </div>';
    } else {
        echo '<div class="row g-4" id="products-grid">';

        foreach ($produits as $p) {

            $is_nouveau = false;
            if ($p['date_ajout_Prod']) {
                $diff = (time() - strtotime($p['date_ajout_Prod'])) / 86400;
                if ($diff < 30) {
                    $is_nouveau = true;
                }
            }
            $is_epuise = ($p['Stock'] == 0);

            echo '
            <div class="col-6 col-md-4 product-col" data-categ="' . $p['ID_Categ'] . '">
                <div class="card border-0 shadow-sm h-100">
                    <div class="badges-wrapper">';

            if ($is_nouveau) {
                echo '<span class="badge badge-nouveau">Nouveau</span>';
            }
            if ($is_epuise) {
                echo '<span class="badge badge-epuise">Épuisé</span>';
            }

            echo '
                    </div>
                    <div class="img-wrapper">
                        <img src="' . $p['Prod_img'] . '" class="card-img-top product-img" alt="' . $p['nom_Prod'] . '">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted-ink">' . $p['nom_Categ'] . '</small>
                        <h6 class="card-title mt-1 mb-1">' . $p['nom_Prod'] . '</h6>
                        <p class="fw-bold price-text mb-3 mt-auto">' . number_format($p['Prix'], 2) . ' MAD</p>
                        <div class="d-flex gap-2">
                            <a href="../Produit details/Produit details.php?id=' . $p['ID_Prod'] . '" class="btn btn-sm btn-voir ' . ($btn_class == 'd-none' ? 'w-100' : 'w-75') . '">Voir</a>
                            <button class="btn btn-sm btn-add w-25 ' . $btn_class . '" data-id="' . $p['ID_Prod'] . '"><i class="bi bi-cart"></i></button>
                        </div>
                    </div>
                </div>
            </div>';
        }

        echo '</div>';
    }
    ?>

</div>

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
<script src="Cooperative detail.js"></script>
</body>
</html>