<?php
session_start();
require_once '../connexion.php';
require_once '../functions.php';

try {
    $req = $pdo->prepare("
        SELECT b.*,
               COUNT(p.ID_Prod) AS nb_produits
        FROM Boutique b
        LEFT JOIN Produit p ON p.ID_boutique = b.ID_boutique
        GROUP BY b.ID_boutique
        ORDER BY b.nom_boutique ASC
    ");
    $req->execute();
    $cooperatives = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

$villes = [];

foreach ($cooperatives as $c) {
    if (!empty($c['ville']) && !in_array($c['ville'], $villes)) {
        $villes[] = $c['ville'];
    }
}

sort($villes);
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coopératives - Green Market</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="cooperatives.css">
</head>

<body>

    <!-- NAVBAR -->
    <?php render_navbar('logo') ?>

    <!-- HEADER -->
    <section class="gm-page-header">
        <div class="container text-center">
            <h1 class="gm-page-title">Nos Coopératives</h1>
            <p class="gm-page-subtitle">
                Découvrez les artisans et producteurs marocains qui façonnent Green Market.
            </p>
        </div>
    </section>

    <!-- SEARCH -->
    <div class="container gm-filter-bar">
        <div class="row align-items-center">

            <div class="col-md-6">
                <div class="input-group gm-search-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>

                    <input type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="Rechercher une coopérative...">
                </div>
            </div>

            <div class="col-md-3 mt-2 mt-md-0">
                <select id="villeFilter" class="form-select gm-select">

                    <option value="">Toutes les villes</option>

                    <?php
                    foreach ($villes as $ville) {
                        echo '<option value="' . $ville . '">' . $ville . '</option>';
                    }
                    ?>

                </select>
            </div>

            <div class="col-md-3 mt-2 mt-md-0 text-md-end">
                <span class="gm-result-count" id="resultCount">
                    <?= count($cooperatives) ?> coopérative<?= count($cooperatives) > 1 ? 's' : '' ?>
                </span>
            </div>

        </div>
    </div>

    <!-- COOPERATIVES -->
    <main class="container gm-main">

        <div class="row g-4" id="coopGrid">

            <?php
            foreach ($cooperatives as $c) {

                echo '
                <div class="col-6 gm-card-wrapper"
                     data-nom="' . strtolower($c['nom_boutique']) . '"
                     data-ville="' . $c['ville'] . '">

                    <a href="../Cooperative detail/Cooperative detail.php?id=' . $c['ID_boutique'] . '" class="gm-card">

                        <div class="gm-card-photo-col">';

                if (!empty($c['image_banner'])) {
                    echo '<img src="' . $c['image_banner'] . '" class="gm-card-banner" alt="' . $c['nom_boutique'] . '">';
                } else {
                    echo '<img src="../uploads/boutiques_images/banner_default.jpg" class="gm-card-banner" alt="' . $c['nom_boutique'] . '">';
                }

                echo '<div class="gm-card-logo-badge">';

                if (!empty($c['logo'])) {
                    echo '<img src="' . $c['logo'] . '" alt="' . $c['nom_boutique'] . '">';
                } else {
                    echo '<img src="../uploads/boutiques_images/logo_default.png" alt="' . $c['nom_boutique'] . '">';
                }

                echo '
                            </div>

                        </div>

                        <div class="gm-card-content-col">

                            <span class="gm-card-location">
                                <i class="bi bi-geo-alt-fill"></i>
                                ' . $c['ville'] . '
                            </span>

                            <h2 class="gm-card-name">
                                ' . $c['nom_boutique'] . '
                            </h2>

                            <p class="gm-card-desc">
                                ' . $c['description_boutique'] . '
                            </p>

                            <div class="gm-card-footer">

                                <span class="gm-card-count">
                                    <i class="bi bi-box-seam"></i>
                                    ' . $c['nb_produits'] . ' produit' . ($c['nb_produits'] > 1 ? 's' : '') . '
                                </span>

                                <span class="gm-card-cta">
                                    Voir la boutique
                                    <i class="bi bi-arrow-right"></i>
                                </span>

                            </div>

                        </div>

                    </a>

                </div>';
            }
            ?>

        </div>

        <div id="emptyState" class="gm-empty-state d-none">
            <i class="bi bi-search"></i>
            <p>Aucune coopérative trouvée.</p>
        </div>

    </main>

    <!-- FOOTER -->
    <?php render_footer() ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="cooperatives.js"></script>

</body>

</html>