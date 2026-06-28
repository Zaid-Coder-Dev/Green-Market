<?php
session_start();
require_once '../connexion.php';
require_once '../functions.php';


// PRODUCT ID
$id_prod = 0;
if (isset($_GET['id'])) {
    $id_prod = $_GET['id'];
}

if ($id_prod == 0) {
    die("Produit introuvable.");
}

// ROLE CHECK
if (isset($_SESSION['id_utili']) && $_SESSION['role'] == 'client') {
    $btn_class = '';
} else {
    $btn_class = 'd-none';
}
// FILL the heart
$est_favori = false;

if (isset($_SESSION['id_utili']) && $_SESSION['role'] == 'client') {
    $req = $pdo->prepare("SELECT 1 FROM Favoris WHERE ID_utili=? AND ID_Prod=?");
    $req->execute([$_SESSION['id_utili'], $id_prod]);

    if ($req->fetch()) {
        $est_favori = true;
    }
}


// TOGGLE FAVORIS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'favoris') {
    if (isset($_SESSION['id_utili']) && $_SESSION['role'] == 'client') {
        try {
            $req_fav = $pdo->prepare("SELECT * FROM Favoris WHERE ID_utili = ? AND ID_Prod = ?");
            $req_fav->execute([$_SESSION['id_utili'], $id_prod]);
            $favori = $req_fav->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }

        if ($favori) {
            try {
                $req_del = $pdo->prepare("DELETE FROM Favoris WHERE ID_utili = ? AND ID_Prod = ?");
                $req_del->execute([$_SESSION['id_utili'], $id_prod]);
            } catch (PDOException $e) {
                die("Erreur : " . $e->getMessage());
            }
        } else {
            try {
                $req_ins_fav = $pdo->prepare("INSERT INTO Favoris (ID_utili, ID_Prod) VALUES (?, ?)");
                $req_ins_fav->execute([$_SESSION['id_utili'], $id_prod]);
            } catch (PDOException $e) {
                die("Erreur : " . $e->getMessage());
            }
        }
    }
    header('Location: Produit details.php?id=' . $id_prod);
    exit();
}

// SUBMIT AVIS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'avis') {
    $note = 0;
    if (isset($_POST['note'])) {
        $note = (int)$_POST['note'];
    }
    $commentaire = '';
    if (isset($_POST['commentaire'])) {
        $commentaire = trim($_POST['commentaire']);
    }

    if ($note >= 1 && $note <= 5 && $commentaire != '') {
        try {
            $req_ins = $pdo->prepare("INSERT INTO Avis (note, commentaire, ID_utili, ID_Prod) VALUES (?, ?, ?, ?)");
            $req_ins->execute([$note, $commentaire, $_SESSION['id_utili'], $id_prod]);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
        header('Location: Produit details.php?id=' . $id_prod . '#avis');
        exit();
    }
}

// PRODUIT
try {
    $req1 = $pdo->prepare("
        SELECT p.*, c.nom_Categ, b.nom_boutique
        FROM Produit p
        JOIN Categorie c ON p.ID_Categ = c.ID_Categ
        JOIN Boutique b ON p.ID_boutique = b.ID_boutique
        WHERE p.ID_Prod = ?
    ");
    $req1->execute([$id_prod]);
    $produit = $req1->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

if (!$produit) {
    die("Produit introuvable.");
}

// GALERIE IMAGES
try {
    $req_imgs = $pdo->prepare("SELECT * FROM Produit_image WHERE ID_Prod = ?");
    $req_imgs->execute([$id_prod]);
    $galerie = $req_imgs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// AVIS
try {
    $req2 = $pdo->prepare("
        SELECT a.*, u.nom, u.prenom
        FROM Avis a
        JOIN Utilisateur u ON a.ID_utili = u.ID_utili
        WHERE a.ID_Prod = ?
        ORDER BY a.date_avis DESC
    ");
    $req2->execute([$id_prod]);
    $avis = $req2->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

$note_moyenne = 0;
if (count($avis) > 0) {
    $total_notes = 0;
    foreach ($avis as $av) {
        $total_notes += $av['note'];
    }
    $note_moyenne = $total_notes / count($avis);
}

// SIMILAIRES
try {
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
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// NOUVEAU BADGE
function est_nouveau($date)
{
    if (!$date) {
        return false;
    }
    $diff = (time() - strtotime($date)) / 86400;
    return $diff < 30;
}

$est_nouveau = est_nouveau($produit['date_ajout_Prod']);
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
    <?php render_navbar('logo'); ?>

    <!-- PRODUCT -->
    <section class="container py-5">
        <div class="row g-5">

            <!-- IMAGE — clean, no badges -->
            <div class="col-lg-5">
                <div class="product-image">
                    <img id="mainImage" class="img-fluid rounded-4" src="<?= $produit['Prod_img'] ?>" alt="<?= $produit['nom_Prod'] ?>">
                </div>
                <div id="SMI" class="small-images mt-3">
                    <?php
                    $thumbs = '<img class="small-img active-img" src="' . $produit['Prod_img'] . '" alt="' . $produit['nom_Prod'] . '">';
                    foreach ($galerie as $img) {
                        $thumbs .= '<img class="small-img" src="' . $img['image'] . '" alt="' . $produit['nom_Prod'] . '">';
                    }
                    echo $thumbs;
                    ?>
                </div>
            </div>

            <!-- DETAILS -->
            <div class="col-lg-7">
                <p id="ctg" class="category"><?= $produit['nom_Categ'] ?></p>
                <h1 id="pro-tit" class="product-title"><?= $produit['nom_Prod'] ?></h1>

                <!-- STARS -->
                <div id="product-stars" class="stars mb-2">
                    <?php
                    $note = round($note_moyenne * 2) / 2;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= floor($note)) {
                            echo '<i class="bi bi-star-fill text-warning"></i>';
                        } else if ($i - $note < 1) {
                            echo '<i class="bi bi-star-half text-warning"></i>';
                        } else {
                            echo '<i class="bi bi-star text-warning"></i>';
                        }
                    }
                    ?>
                    <small class="text-muted-ink ms-1">(<?= count($avis) ?> avis)</small>
                </div>

                <!-- PRIX + BADGES -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <h2 id="prix-p" class="price mb-0"><?= number_format($produit['Prix'], 2) ?> MAD</h2>
                    <?php
                    if ($est_nouveau) {
                        echo '<span class="badge badge-nouveau rounded-pill px-3 py-2">Nouveau</span>';
                    }
                    if ($produit['Stock'] == 0) {
                        echo '<span class="badge badge-epuise rounded-pill px-3 py-2">Épuisé</span>';
                    }
                    ?>
                </div>

                <p id="des-p" class="product-desc"><?= $produit['description_Prod'] ?></p>
                <p class="mb-3"><small class="text-muted-ink"><i class="bi bi-shop me-1"></i><?= $produit['nom_boutique'] ?></small></p>

                <!-- QUANTITY -->
                <?php
                if ($btn_class == '') {
                    echo '
                <div class="quantity-box mb-4">
                    <button id="minusBtn">-</button>
                    <span id="quantity">1</span>
                    <button id="plusBtn">+</button>
                </div>';
                }
                ?>

                <!-- BUTTONS -->
                <?php
                if ($btn_class == '') {
    echo '<div class="product-actions mb-4">';

    if ($produit['Stock'] == 0) {
        echo '<button class="btn-ajouter" disabled style="opacity:0.5;cursor:not-allowed;">
            <i class="bi bi-cart3"></i> Stock épuisé
          </button>';
    } else {
        echo '<form method="POST" action="../ajouter_panier.php" id="formAjouterPanier">
                    <input type="hidden" name="id_prod" value="' . $produit['ID_Prod'] . '">
                    <input type="hidden" name="retour" value="Produit details/Produit details.php?id=' . $produit['ID_Prod'] . '">
                    <input type="hidden" name="quantite" id="inputQuantite" value="1">
                    <button type="submit" class="btn-ajouter">
                        <i class="bi bi-cart3"></i> Ajouter au panier
                    </button>
               </form>';
    }

    echo '<div class="d-flex align-items-center gap-3">';

    if ($produit['Stock'] == 0) {
        echo '<button class="buy-btn" disabled style="opacity:0.5;cursor:not-allowed;">Acheter maintenant</button>';
    } else {
        echo '<form method="GET" action="../Paiement/Paiement.php" id="formAcheterMaintenant">
                    <input type="hidden" name="id_prod" value="' . $produit['ID_Prod'] . '">
                    <input type="hidden" name="quantite" id="inputQuantiteBuy" value="1">
                    <button type="submit" class="buy-btn">Acheter maintenant</button>
                </form>';
    }

    echo '<form method="POST">
                <input type="hidden" name="action" value="favoris">
                <button type="submit" class="wishlist-btn">
                    <i class="bi ' . ($est_favori ? 'bi-heart-fill' : 'bi-heart') . '"></i>
                </button>
            </form>
        </div>
    </div>';
                }
                ?>
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
                        <img src="../uploads/traçacilite/Production.jpg" onerror="this.style.display='none'">
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
                        <img src="../uploads/traçacilite/Artisanat.jpg" onerror="this.style.display='none'">
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
                        <img src="../uploads/traçacilite/Emballage.jpg" onerror="this.style.display='none'">
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
                        <img src="../uploads/traçacilite/Livraison.jpg" onerror="this.style.display='none'">
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
    <section class="container mb-5" id="avis">
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
                        } else if ($i - $note < 1) {
                            echo '<i class="bi bi-star-half text-warning"></i>';
                        } else {
                            echo '<i class="bi bi-star text-warning"></i>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php
        if (count($avis) == 0) {
            echo '<p class="text-muted-ink">Aucun avis pour ce produit.</p>';
        }

        foreach ($avis as $av) {
            $initiale = strtoupper(substr($av['prenom'], 0, 1));
            $nom_affiche = $av['prenom'] . ' ' . strtoupper(substr($av['nom'], 0, 1)) . '.';
            $date_avis = date('d/m/Y', strtotime($av['date_avis']));

            $etoiles = '';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $av['note']) {
                    $etoiles .= '<i class="bi bi-star-fill"></i>';
                } else {
                    $etoiles .= '<i class="bi bi-star"></i>';
                }
            }

            echo '
        <div class="review-box d-flex align-items-start mb-3">
            <div class="review-avatar me-3">' . $initiale . '</div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div>
                        <h6 class="mb-0">' . $nom_affiche . '</h6>
                        <small class="text-muted-ink">' . $date_avis . '</small>
                    </div>
                    <div class="review-stars">' . $etoiles . '</div>
                </div>
                <p class="mb-0">' . $av['commentaire'] . '</p>
            </div>
        </div>';
        }
        ?>
    </section>

    <!-- COMMENT FORM -->
    <?php
    if ($btn_class == '') {
        echo '
    <section class="container mb-5">
        <h4 class="fw-bold mb-3">Ajouter un avis</h4>
        <div class="card comment-form-card mb-4">
            <form method="POST" action="Produit details.php?id=' . $id_prod . '">
                <input type="hidden" name="action" value="avis">
                <input type="hidden" name="note" id="commentRating" value="0">
                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <div id="starRating" class="star-rating">
                        <i class="bi bi-star" data-value="1"></i>
                        <i class="bi bi-star" data-value="2"></i>
                        <i class="bi bi-star" data-value="3"></i>
                        <i class="bi bi-star" data-value="4"></i>
                        <i class="bi bi-star" data-value="5"></i>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Commentaire</label>
                    <textarea class="form-control" name="commentaire" rows="3" placeholder="Écrivez votre avis..."></textarea>
                </div>
                <button type="submit" class="comment-btn">Publier l\'avis</button>
            </form>
        </div>
    </section>';
    }
    ?>

    <!-- VOUS AIMEREZ AUSSI -->
    <?php
    if (count($similaires) > 0) {
        echo '<section class="container mt-5 mb-5">';
        echo '<h4 class="fw-bold mb-4">Vous aimerez aussi</h4>';
        echo '<div class="row g-4">';

        foreach ($similaires as $s) {
            $est_nouveau_s = est_nouveau($s['date_ajout_Prod']);

            $badges = '';
            if ($est_nouveau_s) {
                $badges .= '<span class="badge badge-nouveau">Nouveau</span>';
            }
            if ($s['Stock'] == 0) {
                $badges .= '<span class="badge badge-epuise">Épuisé</span>';
            }

            $voir_class = ($btn_class == 'd-none') ? 'w-100' : 'w-75';

        echo '
<div class="col-6 col-md-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="badges-wrapper">' . $badges . '</div>
        <div class="img-wrapper">
            <img src="' . $s['Prod_img'] . '" class="card-img-top product-img" alt="' . $s['nom_Prod'] . '">
        </div>
        <div class="card-body d-flex flex-column">
            <small class="text-muted-ink">' . $s['nom_Categ'] . '</small>
            <h6 class="card-title mt-1 mb-1">' . $s['nom_Prod'] . '</h6>
            <small class="boutique-name mb-2">' . $s['nom_boutique'] . '</small>
            <p class="fw-bold price-text mb-3 mt-auto">' . number_format($s['Prix'], 2) . ' MAD</p>
            <div class="d-flex gap-2">
                <a href="Produit details.php?id=' . $s['ID_Prod'] . '" class="btn btn-sm btn-voir ' . $voir_class . '">Voir</a>

                <button class="btn btn-sm btn-add w-25 ' . $btn_class . '" data-id="' . $s['ID_Prod'] . '">
    <i class="bi bi-cart"></i>
</button>
            </div>
        </div>
    </div>
</div>';
        }

        echo '</div></section>';
    }
    ?>

    <!-- FOOTER -->
    <?php render_footer(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Panier_handler.js"></script>
    <script src="Produit details.js"></script>
</body>

</html>