<?php
session_start();
require_once '../connexion.php';
require_once '../functions.php';

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
    <?php render_navbar('logo')?>

    
    <!-- BANNER -->
    <div class="boutique-banner position-relative">
    <?php
        if ($boutique['image_banner']) {
            echo '<img src="' . $boutique['image_banner'] . '" alt="Bannière" class="banner-img">';
        } else {
            echo '<img src="../uploads/boutiques_images/banner_default.jpg" alt="Bannière" class="banner-img">';
        }
    ?>
    <div class="banner-overlay"></div>

    <div class="container banner-content">

        <!-- LOGO -->
        <?php
        if ($boutique['logo']) {
            echo '<img src="' . $boutique['logo'] . '" alt="Logo" class="boutique-logo">';
        } else {
            echo '<img src="../uploads/boutiques_images/logo_default.png" alt="Logo" class="boutique-logo">';
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

                            <button class="btn btn-sm btn-add w-25 ' . $btn_class . '" data-id="' . $p['ID_Prod'] . '">
                                <i class="bi bi-cart"></i>
                            </button>
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
    <?php render_footer()?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Panier_handler.js"></script>
    <script src="Cooperative detail.js"></script>
</body>

</html>