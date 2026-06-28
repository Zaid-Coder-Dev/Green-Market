<?php
session_start();
require_once '../connexion.php';
require_once '../functions.php';

require_login();

$id = $_SESSION['id_utili'];

try {

    $req = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_utili = ?");
    $req->execute([$id]);
    $user = $req->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Utilisateur introuvable.");
    }

    $req = $pdo->prepare("SELECT * FROM Commande WHERE id_utili = ? ORDER BY date_com DESC");
    $req->execute([$id]);
    $commandes = $req->fetchAll(PDO::FETCH_ASSOC);

    $nb_commandes = count($commandes);

    $total_depense = 0;
    foreach ($commandes as $c) {
        $total_depense += $c['prix_total'];
    }

    $req = $pdo->prepare("SELECT COUNT(*) as nb FROM Avis WHERE id_utili = ?");
    $req->execute([$id]);
    $nb_avis = $req->fetch(PDO::FETCH_ASSOC)['nb'];

    $req = $pdo->prepare("
        SELECT a.*, p.nom_Prod
        FROM Avis a
        JOIN Produit p ON a.ID_Prod = p.ID_Prod
        WHERE a.id_utili = ?
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
        WHERE f.id_utili = ?
        GROUP BY p.ID_Prod, c.nom_Categ, b.nom_boutique
    ");
    $req->execute([$id]);
    $favoris = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}

$initiale = strtoupper(mb_substr($user['prenom'], 0, 1));

// Modifier avis
$success_avis = '';
$err_avis = [];
if (isset($_GET['ok']) && $_GET['ok'] == 'avis_modifie')  { $success_avis = "Votre avis a été modifié."; }
if (isset($_GET['ok']) && $_GET['ok'] == 'avis_supprime') { $success_avis = "Votre avis a été supprimé."; }

if (isset($_POST['modifier_avis'])) {
    $id_avis   = $_POST['id_avis'];
    $note_new  = $_POST['note_new'];
    $comm_new  = $_POST['commentaire_new'];

    if (empty($note_new) || $note_new < 1 || $note_new > 5) {
        $err_avis["note"] = "La note doit être entre 1 et 5.";
    }
    if (empty($comm_new)) {
        $err_avis["commentaire"] = "Le commentaire est obligatoire.";
    }

    if (empty($err_avis)) {
        try {
            $req = $pdo->prepare("UPDATE Avis SET note=?, commentaire=?, date_avis=NOW() WHERE ID_Avis=? AND id_utili=?");
            $req->execute([$note_new, $comm_new, $id_avis, $id]);
            header('Location: Profile-client.php?ok=avis_modifie');
            exit();
        } catch (PDOException $e) {
            $err_avis["db"] = "Erreur : " . $e->getMessage();
        }
    }
}

// Supprimer avis

if (isset($_POST['supprimer_avis'])) {
    $id_avis = $_POST['id_avis'];
    try {
        $req = $pdo->prepare("DELETE FROM Avis WHERE ID_Avis=? AND id_utili=?");
        $req->execute([$id_avis, $id]);
        header('Location: Profile-client.php?ok=avis_supprime');
        exit();
    } catch (PDOException $e) {
        $err_avis["db"] = "Erreur : " . $e->getMessage();
    }
}

// Signaler une réclamation
$success_reclam = '';
$err_reclam = [];
if (isset($_GET['ok']) && $_GET['ok'] == 'reclam') { $success_reclam = "Votre réclamation a été envoyée."; }

if (isset($_POST['signaler_reclam'])) {
    $id_com      = $_POST['id_com'];
    $description = $_POST['description'];

    if (empty($description)) {
        $err_reclam["description"] = "La description est obligatoire.";
    }

    if (empty($err_reclam)) {
        try {
            $req = $pdo->prepare("INSERT INTO Reclamation (description, status_reclam, ID_Com, id_utili) VALUES (?, 'ouverte', ?, ?)");
            $req->execute([$description, $id_com, $id]);
            header('Location: Profile-client.php?ok=reclam');
            exit();
        } catch (PDOException $e) {
            $err_reclam["db"] = "Erreur : " . $e->getMessage();
        }
    }
}

// Sauvegarder profil
$err_profil = [];
$success_profil = '';

if (isset($_GET['ok']) && $_GET['ok'] == 'profil') { $success_profil = "Profil mis à jour avec succès."; }

if (isset($_POST['save_profil'])) {
    $nom    = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email  = $_POST['email'];
    $ville  = $_POST['ville'];
    $rue    = $_POST['rue'];

    if (empty($nom))    { $err_profil["nom"]    = "Le nom est obligatoire."; }
    if (empty($prenom)) { $err_profil["prenom"] = "Le prénom est obligatoire."; }
    if (empty($email))  { $err_profil["email"]  = "L'email est obligatoire."; }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $err_profil["email"] = "Format de l'email invalide."; }

    if (empty($err_profil)) {
        try {
            $req = $pdo->prepare("SELECT id_utili FROM Utilisateur WHERE email = ? AND id_utili != ?");
            $req->execute([$email, $id]);
            $existing = $req->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $err_profil["email"] = "Cet email est déjà utilisé par un autre compte.";
            } else {
                $req = $pdo->prepare("UPDATE Utilisateur SET nom=?, prenom=?, email=?, rue=?, ville=? WHERE id_utili=?");
                $req->execute([$nom, $prenom, $email, $rue, $ville, $id]);
                header('Location: Profile-client.php?ok=profil');
                exit();
            }
        } catch (PDOException $e) {
            $err_profil["db"] = "Erreur : " . $e->getMessage();
        }
    }
}

// Changer mot de passe
$err_mdp = [];
$success_mdp = '';

if (isset($_GET['ok']) && $_GET['ok'] == 'mdp') { $success_mdp = "Mot de passe modifié avec succès."; }

if (isset($_POST['save_mdp'])) {
    $ancien_mdp  = $_POST['ancien_mdp'];
    $nouveau_mdp = $_POST['nouveau_mdp'];
    $confirm_mdp = $_POST['confirm_mdp'];

    if (empty($ancien_mdp))  { $err_mdp["ancien_mdp"]  = "L'ancien mot de passe est obligatoire."; }
    if (empty($nouveau_mdp)) { $err_mdp["nouveau_mdp"] = "Le nouveau mot de passe est obligatoire."; }
    else if (strlen($nouveau_mdp) < 6) { $err_mdp["nouveau_mdp"] = "Minimum 6 caractères."; }
    if (empty($confirm_mdp)) { $err_mdp["confirm_mdp"] = "Veuillez confirmer le mot de passe."; }
    else if ($nouveau_mdp != $confirm_mdp) { $err_mdp["confirm_mdp"] = "Les mots de passe ne correspondent pas."; }

    if (empty($err_mdp)) {
        if (!password_verify($ancien_mdp, $user['mot_de_passe'])) {
            $err_mdp["ancien_mdp"] = "Ancien mot de passe incorrect.";
        } else {
            try {
                $hash = password_hash($nouveau_mdp, PASSWORD_ARGON2ID);
                $req = $pdo->prepare("UPDATE Utilisateur SET mot_de_passe=? WHERE id_utili=?");
                $req->execute([$hash, $id]);
                header('Location: Profile-client.php?ok=mdp');
                exit();
            } catch (PDOException $e) {
                $err_mdp["db"] = "Erreur : " . $e->getMessage();
            }
        }
    }
}
?>

<?php
// Fetch replies for client's avis
$id_avis_list = array_column($avis, 'ID_Avis');
$reponses_par_avis = [];
if (count($id_avis_list) > 0) {
    $placeholders = implode(',', array_fill(0, count($id_avis_list), '?'));
    $req = $pdo->prepare("
        SELECT r.*, u.nom, u.prenom, u.role
        FROM Reponse r
        JOIN Utilisateur u ON r.ID_utili = u.id_utili
        WHERE r.ID_Avis IN ($placeholders)
        ORDER BY r.ID_Rep ASC
    ");
    $req->execute($id_avis_list);
    foreach ($req->fetchAll(PDO::FETCH_ASSOC) as $rep) {
        $reponses_par_avis[$rep['ID_Avis']][] = $rep;
    }
}

$toast_msg  = '';
$toast_type = 'success';
if ($success_profil != '')   { $toast_msg = $success_profil; }
if ($success_mdp != '')      { $toast_msg = $success_mdp; }
if ($success_avis != '')     { $toast_msg = $success_avis; }
if ($success_reclam != '')   { $toast_msg = $success_reclam; }
if (!empty($err_avis))       { $toast_msg = implode(' ', $err_avis);   $toast_type = 'error'; }
if (!empty($err_reclam))     { $toast_msg = implode(' ', $err_reclam); $toast_type = 'error'; }

$villes = [
    'Agadir','Al Hoceïma','Asilah','Azrou','Béni Mellal',
    'Berkane','Berrechid','Casablanca','Chefchaouen','Dakhla',
    'El Jadida','Errachidia','Essaouira','Fès','Fnideq',
    'Guelmim','Ifrane','Kénitra','Khémisset','Khénifra',
    'Khouribga','Laâyoune','Larache','Marrakech','Meknès',
    'Mohammedia','Nador','Ouarzazate','Oujda','Rabat',
    'Safi','Salé','Settat','Sidi Ifni','Sidi Kacem',
    'Tanger','Tan-Tan','Taounate','Taroudant','Taza',
    'Tétouan','Tiznit','Zagora'
];
$opts_villes = '';
foreach ($villes as $v) { $opts_villes .= '<option value="' . $v . '">'; }
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

<?php render_navbar('logo'); ?>

<div class="container py-5">
    <div class="row g-4 align-items-start">

        <!-- SIDEBAR -->
        <div class="col-12 col-md-3 sticky-top">
            <div class="profile-sidebar p-3">
                <div class="text-center mb-4">
                    <?php echo '<div class="avatar mx-auto mb-3">' . $initiale . '</div>'; ?>
                    <?php echo '<h6 class="fw-bold mb-0">' . $user['prenom'] . ' ' . $user['nom'] . '</h6>'; ?>
                    <small class="text-muted-ink">Client</small>
                </div>
                <hr class="sidebar-divider">
                <div class="d-flex flex-column gap-1">
                    <button class="sidebar-link active" data-section="dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</button>
                    <button class="sidebar-link" data-section="profil"><i class="bi bi-person me-2"></i>Mon Profil</button>
                    <button class="sidebar-link" data-section="commandes"><i class="bi bi-bag me-2"></i>Mes Commandes</button>
                    <button class="sidebar-link" data-section="avis"><i class="bi bi-star me-2"></i>Mes Avis</button>
                    <button class="sidebar-link" data-section="favoris"><i class="bi bi-heart me-2"></i>Mes Favoris</button>
                </div>
                <hr class="sidebar-divider">
                <a href="../deconnexion.php" class="log-out"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-12 col-md-9">

            <?php echo '<div id="toast-data" data-msg="' . str_replace('"', '&quot;', $toast_msg) . '" data-type="' . $toast_type . '"></div>'; ?>

            <!-- DASHBOARD -->
            <div id="dashboard" class="section">
                <?php echo '<h4 class="mb-1">Bonjour, ' . $user['prenom'] . ' !</h4>'; ?>
                <p class="text-muted-ink mb-4" style="font-size:0.9rem;">Bienvenue sur votre espace personnel.</p>
                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
                            <?php echo '<div class="stat-value">' . $nb_commandes . '</div>'; ?>
                            <div class="stat-label">Commandes</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                            <?php echo '<div class="stat-value">' . number_format($total_depense, 0) . '</div>'; ?>
                            <div class="stat-label">MAD dépensés</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-star"></i></div>
                            <?php echo '<div class="stat-value">' . $nb_avis . '</div>'; ?>
                            <div class="stat-label">Avis laissés</div>
                        </div>
                    </div>
                </div>
                <div class="content-card">
                    <h6 class="content-card-title mb-3">Dernières commandes</h6>
                    <?php
                    if (count($commandes) == 0) {
                        echo '<p class="text-muted-ink text-center py-3">Aucune commande pour l\'instant.</p>';
                    } else {
                        $recent = array_slice($commandes, 0, 4);
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-borderless align-middle mb-0">';
                        echo '<thead><tr class="table-header-row"><th>N° Commande</th><th>Date</th><th>Total</th><th>Statut</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($recent as $c) {
                            $badge = '<span class="badge-statut encours">' . $c['status_com'] . '</span>';
                            if ($c['status_com'] == 'livré')      { $badge = '<span class="badge-statut livré">Livré</span>'; }
                            if ($c['status_com'] == 'en attente') { $badge = '<span class="badge-statut encours">En attente</span>'; }
                            if ($c['status_com'] == 'annulé')     { $badge = '<span class="badge-statut annulé">Annulé</span>'; }
                            echo '<tr>';
                            echo '<td class="fw-bold">#' . $c['ID_Com'] . '</td>';
                            echo '<td>' . date('d M Y', strtotime($c['date_com'])) . '</td>';
                            echo '<td class="price-text fw-bold">' . number_format($c['prix_total'], 2) . ' MAD</td>';
                            echo '<td>' . $badge . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                    }
                    ?>
                </div>
            </div>

            <!-- MON PROFIL -->
            <div id="profil" class="section d-none">
                <h4 class="mb-4">Mon Profil</h4>
                <div class="content-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="content-card-title mb-0" style="border:none;padding:0;">Informations personnelles</h6>
                        <button type="button" id="btnModifierProfil" class="btn btn-modifier-profil">Modifier</button>
                    </div>

                    <!-- MODE AFFICHAGE -->
                    <div id="profilDisplay">
                        <div class="row g-3">
                            <?php
                            echo '<div class="col-md-6"><div class="profil-field-label">Nom</div><div class="profil-field-value">' . $user['nom'] . '</div></div>';
                            echo '<div class="col-md-6"><div class="profil-field-label">Prénom</div><div class="profil-field-value">' . $user['prenom'] . '</div></div>';
                            echo '<div class="col-md-6"><div class="profil-field-label">Email</div><div class="profil-field-value">' . $user['email'] . '</div></div>';
                            echo '<div class="col-md-6"><div class="profil-field-label">Ville</div><div class="profil-field-value">' . ($user['ville'] ? $user['ville'] : '—') . '</div></div>';
                            echo '<div class="col-12"><div class="profil-field-label">Rue / Adresse</div><div class="profil-field-value">' . ($user['rue'] ? $user['rue'] : '—') . '</div></div>';
                            ?>
                        </div>
                    </div>

                    <!-- MODE ÉDITION -->
                    <div id="profilEdit" class="d-none">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label label-info-perso small fw-bold">Nom</label>
                                    <?php if (isset($err_profil["nom"])) echo '<small class="text-danger d-block mb-1">' . $err_profil['nom'] . '</small>'; ?>
                                    <?php echo '<input type="text" name="nom" class="form-control input-info-perso form-control-profile" value="' . $user['nom'] . '">'; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-info-perso small fw-bold">Prénom</label>
                                    <?php if (isset($err_profil["prenom"])) echo '<small class="text-danger d-block mb-1">' . $err_profil['prenom'] . '</small>'; ?>
                                    <?php echo '<input type="text" name="prenom" class="form-control input-info-perso form-control-profile" value="' . $user['prenom'] . '">'; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-info-perso small fw-bold">Email</label>
                                    <?php if (isset($err_profil["email"])) echo '<small class="text-danger d-block mb-1">' . $err_profil['email'] . '</small>'; ?>
                                    <?php echo '<input type="email" name="email" class="form-control input-info-perso form-control-profile" value="' . $user['email'] . '">'; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label label-info-perso small fw-bold">Ville</label>
                                    <?php echo '<input list="villes-list" type="text" name="ville" class="form-control input-info-perso form-control-profile" value="' . $user['ville'] . '">'; ?>
                                    <?php echo '<datalist id="villes-list">' . $opts_villes . '</datalist>'; ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label label-info-perso small fw-bold">Rue / Adresse</label>
                                    <?php echo '<input type="text" name="rue" class="form-control input-info-perso form-control-profile" value="' . $user['rue'] . '">'; ?>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" name="save_profil" class="btn btn-save text-white">Sauvegarder</button>
                                    <button type="button" id="btnAnnulerProfil" class="btn btn-annuler-profil">Annuler</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php
                    if (!empty($err_profil)) {
                        echo '<script>
                            document.getElementById("profilDisplay").classList.add("d-none");
                            document.getElementById("profilEdit").classList.remove("d-none");
                            document.getElementById("btnModifierProfil").classList.add("d-none");
                        </script>';
                    }
                    ?>
                </div>

                <div class="content-card">
                    <h6 class="content-card-title mb-4">Changer le mot de passe</h6>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ancien mot de passe</label>
                                <?php if (isset($err_mdp["ancien_mdp"])) echo '<small class="text-danger d-block mb-1">' . $err_mdp['ancien_mdp'] . '</small>'; ?>
                                <input type="password" name="ancien_mdp" class="form-control form-control-profile" placeholder="••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nouveau</label>
                                <?php if (isset($err_mdp["nouveau_mdp"])) echo '<small class="text-danger d-block mb-1">' . $err_mdp['nouveau_mdp'] . '</small>'; ?>
                                <input type="password" name="nouveau_mdp" class="form-control form-control-profile" placeholder="••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Confirmer</label>
                                <?php if (isset($err_mdp["confirm_mdp"])) echo '<small class="text-danger d-block mb-1">' . $err_mdp['confirm_mdp'] . '</small>'; ?>
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
                    <?php
                    if (count($commandes) == 0) {
                        echo '<p class="text-muted-ink text-center py-3">Aucune commande pour l\'instant.</p>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-borderless align-middle mb-0">';
                        echo '<thead><tr class="table-header-row"><th>N° Commande</th><th>Date</th><th>Ville livraison</th><th>Total</th><th>Statut</th><th>Action</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($commandes as $c) {
                            $badge = '<span class="badge-statut encours">' . $c['status_com'] . '</span>';
                            if ($c['status_com'] == 'livré')      { $badge = '<span class="badge-statut livré">Livré</span>'; }
                            if ($c['status_com'] == 'en attente') { $badge = '<span class="badge-statut encours">En attente</span>'; }
                            if ($c['status_com'] == 'annulé')     { $badge = '<span class="badge-statut annulé">Annulé</span>'; }
                            $ville_liv = $c['ville_livraison'] ? $c['ville_livraison'] : '—';
                            echo '<tr>';
                            echo '<td class="fw-bold">#' . $c['ID_Com'] . '</td>';
                            echo '<td>' . date('d M Y', strtotime($c['date_com'])) . '</td>';
                            echo '<td>' . $ville_liv . '</td>';
                            echo '<td class="price-text fw-bold">' . number_format($c['prix_total'], 2) . ' MAD</td>';
                            echo '<td>' . $badge . '</td>';
                            echo '<td><button class="btn btn-sm btn-reclam" data-id="' . $c['ID_Com'] . '" data-num="#' . $c['ID_Com'] . '"><i class="bi bi-flag me-1"></i>Signaler</button></td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                    }
                    ?>
                </div>
            </div>

            <!-- MES AVIS -->
            <div id="avis" class="section d-none">
                <h4 class="mb-4">Mes Avis</h4>
                <div class="content-card">
                    <?php
                    if (count($avis) == 0) {
                        echo '<p class="text-muted-ink text-center py-3">Vous n\'avez laissé aucun avis pour l\'instant.</p>';
                    } else {
                        echo '<div class="d-flex flex-column gap-3">';
                        $first = true;
                        foreach ($avis as $av) {
                            if (!$first) { echo '<hr class="avis-divider">'; }
                            $first = false;

                            $safe_comm   = str_replace('"', '&quot;', $av['commentaire']);
                            $safe_produit = str_replace('"', '&quot;', $av['nom_Prod']);

                            // étoiles
                            $stars = '';
                            for ($i = 1; $i <= 5; $i++) {
                                $stars .= $i <= $av['note'] ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                            }

                            echo '<div class="avis-item">';
                            echo '<div class="d-flex justify-content-between align-items-start mb-1">';
                            echo '<h6 class="fw-bold mb-0">' . $av['nom_Prod'] . '</h6>';
                            echo '<small class="text-muted-ink">' . date('d M Y', strtotime($av['date_avis'])) . '</small>';
                            echo '</div>';
                            echo '<div class="stars-display mb-2">' . $stars . '</div>';
                            echo '<p class="text-muted-ink mb-2" style="font-size:0.875rem;">' . $av['commentaire'] . '</p>';

                            // Réponses
                            if (isset($reponses_par_avis[$av['ID_Avis']])) {
                                echo '<div class="d-flex flex-column gap-2 mb-2">';
                                foreach ($reponses_par_avis[$av['ID_Avis']] as $rep) {
                                    if ($rep['role'] == 'admin') {
                                        $auteur = '<i class="bi bi-shield-check me-1"></i>Équipe Green Market';
                                    } else if ($rep['role'] == 'producteur') {
                                        $auteur = '<i class="bi bi-shop me-1"></i>' . $rep['prenom'] . ' ' . $rep['nom'];
                                    } else {
                                        $auteur = $rep['prenom'] . ' ' . $rep['nom'];
                                    }
                                    echo '<div class="reponse-client-item">';
                                    echo '<span class="reponse-client-auteur">' . $auteur . '</span>';
                                    echo '<span class="reponse-client-texte">' . $rep['message'] . '</span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }

                            echo '<div class="d-flex gap-2">';
                            echo '<button class="btn btn-sm btn-modifier-avis" data-id="' . $av['ID_Avis'] . '" data-note="' . $av['note'] . '" data-commentaire="' . $safe_comm . '" data-produit="' . $safe_produit . '"><i class="bi bi-pencil me-1"></i>Modifier</button>';
                            echo '<button class="btn btn-sm btn-supprimer-avis" data-id="' . $av['ID_Avis'] . '" data-produit="' . $safe_produit . '"><i class="bi bi-trash me-1"></i>Supprimer</button>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- MES FAVORIS -->
            <div id="favoris" class="section d-none">
                <h4 class="mb-4">Mes Favoris</h4>
                <?php
                if (count($favoris) == 0) {
                    echo '<div class="content-card"><p class="text-muted-ink text-center py-3">Aucun favori pour l\'instant.</p></div>';
                } else {
                    echo '<div class="row g-3">';
                    foreach ($favoris as $p) {
                        $note = $p['note_moyenne'] ? (float)$p['note_moyenne'] : 0;
                        $nb   = (int)$p['nb_avis'];
                        $stars = '';
                        for ($i = 1; $i <= 5; $i++) {
                            if ($note >= $i)           { $stars .= '<i class="bi bi-star-fill"></i>'; }
                            else if ($note >= $i - 0.5){ $stars .= '<i class="bi bi-star-half"></i>'; }
                            else                       { $stars .= '<i class="bi bi-star"></i>'; }
                        }
                        if ($nb > 0) { $stars .= '<small class="text-muted-ink ms-1">(' . $nb . ')</small>'; }

                        echo '<div class="col-6 col-md-4">';
                        echo '<div class="card border-0 shadow-sm h-100">';
                        echo '<div class="img-wrapper"><img src="' . $p['Prod_img'] . '" class="card-img-top product-img" alt="' . $p['nom_Prod'] . '"></div>';
                        echo '<div class="card-body d-flex flex-column">';
                        echo '<small class="text-muted-ink">' . $p['nom_Categ'] . '</small>';
                        echo '<h6 class="card-title mt-1 mb-1">' . $p['nom_Prod'] . '</h6>';
                        echo '<small class="boutique-name mb-1">' . $p['nom_boutique'] . '</small>';
                        echo '<div class="stars-display mb-2">' . $stars . '</div>';
                        echo '<p class="fw-bold price-text mb-3 mt-auto">' . number_format($p['Prix'], 2) . ' MAD</p>';
                        echo '<div class="d-flex gap-2">';
                        echo '<a href="../Produit details/Produit details.php?id=' . $p['ID_Prod'] . '" class="btn btn-sm w-75 btn-voir">Voir</a>';
                        echo '<button class="btn btn-sm btn-add w-25" data-id="' . $p['ID_Prod'] . '"><i class="bi bi-cart"></i></button>';
                        echo '</div></div></div></div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>

        </div>
    </div>
</div>

<!-- MODAL MODIFIER AVIS -->
<div class="modal fade" id="modalModifierAvis" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Modifier mon avis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted-ink mb-3" id="modal-avis-produit" style="font-size:0.875rem;"></p>
                <form method="POST">
                    <input type="hidden" name="id_avis" id="input-id-avis">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Note</label>
                        <div class="star-picker d-flex gap-2 mb-1">
                            <i class="bi bi-star-fill star-btn" data-val="1"></i>
                            <i class="bi bi-star-fill star-btn" data-val="2"></i>
                            <i class="bi bi-star-fill star-btn" data-val="3"></i>
                            <i class="bi bi-star-fill star-btn" data-val="4"></i>
                            <i class="bi bi-star-fill star-btn" data-val="5"></i>
                        </div>
                        <input type="hidden" name="note_new" id="input-note-new">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Commentaire</label>
                        <textarea name="commentaire_new" id="input-commentaire-new" class="form-control form-control-profile" rows="4"></textarea>
                    </div>
                    <button type="submit" name="modifier_avis" class="btn btn-save text-white w-100">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SUPPRIMER AVIS -->
<div class="modal fade" id="modalSupprimerAvis" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Supprimer l'avis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted-ink mb-3" style="font-size:0.875rem;">Êtes-vous sûr de vouloir supprimer votre avis pour <strong id="modal-suppr-produit"></strong> ?</p>
                <form method="POST">
                    <input type="hidden" name="id_avis" id="input-suppr-id-avis">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-appliquer w-50" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="supprimer_avis" class="btn btn-sm btn-danger w-50">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RÉCLAMATION -->
<div class="modal fade" id="modalReclam" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Signaler un problème</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted-ink mb-3" style="font-size:0.875rem;">Commande <strong id="modal-reclam-num"></strong></p>
                <form method="POST">
                    <input type="hidden" name="id_com" id="input-reclam-id-com">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description du problème</label>
                        <textarea name="description" class="form-control form-control-profile" rows="4" placeholder="Décrivez le problème rencontré..."></textarea>
                    </div>
                    <button type="submit" name="signaler_reclam" class="btn btn-save text-white w-100">Envoyer la réclamation</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

<!-- TOAST -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="clientToast" class="toast align-items-center border-0" role="alert" aria-live="assertive">
        <div class="d-flex">
            <div class="toast-body fw-bold" id="clientToastMsg"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Panier_handler.js"></script>
<script src="Profile-client.js"></script>
<script>
    var toastData = document.getElementById('toast-data');
    var msg  = toastData ? toastData.getAttribute('data-msg')  : '';
    var type = toastData ? toastData.getAttribute('data-type') : '';
    if (msg && msg.trim() != '') {
        var toastEl  = document.getElementById('clientToast');
        var toastMsg = document.getElementById('clientToastMsg');
        toastMsg.textContent = msg;
        toastEl.style.backgroundColor = type == 'error' ? '#c0392b' : '#2d4a2d';
        toastEl.style.color = '#f5ede0';
        new bootstrap.Toast(toastEl, { delay: 3500 }).show();
    }
</script>
</body>
</html>