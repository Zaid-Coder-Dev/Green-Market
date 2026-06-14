<?php
session_start();
require_once '../connexion.php';

if (!isset($_SESSION['id_utili'])) {
    header('Location: ../Inscription/Inscription.php');
    exit();
}

$id = $_SESSION['id_utili'];

try {

    $req = $pdo->prepare("SELECT * FROM Utilisateur WHERE ID_utili = ?");
    $req->execute([$id]);
    $user = $req->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Utilisateur introuvable.");
    }

    $req = $pdo->prepare("SELECT * FROM Commande WHERE ID_utili = ? ORDER BY date_com DESC");
    $req->execute([$id]);
    $commandes = $req->fetchAll(PDO::FETCH_ASSOC);

    $nb_commandes = count($commandes);

    $total_depense = 0;
    foreach ($commandes as $c) {
        $total_depense += $c['prix_total'];
    }

    $req = $pdo->prepare("SELECT COUNT(*) as nb FROM Avis WHERE ID_utili = ?");
    $req->execute([$id]);
    $nb_avis = $req->fetch(PDO::FETCH_ASSOC)['nb'];

    $req = $pdo->prepare("
        SELECT a.*, p.nom_Prod
        FROM Avis a
        JOIN Produit p ON a.ID_Prod = p.ID_Prod
        WHERE a.ID_utili = ?
        ORDER BY a.date_avis DESC
    ");
    $req->execute([$id]);
    $avis = $req->fetchAll(PDO::FETCH_ASSOC);

    $req = $pdo->prepare("
        SELECT p.*, c.nom_Categ, b.nom_boutique,
               ROUND(AVG(av.note) * 2) / 2 AS note_moyenne,
               COUNT(av.ID_Avis) AS nb_avis
        FROM Favoris f
        JOIN Produit p ON f.ID_Prod = p.ID_Prod
        JOIN Categorie c ON p.ID_Categ = c.ID_Categ
        JOIN Boutique b ON p.ID_boutique = b.ID_boutique
        LEFT JOIN Avis av ON av.ID_Prod = p.ID_Prod
        WHERE f.ID_utili = ?
        GROUP BY p.ID_Prod, c.nom_Categ, b.nom_boutique
    ");
    $req->execute([$id]);
    $favoris = $req->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}

$initiale = strtoupper(mb_substr($user['prenom'], 0, 1));

// Sauvegarder profil
$success_profil = '';
$error_profil = '';

$err_profil = [];
$success_profil = '';

if (isset($_POST['save_profil'])) {
    extract($_POST);

    if (empty($nom) || !isset($nom)) {
        $err_profil["nom"] = "Le nom est obligatoire.";
    }
    if (empty($prenom) || !isset($prenom)) {
        $err_profil["prenom"] = "Le prénom est obligatoire.";
    }
    if (empty($email) || !isset($email)) {
        $err_profil["email"] = "L'email est obligatoire.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err_profil["email"] = "Format de l'email invalide.";
    }

    if (empty($err_profil)) {
        try {
            $req = $pdo->prepare("SELECT ID_utili FROM Utilisateur WHERE email = ? AND ID_utili != ?");
            $req->execute([$email, $id]);
            $existing = $req->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $err_profil["email"] = "Cet email est déjà utilisé par un autre compte.";
            } else {
                $req = $pdo->prepare("UPDATE Utilisateur SET nom=?, prenom=?, email=?, rue=?, ville=? WHERE ID_utili=?");
                $req->execute([$nom, $prenom, $email, $rue, $ville, $id]);
                $success_profil = "Profil mis à jour avec succès.";

                $req = $pdo->prepare("SELECT * FROM Utilisateur WHERE ID_utili = ?");
                $req->execute([$id]);
                $user = $req->fetch(PDO::FETCH_ASSOC);
                $initiale = strtoupper(mb_substr($user['prenom'], 0, 1));
            }
        } catch (PDOException $e) {
            $err_profil["db"] = "Erreur : " . $e->getMessage();
        }
    }
}

// Changer mot de passe
$err_mdp = [];
$success_mdp = '';

if (isset($_POST['save_mdp'])) {
    extract($_POST);

    if (empty($ancien_mdp) || !isset($ancien_mdp)) {
        $err_mdp["ancien_mdp"] = "L'ancien mot de passe est obligatoire.";
    }
    if (empty($nouveau_mdp) || !isset($nouveau_mdp)) {
        $err_mdp["nouveau_mdp"] = "Le nouveau mot de passe est obligatoire.";
    } else if (strlen($nouveau_mdp) < 6) {
        $err_mdp["nouveau_mdp"] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    if (empty($confirm_mdp) || !isset($confirm_mdp)) {
        $err_mdp["confirm_mdp"] = "Veuillez confirmer le mot de passe.";
    } else if ($nouveau_mdp != $confirm_mdp) {
        $err_mdp["confirm_mdp"] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($err_mdp)) {
        if (!password_verify($ancien_mdp, $user['mot_de_passe'])) {
            $err_mdp["ancien_mdp"] = "Ancien mot de passe incorrect.";
        } else {
            try {
                $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
                $req = $pdo->prepare("UPDATE Utilisateur SET mot_de_passe=? WHERE ID_utili=?");
                $req->execute([$hash, $id]);
                $success_mdp = "Mot de passe modifié avec succès.";
            } catch (PDOException $e) {
                $err_mdp["db"] = "Erreur : " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mon Profil – Green Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="Profile-client.css" rel="stylesheet" />
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
                <li class="nav-item"><a class="nav-link" href="../Produits/Produits.php">Boutique</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['id_utili'])) { ?>
                    <a href="../Panier/Panier.php" class="position-relative text-decoration-none nav-icon">
                <?php } else { ?>
                    <a href="../Inscription/Inscription.php" class="position-relative text-decoration-none nav-icon">
                <?php } ?>
                    <i class="bi bi-cart3"></i>
                    <span class="cart-badge" id="cart-count">0</span>
                </a>
                <a href="#" class="position-relative text-decoration-none nav-icon">
                    <i class="bi bi-bell"></i>
                    <span class="cart-badge" id="bell-count">0</span>
                </a>
                <?php if (isset($_SESSION['id_utili'])) { ?>
                    <a href="../Profile-client/Profile-client.php" class="position-relative text-decoration-none nav-icon">
                <?php } else { ?>
                    <a href="../Inscription/Inscription.php" class="position-relative text-decoration-none nav-icon">
                <?php } ?>
                    <i class="bi bi-person"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- CONTENU -->
<div class="container py-5">
    <div class="row g-4 align-items-start">

        <!-- SIDEBAR -->
        <div class="col-12 col-md-3">
            <div class="profile-sidebar p-3">

                <div class="text-center mb-4">
                    <div class="avatar mx-auto mb-3"><?= $initiale ?></div>
                    <h6 class="fw-bold mb-0"><?= $user['prenom'] ?> <?= $user['nom'] ?></h6>
                    <small class="text-muted-ink">Client</small>
                </div>

                <hr class="sidebar-divider">

                <div class="d-flex flex-column gap-1">
                    <button class="sidebar-link active" data-section="dashboard">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </button>
                    <button class="sidebar-link" data-section="profil">
                        <i class="bi bi-person me-2"></i>Mon Profil
                    </button>
                    <button class="sidebar-link" data-section="commandes">
                        <i class="bi bi-bag me-2"></i>Mes Commandes
                    </button>
                    <button class="sidebar-link" data-section="avis">
                        <i class="bi bi-star me-2"></i>Mes Avis
                    </button>
                    <button class="sidebar-link" data-section="favoris">
                        <i class="bi bi-heart me-2"></i>Mes Favoris
                    </button>
                </div>

                <hr class="sidebar-divider">

                <a href="../deconnexion.php" class="log-out">
                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                </a>

            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-12 col-md-9">

            <!-- DASHBOARD -->
            <div id="dashboard" class="section">
                <h4 class="mb-1">Bonjour, <?= $user['prenom'] ?> !</h4>
                <p class="text-muted-ink mb-4" style="font-size:0.9rem;">Bienvenue sur votre espace personnel.</p>

                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
                            <div class="stat-value"><?= $nb_commandes ?></div>
                            <div class="stat-label">Commandes</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                            <div class="stat-value"><?= number_format($total_depense, 0) ?></div>
                            <div class="stat-label">MAD dépensés</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-star"></i></div>
                            <div class="stat-value"><?= $nb_avis ?></div>
                            <div class="stat-label">Avis laissés</div>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <h6 class="content-card-title mb-3">Dernières commandes</h6>
                    <?php if (count($commandes) == 0) { ?>
                        <p class="text-muted-ink text-center py-3">Aucune commande pour l'instant.</p>
                    <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr class="table-header-row">
                                    <th>N° Commande</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent = array_slice($commandes, 0, 4);
                                foreach ($recent as $c) {
                                ?>
                                <tr>
                                    <td class="fw-bold">#<?= $c['ID_Com'] ?></td>
                                    <td><?= date('d M Y', strtotime($c['date_com'])) ?></td>
                                    <td class="price-text fw-bold"><?= number_format($c['prix_total'], 2) ?> MAD</td>
                                    <td>
                                        <?php if ($c['status_com'] == 'livré') { ?>
                                            <span class="badge-statut livré">Livré</span>
                                        <?php } else if ($c['status_com'] == 'en attente') { ?>
                                            <span class="badge-statut encours">En attente</span>
                                        <?php } else if ($c['status_com'] == 'en cours') { ?>
                                            <span class="badge-statut encours">En cours</span>
                                        <?php } else if ($c['status_com'] == 'annulé') { ?>
                                            <span class="badge-statut annulé">Annulé</span>
                                        <?php } else { ?>
                                            <span class="badge-statut encours"><?= $c['status_com'] ?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- MON PROFIL -->
            <div id="profil" class="section d-none">
                <h4 class="mb-4">Mon Profil</h4>

                <?php if ($success_profil != '') { ?>
                    <div class="alert-success-custom mb-3"><?= $success_profil ?></div>
                <?php } ?>
                <?php if (isset($err_profil["db"])) { ?>
                    <div class="alert-error-custom mb-3"><?= $err_profil["db"] ?></div>
                <?php } ?>

                <div class="content-card mb-4">
                    <h6 class="content-card-title mb-4">Informations personnelles</h6>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nom</label>
                                <?php if (isset($err_profil["nom"])) echo "<small class='text-danger d-block mb-1'>{$err_profil['nom']}</small>"; ?>
                                <input type="text" name="nom" class="form-control form-control-profile" value="<?= $user['nom'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Prénom</label>
                                <?php if (isset($err_profil["prenom"])) echo "<small class='text-danger d-block mb-1'>{$err_profil['prenom']}</small>"; ?>
                                <input type="text" name="prenom" class="form-control form-control-profile" value="<?= $user['prenom'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email</label>
                                <?php if (isset($err_profil["email"])) echo "<small class='text-danger d-block mb-1'>{$err_profil['email']}</small>"; ?>
                                <input type="email" name="email" class="form-control form-control-profile" value="<?= $user['email'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Ville</label>
                                <input type="text" name="ville" class="form-control form-control-profile" value="<?= $user['ville'] ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Rue / Adresse</label>
                                <input type="text" name="rue" class="form-control form-control-profile" value="<?= $user['rue'] ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" name="save_profil" class="btn btn-save text-white">Sauvegarder</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="content-card">
                    <h6 class="content-card-title mb-4">Changer le mot de passe</h6>

                    <?php if ($success_mdp != '') { ?>
                        <div class="alert-success-custom mb-3"><?= $success_mdp ?></div>
                    <?php } ?>
                    <?php if (isset($err_mdp["db"])) { ?>
                        <div class="alert-error-custom mb-3"><?= $err_mdp["db"] ?></div>
                    <?php } ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ancien mot de passe</label>
                                <?php if (isset($err_mdp["ancien_mdp"])) echo "<small class='text-danger d-block mb-1'>{$err_mdp['ancien_mdp']}</small>"; ?>
                                <input type="password" name="ancien_mdp" class="form-control form-control-profile" placeholder="••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nouveau</label>
                                <?php if (isset($err_mdp["nouveau_mdp"])) echo "<small class='text-danger d-block mb-1'>{$err_mdp['nouveau_mdp']}</small>"; ?>
                                <input type="password" name="nouveau_mdp" class="form-control form-control-profile" placeholder="••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Confirmer</label>
                                <?php if (isset($err_mdp["confirm_mdp"])) echo "<small class='text-danger d-block mb-1'>{$err_mdp['confirm_mdp']}</small>"; ?>
                                <input type="password" name="confirm_mdp" class="form-control form-control-profile" placeholder="••••••">
                            </div>
                            <div class="col-12">
                                <button type="submit" name="save_mdp" class="btn btn-save text-white">Modifier</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MES COMMANDES -->
            <div id="commandes" class="section d-none">
                <h4 class="mb-4">Mes Commandes</h4>
                <div class="content-card">
                    <?php if (count($commandes) == 0) { ?>
                        <p class="text-muted-ink text-center py-3">Aucune commande pour l'instant.</p>
                    <?php } else { ?>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead>
                                <tr class="table-header-row">
                                    <th>N° Commande</th>
                                    <th>Date</th>
                                    <th>Ville livraison</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($commandes as $c) { ?>
                                <tr>
                                    <td class="fw-bold">#<?= $c['ID_Com'] ?></td>
                                    <td><?= date('d M Y', strtotime($c['date_com'])) ?></td>
                                    <td><?= $c['ville_livraison'] ? $c['ville_livraison'] : '—' ?></td>
                                    <td class="price-text fw-bold"><?= number_format($c['prix_total'], 2) ?> MAD</td>
                                    <td>
                                        <?php if ($c['status_com'] == 'livré') { ?>
                                            <span class="badge-statut livré">Livré</span>
                                        <?php } else if ($c['status_com'] == 'en attente') { ?>
                                            <span class="badge-statut encours">En attente</span>
                                        <?php } else if ($c['status_com'] == 'en cours') { ?>
                                            <span class="badge-statut encours">En cours</span>
                                        <?php } else if ($c['status_com'] == 'annulé') { ?>
                                            <span class="badge-statut annulé">Annulé</span>
                                        <?php } else { ?>
                                            <span class="badge-statut encours"><?= $c['status_com'] ?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- MES AVIS -->
            <div id="avis" class="section d-none">
                <h4 class="mb-4">Mes Avis</h4>
                <div class="content-card">
                    <?php if (count($avis) == 0) { ?>
                        <p class="text-muted-ink text-center py-3">Vous n'avez laissé aucun avis pour l'instant.</p>
                    <?php } else { ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($avis as $index => $av) { ?>
                            <?php if ($index > 0) { ?><hr class="avis-divider"><?php } ?>
                            <div class="avis-item">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0"><?= $av['nom_Prod'] ?></h6>
                                    <small class="text-muted-ink"><?= date('d M Y', strtotime($av['date_avis'])) ?></small>
                                </div>
                                <div class="stars-display mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                        <?php if ($i <= $av['note']) { ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php } else { ?>
                                            <i class="bi bi-star"></i>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                                <p class="text-muted-ink mb-0" style="font-size:0.875rem;"><?= $av['commentaire'] ?></p>
                            </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- MES FAVORIS -->
            <div id="favoris" class="section d-none">
                <h4 class="mb-4">Mes Favoris</h4>
                <?php if (count($favoris) == 0) { ?>
                    <div class="content-card">
                        <p class="text-muted-ink text-center py-3">Aucun favori pour l'instant.</p>
                    </div>
                <?php } else { ?>
                <div class="row g-3">
                    <?php foreach ($favoris as $p) { ?>
                    <div class="col-6 col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="img-wrapper">
                                <img src="../uploads/produits/<?= $p['Prod_img'] ?>" class="card-img-top product-img" alt="<?= $p['nom_Prod'] ?>">
                            </div>
                            <div class="card-body d-flex flex-column">
                                <small class="text-muted-ink"><?= $p['nom_Categ'] ?></small>
                                <h6 class="card-title mt-1 mb-1"><?= $p['nom_Prod'] ?></h6>
                                <small class="boutique-name mb-1"><?= $p['nom_boutique'] ?></small>
                                <?php
                                $note = $p['note_moyenne'] ? (float)$p['note_moyenne'] : 0;
                                $nb   = (int)$p['nb_avis'];
                                ?>
                                <div class="stars-display mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                        <?php if ($note >= $i) { ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php } else if ($note >= $i - 0.5) { ?>
                                            <i class="bi bi-star-half"></i>
                                        <?php } else { ?>
                                            <i class="bi bi-star"></i>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($nb > 0) { ?>
                                        <small class="text-muted-ink ms-1">(<?= $nb ?>)</small>
                                    <?php } ?>
                                </div>
                                <p class="fw-bold price-text mb-3 mt-auto"><?= number_format($p['Prix'], 2) ?> MAD</p>
                                <div class="d-flex gap-2">
                                    <a href="../Produit details/Produit details.php?id=<?= $p['ID_Prod'] ?>" class="btn btn-sm w-75 btn-voir">Voir</a>
                                    <button class="btn btn-sm btn-add w-25" data-id="<?= $p['ID_Prod'] ?>"><i class="bi bi-cart"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>

        </div>
    </div>
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
<script src="Profile-client.js"></script>
</body>
</html>
