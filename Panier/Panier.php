<?php
session_start();
require_once '../connexion.php';
require_once '../functions.php';

require_login();

$id_utili = $_SESSION['id_utili'];

// PANIER DE L'UTILISATEUR
try {
    $req1 = $pdo->prepare("SELECT * FROM Panier WHERE ID_utili = ?");
    $req1->execute([$id_utili]);
    $panier = $req1->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

$id_panier = 0;
if ($panier) {
    $id_panier = $panier['ID_Panier'];
}

// MODIFIER QUANTITE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'modifier_qte') {
    $id_prod_qte = 0;
    if (isset($_POST['id_prod'])) {
        $id_prod_qte = $_POST['id_prod'];
    }
    $nouvelle_qte = 1;
    if (isset($_POST['quantite'])) {
        $nouvelle_qte = (int)$_POST['quantite'];
    }

    if ($nouvelle_qte >= 1 && $id_panier > 0) {
        try {
            $req_maj = $pdo->prepare("UPDATE Ligne_panier SET Quantite = ? WHERE ID_Panier = ? AND ID_Prod = ?");
            $req_maj->execute([$nouvelle_qte, $id_panier, $id_prod_qte]);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
        header('Location: Panier.php');
        exit();
    }
}

// SUPPRIMER UN ARTICLE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'supprimer_article') {
    $id_prod_sup = 0;
    if (isset($_POST['id_prod'])) {
        $id_prod_sup = $_POST['id_prod'];
    }

    if ($id_panier > 0) {
        try {
            $req_sup = $pdo->prepare("DELETE FROM Ligne_panier WHERE ID_Panier = ? AND ID_Prod = ?");
            $req_sup->execute([$id_panier, $id_prod_sup]);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
        header('Location: Panier.php');
        exit();
    }
}

// ARTICLES DU PANIER
$articles = [];
if ($id_panier > 0) {
    try {
        $req2 = $pdo->prepare("
            SELECT lp.Quantite, p.ID_Prod, p.nom_Prod, p.Prix, p.Prod_img, c.nom_Categ
            FROM Ligne_panier lp
            JOIN Produit p ON lp.ID_Prod = p.ID_Prod
            JOIN Categorie c ON p.ID_Categ = c.ID_Categ
            WHERE lp.ID_Panier = ?
        ");
        $req2->execute([$id_panier]);
        $articles = $req2->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}

$nb_articles_total = 0;
$sous_total = 0;
foreach ($articles as $art) {
    $sous_total += $art['Prix'] * $art['Quantite'];
    $nb_articles_total += $art['Quantite'];
}
$total = $sous_total;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green Market - Panier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="Panier.css" rel="stylesheet">
</head>

<body>

    <!-- NAVBAR -->
    <?php render_navbar('logo'); ?>

    <!-- TITRE -->
    <section class="py-4 page-header">
        <div class="container">
            <h2 class="mb-0">Mon Panier</h2>
            <small class="text-muted-ink"><?= $nb_articles_total ?> article<?= $nb_articles_total > 1 ? 's' : '' ?></small>
        </div>
    </section>

    <!-- CONTENU -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">

                <!-- LISTE DES PRODUITS -->
                <div class="col-12 col-md-8">
                    <div class="content-card">
                        <?php
                        if (count($articles) == 0) {
                            echo '<p class="text-muted-ink text-center py-4">Votre panier est vide.</p>';
                        } else {
                            $nb_articles = count($articles);
                            $i = 0;
                            foreach ($articles as $art) {
                                $i++;
                                echo '
                        <div class="cart-item d-flex align-items-center gap-3">
                            <img src="' . $art['Prod_img'] . '" class="cart-img" alt="' . $art['nom_Prod'] . '">
                            <div class="flex-grow-1">
                                <small class="text-muted-ink">' . $art['nom_Categ'] . '</small>
                                <h6 class="mb-1">' . $art['nom_Prod'] . '</h6>
                                <p class="price-text fw-bold mb-0">' . number_format($art['Prix'], 2) . ' MAD</p>
                            </div>
                            <form method="POST" action="Panier.php" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="action" value="modifier_qte">
                                <input type="hidden" name="id_prod" value="' . $art['ID_Prod'] . '">
                                <button type="submit" name="quantite" value="' . ($art['Quantite'] - 1) . '" class="btn btn-sm btn-qty">−</button>
                                <span class="qty">' . $art['Quantite'] . '</span>
                                <button type="submit" name="quantite" value="' . ($art['Quantite'] + 1) . '" class="btn btn-sm btn-qty">+</button>
                            </form>
                            <form method="POST" action="Panier.php">
                                <input type="hidden" name="action" value="supprimer_article">
                                <input type="hidden" name="id_prod" value="' . $art['ID_Prod'] . '">
                                <button type="submit" class="btn btn-sm text-danger btn-remove">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>';
                                if ($i < $nb_articles) {
                                    echo '<hr>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- RÉSUMÉ -->
                <div class="col-12 col-md-4">
                    <div class="content-card sticky-top">
                        <h5 class="fw-bold mb-4 ">Résumé de la commande</h5>

                        <?php
                        foreach ($articles as $art) {
                            echo '
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-ink small">' . $art['nom_Prod'] . ' x' . $art['Quantite'] . '</span>
                            <span class="small">' . number_format($art['Prix'] * $art['Quantite'], 2) . ' MAD</span>
                        </div>';
                        }
                        ?>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-ink small">Sous-total</span>
                            <span class="small"><?= number_format($sous_total, 2) ?> MAD</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted-ink small">Livraison</span>
                            <span class="small price-text">Gratuite</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold price-text"><?= number_format($total, 2) ?> MAD</span>
                        </div>

                        <?php
                        if (count($articles) > 0) {
                            echo '<a href="../Paiement/Paiement.php" class="btn w-100 text-white btn-add mb-2">Commander</a>';
                        }
                        ?>
                        <a href="../Produits/Produits.php" class="btn w-100 btn-retour">Continuer mes achats</a>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php render_footer(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Panier.js"></script>
</body>

</html>