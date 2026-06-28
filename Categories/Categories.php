<?php
session_start();
require_once '../connexion.php';
require_once '../functions.php';

// Récupération des catégories
try {
    $req = $pdo->prepare("SELECT ID_Categ, nom_Categ, description_Categ, Categ_img FROM Categorie ORDER BY nom_Categ ASC");
    $req->execute();
    $categories = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories — Green Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link href="Categories.css" rel="stylesheet">
</head>
<body>

<?php render_navbar('logo'); ?>

<main class='pb-5'>

    <!-- En-tête -->
    <div class="page-header text-center py-5">
        <h2 class="mb-0">Nos Catégories</h2>
        <small class="text-muted-ink">Cliquez sur une catégorie pour voir ses produits</small>
    </div>
    <div class="container">
        <!-- Grille des catégories -->
        <div class="row g-4 justify-content-center mt-4" id="categoriesGrid">

            <?php foreach ($categories as $cat) {
                $id = $cat['ID_Categ'];
                $nom = $cat['nom_Categ'];
                $desc = $cat['description_Categ'] ?? '';

                if (!empty($cat['Categ_img'])) {
                    $img_html = '<img src="' . $cat['Categ_img'] . '" alt="' . $nom . '" class="categ-img">';
                } else {
                    $img_html = '<div class="categ-logo-circle"><span class="categ-logo-text">GM</span></div>';
                }
            ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="../Produits/Produits.php?categ=<?= $id ?>" class="categ-card-link">
                        <div class="categ-card">
                            <div class="categ-visual">
                                <?= $img_html ?>
                                <span class="categ-badge">Voir</span>
                            </div>
                            <div class="categ-body">
                                <h6 class="categ-name"><?= htmlspecialchars($nom) ?></h6>
                                <?php if (!empty($desc)): ?>
                                    <p class="categ-desc"><?= htmlspecialchars($desc) ?></p>
                                <?php endif; ?>
                                <span class="categ-count">← Découvrir</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>

        </div>
    </div>
</main>

<?php render_footer(); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="Categories.js"></script>
</body>
</html>