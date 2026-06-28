<?php
session_start();
require_once '../functions.php';
require_once '../connexion.php';

// Check roles using the functions
require_login();
require_role('admin');

$id = $_SESSION['id_utili'];
$success = '';
$err = [];

// Fetch admin first (needed for password verify)
try {
    $req = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_utili = ?");
    $req->execute([$id]);
    $admin = $req->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// --- SUSPENDRE / ACTIVER UTILISATEUR ---
if (isset($_POST['toggle_user'])) {
    $uid     = $_POST['uid'];
    $nouveau = $_POST['nouveau_statut'];
    try {
        $req = $pdo->prepare("UPDATE Utilisateur SET est_active=? WHERE id_utili=?");
        $req->execute([$nouveau, $uid]);
        $success = "Statut utilisateur mis à jour.";
    } catch (PDOException $e) {
        $err[] = "Erreur : " . $e->getMessage();
    }
}

// --- SUPPRIMER UTILISATEUR ---
if (isset($_POST['supprimer_user'])) {
    $uid = $_POST['uid'];
    try {
        $pdo->prepare("DELETE FROM Notification WHERE ID_utili=?")->execute([$uid]);
        $pdo->prepare("DELETE FROM Reclamation WHERE ID_utili=?")->execute([$uid]);
        $pdo->prepare("DELETE FROM Reponse WHERE ID_utili=?")->execute([$uid]);
        $pdo->prepare("DELETE FROM Avis WHERE ID_utili=?")->execute([$uid]);
        $pdo->prepare("DELETE FROM Favoris WHERE ID_utili=?")->execute([$uid]);

        $req = $pdo->prepare("SELECT ID_Panier FROM Panier WHERE ID_utili=?");
        $req->execute([$uid]);
        foreach ($req->fetchAll(PDO::FETCH_ASSOC) as $pan) {
            $pdo->prepare("DELETE FROM Ligne_panier WHERE ID_Panier=?")->execute([$pan['ID_Panier']]);
        }
        $pdo->prepare("DELETE FROM Panier WHERE ID_utili=?")->execute([$uid]);

        $req = $pdo->prepare("SELECT ID_Com FROM Commande WHERE ID_utili=?");
        $req->execute([$uid]);
        foreach ($req->fetchAll(PDO::FETCH_ASSOC) as $com) {
            $pdo->prepare("DELETE FROM Ligne_commande WHERE ID_Com=?")->execute([$com['ID_Com']]);
            $pdo->prepare("DELETE FROM Paiement WHERE ID_Com=?")->execute([$com['ID_Com']]);
        }
        $pdo->prepare("DELETE FROM Commande WHERE ID_utili=?")->execute([$uid]);

        // Supprimer les boutiques du producteur et leurs produits
        $req = $pdo->prepare("SELECT ID_boutique FROM Boutique WHERE ID_utili=?");
        $req->execute([$uid]);
        foreach ($req->fetchAll(PDO::FETCH_ASSOC) as $b) {
            $bid = $b['ID_boutique'];
            $req2 = $pdo->prepare("SELECT ID_Prod FROM Produit WHERE ID_boutique=?");
            $req2->execute([$bid]);
            foreach ($req2->fetchAll(PDO::FETCH_ASSOC) as $pr) {
                $pid = $pr['ID_Prod'];
                $pdo->prepare("DELETE FROM Produit_image WHERE ID_Prod=?")->execute([$pid]);
                $pdo->prepare("DELETE FROM Favoris WHERE ID_Prod=?")->execute([$pid]);
                $pdo->prepare("DELETE FROM Ligne_panier WHERE ID_Prod=?")->execute([$pid]);
                $pdo->prepare("DELETE FROM Reponse WHERE ID_Avis IN (SELECT ID_Avis FROM Avis WHERE ID_Prod=?)")->execute([$pid]);
                $pdo->prepare("DELETE FROM Avis WHERE ID_Prod=?")->execute([$pid]);
                $pdo->prepare("DELETE FROM Ligne_commande WHERE ID_Prod=?")->execute([$pid]);
                $pdo->prepare("DELETE FROM Produit WHERE ID_Prod=?")->execute([$pid]);
            }
            $pdo->prepare("DELETE FROM Boutique WHERE ID_boutique=?")->execute([$bid]);
        }

        $pdo->prepare("DELETE FROM Utilisateur WHERE id_utili=?")->execute([$uid]);
        $success = "Utilisateur supprimé.";
    } catch (PDOException $e) {
        $err[] = "Erreur : " . $e->getMessage();
    }
}

// --- CHANGER STATUT COMMANDE ---
if (isset($_POST['changer_statut_com'])) {
    $id_com  = $_POST['id_com'];
    $nouveau = $_POST['nouveau_statut'];
    try {
        $req = $pdo->prepare("UPDATE Commande SET status_com=? WHERE ID_Com=?");
        $req->execute([$nouveau, $id_com]);
        $success = "Statut commande mis à jour.";
    } catch (PDOException $e) {
        $err[] = "Erreur : " . $e->getMessage();
    }
}

// --- CHANGER STATUT RÉCLAMATION ---
if (isset($_POST['changer_statut_reclam'])) {
    $id_rec  = $_POST['id_reclam'];
    $nouveau = $_POST['nouveau_statut'];
    try {
        $req = $pdo->prepare("UPDATE Reclamation SET status_reclam=? WHERE ID_Reclam=?");
        $req->execute([$nouveau, $id_rec]);
        $success = "Réclamation mise à jour.";
    } catch (PDOException $e) {
        $err[] = "Erreur : " . $e->getMessage();
    }
}

// --- SUPPRIMER AVIS ---
if (isset($_POST['supprimer_avis'])) {
    $id_avis = $_POST['id_avis'];
    try {
        $pdo->prepare("DELETE FROM Reponse WHERE ID_Avis=?")->execute([$id_avis]);
        $pdo->prepare("DELETE FROM Avis WHERE ID_Avis=?")->execute([$id_avis]);
        $success = "Avis supprimé.";
    } catch (PDOException $e) {
        $err[] = "Erreur : " . $e->getMessage();
    }
}

// --- AJOUTER RÉPONSE ---
if (isset($_POST['ajouter_reponse'])) {
    $id_avis = $_POST['id_avis'];
    $message = $_POST['message_reponse'];
    if (empty($message)) {
        $err[] = "La réponse ne peut pas être vide.";
    } else {
        try {
            $req = $pdo->prepare("INSERT INTO Reponse (message, ID_utili, ID_Avis) VALUES (?, ?, ?)");
            $req->execute([$message, $id, $id_avis]);
            $success = "Réponse ajoutée.";
        } catch (PDOException $e) {
            $err[] = "Erreur : " . $e->getMessage();
        }
    }
}

// --- MODIFIER RÉPONSE ---
if (isset($_POST['modifier_reponse'])) {
    $id_rep  = $_POST['id_rep'];
    $message = $_POST['message_reponse_edit'];
    if (empty($message)) {
        $err[] = "La réponse ne peut pas être vide.";
    } else {
        try {
            $req = $pdo->prepare("UPDATE Reponse SET message=? WHERE ID_Rep=? AND ID_utili=?");
            $req->execute([$message, $id_rep, $id]);
            $success = "Réponse modifiée.";
        } catch (PDOException $e) {
            $err[] = "Erreur : " . $e->getMessage();
        }
    }
}

// --- SUPPRIMER RÉPONSE ---
if (isset($_POST['supprimer_reponse'])) {
    $id_rep = $_POST['id_rep'];
    try {
        $req = $pdo->prepare("DELETE FROM Reponse WHERE ID_Rep=?");
        $req->execute([$id_rep]);
        $success = "Réponse supprimée.";
    } catch (PDOException $e) {
        $err[] = "Erreur : " . $e->getMessage();
    }
}

// --- AJOUTER CATÉGORIE (FIXED) ---
if (isset($_POST['ajouter_categorie'])) {
    $nom  = $_POST['nom_categ'];
    $desc = $_POST['desc_categ'];
    $image_path = '';

    // Gestion de l'upload d'image
    if (isset($_FILES['image_categ']) && $_FILES['image_categ']['error'] == 0) {
        $upload_dir = '../uploads/categories_images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = pathinfo($_FILES['image_categ']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image_categ']['tmp_name'], $upload_dir . $filename);
        $image_path = '../uploads/categories_images/' . $filename; // Store relative path
    }

    if (empty($nom)) {
        $err[] = "Le nom de la catégorie est obligatoire.";
    } else {
        try {
            // FIXED: Use Categ_img not image_Categ
            $req = $pdo->prepare("INSERT INTO Categorie (nom_Categ, description_Categ, Categ_img) VALUES (?,?,?)");
            $req->execute([$nom, $desc, $image_path]);
            $success = "Catégorie ajoutée.";
        } catch (PDOException $e) {
            $err[] = "Erreur : " . $e->getMessage();
        }
    }
}

// --- MODIFIER CATÉGORIE (FIXED) ---
if (isset($_POST['modifier_categorie'])) {
    $id_cat = $_POST['id_categ'];
    $nom    = $_POST['nom_categ_edit'];
    $desc   = $_POST['desc_categ_edit'];
    
    if (empty($nom)) {
        $err[] = "Le nom de la catégorie est obligatoire.";
    } else {
        try {
            // Check if a new image was uploaded
            if (isset($_FILES['image_categ_edit']) && $_FILES['image_categ_edit']['error'] == 0) {
                $upload_dir = '../uploads/categories_images/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext = pathinfo($_FILES['image_categ_edit']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image_categ_edit']['tmp_name'], $upload_dir . $filename);
                // FIXED: Store relative path
                $image_path = 'uploads/categories_images/' . $filename;

                // FIXED: Use Categ_img not image_Categ
                $req = $pdo->prepare("UPDATE Categorie SET nom_Categ=?, description_Categ=?, Categ_img=? WHERE ID_Categ=?");
                $req->execute([$nom, $desc, $image_path, $id_cat]);
            } else {
                $req = $pdo->prepare("UPDATE Categorie SET nom_Categ=?, description_Categ=? WHERE ID_Categ=?");
                $req->execute([$nom, $desc, $id_cat]);
            }
            $success = "Catégorie modifiée.";
        } catch (PDOException $e) {
            $err[] = "Erreur : " . $e->getMessage();
        }
    }
}

// --- SUPPRIMER CATÉGORIE ---
if (isset($_POST['supprimer_categorie'])) {
    $id_cat = $_POST['id_categ'];
    try {
        $req = $pdo->prepare("DELETE FROM Categorie WHERE ID_Categ=?");
        $req->execute([$id_cat]);
        $success = "Catégorie supprimée.";
    } catch (PDOException $e) {
        $err[] = "Erreur : " . $e->getMessage();
    }
}

// --- SUPPRIMER PRODUIT ---
if (isset($_POST['supprimer_produit'])) {
    $id_prod = $_POST['id_prod'];
    try {
        $pdo->prepare("DELETE FROM Produit_image WHERE ID_Prod=?")->execute([$id_prod]);
        $pdo->prepare("DELETE FROM Favoris WHERE ID_Prod=?")->execute([$id_prod]);
        $pdo->prepare("DELETE FROM Reponse WHERE ID_Avis IN (SELECT ID_Avis FROM Avis WHERE ID_Prod=?)")->execute([$id_prod]);
        $pdo->prepare("DELETE FROM Avis WHERE ID_Prod=?")->execute([$id_prod]);
        $pdo->prepare("DELETE FROM Ligne_panier WHERE ID_Prod=?")->execute([$id_prod]);
        $pdo->prepare("DELETE FROM Produit WHERE ID_Prod=?")->execute([$id_prod]);
        $success = "Produit supprimé.";
    } catch (PDOException $e) {
        $err[] = "Erreur : " . $e->getMessage();
    }
}

// --- MODIFIER STOCK ---
if (isset($_POST['modifier_stock'])) {
    $id_prod   = $_POST['id_prod'];
    $new_stock = $_POST['new_stock'];
    if (!is_numeric($new_stock) || $new_stock < 0) {
        $err[] = "Stock invalide.";
    } else {
        try {
            $req = $pdo->prepare("UPDATE Produit SET Stock=? WHERE ID_Prod=?");
            $req->execute([$new_stock, $id_prod]);
            $success = "Stock mis à jour.";
        } catch (PDOException $e) {
            $err[] = "Erreur : " . $e->getMessage();
        }
    }
}

// --- MODIFIER PROFIL ADMIN ---
$success_profil = '';
$err_profil = [];
if (isset($_GET['ok']) && $_GET['ok'] == 'profil') { $success_profil = "Profil mis à jour."; }
if (isset($_POST['save_profil'])) {
    $nom    = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email  = $_POST['email'];
    $ville  = $_POST['ville'];
    $rue    = $_POST['rue'];
    if (empty($nom))    { $err_profil['nom']    = "Nom obligatoire."; }
    if (empty($prenom)) { $err_profil['prenom'] = "Prénom obligatoire."; }
    if (empty($email))  { $err_profil['email']  = "Email obligatoire."; }
    if (empty($err_profil)) {
        try {
            $req = $pdo->prepare("UPDATE Utilisateur SET nom=?, prenom=?, email=?, ville=?, rue=? WHERE id_utili=?");
            $req->execute([$nom, $prenom, $email, $ville, $rue, $id]);
            header('Location: Admin.php?ok=profil');
            exit();
        } catch (PDOException $e) {
            $err_profil['db'] = "Erreur : " . $e->getMessage();
        }
    }
}

// --- MODIFIER MOT DE PASSE ---
$success_mdp = '';
$err_mdp = [];
if (isset($_GET['ok']) && $_GET['ok'] == 'mdp') { $success_mdp = "Mot de passe modifié."; }
if (isset($_POST['save_mdp'])) {
    $ancien    = $_POST['ancien_mdp'];
    $nouveau   = $_POST['nouveau_mdp'];
    $confirmer = $_POST['confirmer_mdp'];
    if (empty($ancien))                          { $err_mdp['ancien']    = "Ancien mot de passe requis."; }
    if (empty($nouveau) || strlen($nouveau) < 6) { $err_mdp['nouveau']   = "Minimum 6 caractères."; }
    if ($nouveau != $confirmer)                  { $err_mdp['confirmer'] = "Les mots de passe ne correspondent pas."; }
    if (empty($err_mdp)) {
        if (!password_verify($ancien, $admin['mot_de_passe'])) {
            $err_mdp['ancien'] = "Ancien mot de passe incorrect.";
        } else {
            try {
                $hash = password_hash($nouveau, PASSWORD_ARGON2ID);
                $req = $pdo->prepare("UPDATE Utilisateur SET mot_de_passe=? WHERE id_utili=?");
                $req->execute([$hash, $id]);
                header('Location: Admin.php?ok=mdp');
                exit();
            } catch (PDOException $e) {
                $err_mdp['db'] = "Erreur : " . $e->getMessage();
            }
        }
    }
}

// ===== FETCH ALL DATA =====
try {
    $req = $pdo->prepare("SELECT COUNT(*) as nb FROM Utilisateur WHERE role='client'");
    $req->execute();
    $nb_clients = $req->fetch(PDO::FETCH_ASSOC)['nb'];

    $req = $pdo->prepare("SELECT COUNT(*) as nb FROM Utilisateur WHERE role='producteur' AND est_active=1");
    $req->execute();
    $nb_producteurs_actifs = $req->fetch(PDO::FETCH_ASSOC)['nb'];

    $req = $pdo->prepare("SELECT COUNT(*) as nb FROM Commande");
    $req->execute();
    $nb_commandes_total = $req->fetch(PDO::FETCH_ASSOC)['nb'];

    $req = $pdo->prepare("SELECT COALESCE(SUM(prix_total),0) as total FROM Commande WHERE status_com='livré'");
    $req->execute();
    $volume_ventes = $req->fetch(PDO::FETCH_ASSOC)['total'];

    $req = $pdo->prepare("SELECT COUNT(*) as nb FROM Commande WHERE status_com='en attente'");
    $req->execute();
    $nb_com_attente = $req->fetch(PDO::FETCH_ASSOC)['nb'];

    $req = $pdo->prepare("SELECT COUNT(*) as nb FROM Commande WHERE status_com='livré'");
    $req->execute();
    $nb_com_livrees = $req->fetch(PDO::FETCH_ASSOC)['nb'];

    $req = $pdo->prepare("SELECT COUNT(*) as nb FROM Commande WHERE status_com='annulé'");
    $req->execute();
    $nb_com_annulees = $req->fetch(PDO::FETCH_ASSOC)['nb'];

    $req = $pdo->prepare("SELECT COUNT(*) as nb FROM Reclamation WHERE status_reclam='ouverte'");
    $req->execute();
    $nb_reclam_ouvertes = $req->fetch(PDO::FETCH_ASSOC)['nb'];

    $req = $pdo->prepare("SELECT * FROM Utilisateur ORDER BY date_inscription DESC");
    $req->execute();
    $users = $req->fetchAll(PDO::FETCH_ASSOC);

    $nb_users_total = count($users);
    $nb_actifs = 0; $nb_suspendus = 0;
    $nb_prod_total = 0; $nb_clients_total = 0; $nb_admins_total = 0;
    foreach ($users as $u) {
        if ($u['est_active']) { $nb_actifs++; } else { $nb_suspendus++; }
        if ($u['role'] == 'producteur') { $nb_prod_total++; }
        if ($u['role'] == 'client')     { $nb_clients_total++; }
        if ($u['role'] == 'admin')      { $nb_admins_total++; }
    }

    $req = $pdo->prepare("
        SELECT c.*, u.nom, u.prenom, u.email
        FROM Commande c
        JOIN Utilisateur u ON c.ID_utili = u.id_utili
        ORDER BY c.date_com DESC
    ");
    $req->execute();
    $commandes = $req->fetchAll(PDO::FETCH_ASSOC);

    $req = $pdo->prepare("
        SELECT r.*, u.nom, u.prenom, c.prix_total
        FROM Reclamation r
        JOIN Utilisateur u ON r.ID_utili = u.id_utili
        JOIN Commande c ON r.ID_Com = c.ID_Com
        ORDER BY r.ID_Reclam DESC
    ");
    $req->execute();
    $reclamations = $req->fetchAll(PDO::FETCH_ASSOC);

    $req = $pdo->prepare("SELECT * FROM Boutique ORDER BY nom_boutique ASC");
    $req->execute();
    $boutiques = $req->fetchAll(PDO::FETCH_ASSOC);

    $filtre_boutique = 0;
    if (isset($_GET['boutique_id']) && is_numeric($_GET['boutique_id'])) {
        $filtre_boutique = (int)$_GET['boutique_id'];
    }

    if ($filtre_boutique > 0) {
        $req = $pdo->prepare("
            SELECT a.*, u.nom, u.prenom, p.nom_Prod, b.nom_boutique, b.ID_boutique
            FROM Avis a
            JOIN Utilisateur u ON a.ID_utili = u.id_utili
            JOIN Produit p ON a.ID_Prod = p.ID_Prod
            JOIN Boutique b ON p.ID_boutique = b.ID_boutique
            WHERE b.ID_boutique = ?
            ORDER BY a.date_avis DESC
        ");
        $req->execute([$filtre_boutique]);
    } else {
        $req = $pdo->prepare("
            SELECT a.*, u.nom, u.prenom, p.nom_Prod, b.nom_boutique, b.ID_boutique
            FROM Avis a
            JOIN Utilisateur u ON a.ID_utili = u.id_utili
            JOIN Produit p ON a.ID_Prod = p.ID_Prod
            JOIN Boutique b ON p.ID_boutique = b.ID_boutique
            ORDER BY a.date_avis DESC
        ");
        $req->execute();
    }
    $avis_list = $req->fetchAll(PDO::FETCH_ASSOC);
    $nb_avis_total = count($avis_list);

    $id_avis_list = array_column($avis_list, 'ID_Avis');
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

    $req = $pdo->prepare("
        SELECT cat.*, COUNT(p.ID_Prod) as nb_produits
        FROM Categorie cat
        LEFT JOIN Produit p ON p.ID_Categ = cat.ID_Categ
        GROUP BY cat.ID_Categ
        ORDER BY cat.nom_Categ ASC
    ");
    $req->execute();
    $categories = $req->fetchAll(PDO::FETCH_ASSOC);
    $nb_categories = count($categories);

    $req = $pdo->prepare("
        SELECT pay.*, u.nom, u.prenom
        FROM Paiement pay
        JOIN Commande c ON pay.ID_Com = c.ID_Com
        JOIN Utilisateur u ON c.ID_utili = u.id_utili
        ORDER BY pay.date_pay DESC
    ");
    $req->execute();
    $paiements = $req->fetchAll(PDO::FETCH_ASSOC);

    $total_paiements = 0;
    foreach ($paiements as $p) { $total_paiements += $p['montant']; }

    $req = $pdo->prepare("
        SELECT p.*, c.nom_Categ, b.nom_boutique
        FROM Produit p
        JOIN Categorie c ON p.ID_Categ = c.ID_Categ
        JOIN Boutique b ON p.ID_boutique = b.ID_boutique
        ORDER BY p.date_ajout_Prod DESC
    ");
    $req->execute();
    $produits = $req->fetchAll(PDO::FETCH_ASSOC);

    $nb_produits_total = count($produits);
    $nb_stock_faible = 0; $nb_rupture = 0;
    foreach ($produits as $pr) {
        if ($pr['Stock'] == 0)              { $nb_rupture++; }
        else if ($pr['Stock'] <= 5)         { $nb_stock_faible++; }
    }

} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}

$initiale = strtoupper(mb_substr($admin['prenom'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Green Market – Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <link href="Admin.css" rel="stylesheet">
</head>
<body>

<?php render_navbar('logo'); ?>

<?php
$toast_msg  = '';
$toast_type = 'success';
if ($success != '')        { $toast_msg = $success;             $toast_type = 'success'; }
if (!empty($err))          { $toast_msg = implode(' ', $err);   $toast_type = 'error'; }
if ($success_profil != '') { $toast_msg = $success_profil;      $toast_type = 'success'; }
if ($success_mdp != '')    { $toast_msg = $success_mdp;         $toast_type = 'success'; }

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

<div class="container-fluid px-4 px-lg-5 py-4">
  <div class="row g-4">

    <!-- SIDEBAR -->
    <div class="col-12 col-md-3">
      <div class="profile-sidebar p-3">
        <div class="d-flex align-items-center gap-3 p-2 mb-2">
          <?php echo '<div class="avatar">' . $initiale . '</div>'; ?>
          <div>
            <?php echo '<h6 class="fw-bold mb-0" style="font-family:\'Playfair Display\',serif;">' . $admin['prenom'] . ' ' . $admin['nom'] . '</h6>'; ?>
            <small class="text-muted-ink">Administrateur</small>
          </div>
        </div>
        <hr>
        <div class="d-flex flex-column gap-1">
          <a href="#" class="sidebar-link active" data-section="dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
          <a href="#" class="sidebar-link" data-section="utilisateurs"><i class="bi bi-people me-2"></i>Utilisateurs</a>
          <a href="#" class="sidebar-link" data-section="produits"><i class="bi bi-boxes me-2"></i>Produits</a>
          <a href="#" class="sidebar-link" data-section="stock"><i class="bi bi-box-seam me-2"></i>Stock</a>
          <a href="#" class="sidebar-link" data-section="commandes"><i class="bi bi-cart3 me-2"></i>Commandes</a>
          <a href="#" class="sidebar-link" data-section="paiements"><i class="bi bi-credit-card me-2"></i>Paiements</a>
          <a href="#" class="sidebar-link" data-section="reclamations">
            <i class="bi bi-exclamation-diamond me-2"></i>Réclamations
            <?php if ($nb_reclam_ouvertes > 0) { echo '<span class="cart-badge position-relative ms-2" style="top:0;right:0;display:inline-flex;">' . $nb_reclam_ouvertes . '</span>'; } ?>
          </a>
          <a href="#" class="sidebar-link" data-section="categories"><i class="bi bi-grid me-2"></i>Catégories</a>
          <a href="#" class="sidebar-link" data-section="avis"><i class="bi bi-star me-2"></i>Avis</a>
          <a href="#" class="sidebar-link" data-section="profil"><i class="bi bi-person-gear me-2"></i>Mon Profil</a>
        </div>
        <hr>
        <a href="../deconnexion.php" class="sidebar-link" style="color:#c0392b;"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a>
      </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="col-12 col-md-9">

      <?php echo '<div id="toast-data" data-msg="' . str_replace('"', '&quot;', $toast_msg) . '" data-type="' . $toast_type . '"></div>'; ?>

      <!-- DASHBOARD -->
      <div id="dashboard" class="section">
        <h2 class="mb-1">Dashboard</h2>
        <?php echo '<p class="text-muted-ink mb-4">Bonjour ' . $admin['prenom'] . ', voici l\'état actuel de la plateforme.</p>'; ?>
        <div class="row g-3 mb-4">
          <?php
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca h-100"><div class="icon"><i class="bi bi-people-fill"></i></div><div class="content"><p class="title fw-bold">Clients</p><h2 class="fw-bold">' . $nb_clients_total . '</h2></div></div></div>';
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca h-100"><div class="icon"><i class="bi bi-shop"></i></div><div class="content"><p class="title fw-bold">Producteurs actifs</p><h2 class="fw-bold">' . $nb_producteurs_actifs . '</h2></div></div></div>';
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca h-100"><div class="icon"><i class="bi bi-cart-check-fill"></i></div><div class="content"><p class="title fw-bold">Commandes</p><h2 class="fw-bold">' . $nb_commandes_total . '</h2></div></div></div>';
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca h-100"><div class="icon"><i class="bi bi-cash-stack"></i></div><div class="content"><p class="title fw-bold">Volume livré</p><h2 class="fw-bold">' . number_format($volume_ventes, 0, ',', ' ') . ' MAD</h2></div></div></div>';
          ?>
        </div>
        <div class="charts mb-4">
          <div class="chart">
            <div class="text-center w-100">
              <i class="bi bi-bar-chart-fill mb-2" style="font-size:2.5rem;color:var(--green);display:block;"></i>
              <div class="row g-2 mt-2">
                <?php
                echo '<div class="col-4"><div class="fw-bold" style="font-size:1.4rem;color:var(--green);">' . $nb_com_attente . '</div><small class="text-muted-ink">En attente</small></div>';
                echo '<div class="col-4"><div class="fw-bold" style="font-size:1.4rem;color:var(--green);">' . $nb_com_livrees . '</div><small class="text-muted-ink">Livrées</small></div>';
                echo '<div class="col-4"><div class="fw-bold" style="font-size:1.4rem;color:#c62828;">' . $nb_com_annulees . '</div><small class="text-muted-ink">Annulées</small></div>';
                ?>
              </div>
            </div>
          </div>
          <div class="quick-view">
            <h3 class="section-title">Alertes</h3>
            <?php
            echo '<div class="quick-item"><div class="icon" style="background:#c62828;"><i class="bi bi-chat-left-text-fill"></i></div><p>Réclamations ouvertes</p><span class="fw-bold">' . $nb_reclam_ouvertes . '</span></div>';
            echo '<div class="quick-item"><div class="icon" style="background:var(--terracotta);"><i class="bi bi-x-circle"></i></div><p>Ruptures de stock</p><span class="fw-bold">' . $nb_rupture . '</span></div>';
            echo '<div class="quick-item"><div class="icon" style="background:var(--green);"><i class="bi bi-boxes"></i></div><p>Total produits</p><span class="fw-bold">' . $nb_produits_total . '</span></div>';
            ?>
          </div>
        </div>
      </div>

      <!-- UTILISATEURS -->
      <div id="utilisateurs" class="section d-none">
        <h2 class="mb-1">Utilisateurs</h2>
        <p class="text-muted-ink mb-4">Gérez tous les comptes de la plateforme.</p>
        <div class="row g-3 mb-4">
          <?php
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-people"></i></div><div class="content"><p class="title fw-bold">Total</p><h2 class="fw-bold">' . $nb_users_total . '</h2></div></div></div>';
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-person-heart"></i></div><div class="content"><p class="title fw-bold">Clients</p><h2 class="fw-bold">' . $nb_clients_total . '</h2></div></div></div>';
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-shop"></i></div><div class="content"><p class="title fw-bold">Producteurs</p><h2 class="fw-bold">' . $nb_prod_total . '</h2></div></div></div>';
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-person-x"></i></div><div class="content"><p class="title fw-bold">Suspendus</p><h2 class="fw-bold">' . $nb_suspendus . '</h2></div></div></div>';
          ?>
        </div>
        <div class="notif-tabs mb-3">
          <?php
          echo '<button class="active" data-role="tous">Tous (' . $nb_users_total . ')</button>';
          echo '<button data-role="client">Clients (' . $nb_clients_total . ')</button>';
          echo '<button data-role="producteur">Producteurs (' . $nb_prod_total . ')</button>';
          echo '<button data-role="admin">Admins (' . $nb_admins_total . ')</button>';
          ?>
        </div>
        <div class="products-page">
          <div class="table-scroll">
            <table class="product-table">
              <thead><tr><th>Utilisateur</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Date d'inscription</th><th>Actions</th></tr></thead>
              <tbody>
                <?php
                foreach ($users as $u) {
                    $badge = $u['est_active']
                        ? '<span class="badge-statut livré">Actif</span>'
                        : '<span class="badge-statut annulé">Suspendu</span>';

                    $actions = '<small class="text-muted-ink">Vous</small>';
                    if ($u['id_utili'] != $id) {
                        $toggle_val   = $u['est_active'] ? '0' : '1';
                        $toggle_class = $u['est_active'] ? 'suspend-btn' : 'view-btn';
                        $toggle_icon  = $u['est_active'] ? 'bi-slash-circle' : 'bi-check-circle';
                        $toggle_title = $u['est_active'] ? 'Suspendre' : 'Activer';
                        $actions  = '<div class="action-buttons">';
                        $actions .= '<form method="POST" style="display:inline;">';
                        $actions .= '<input type="hidden" name="uid" value="' . $u['id_utili'] . '">';
                        $actions .= '<input type="hidden" name="nouveau_statut" value="' . $toggle_val . '">';
                        $actions .= '<button type="submit" name="toggle_user" class="icon-btn ' . $toggle_class . '" title="' . $toggle_title . '"><i class="bi ' . $toggle_icon . '"></i></button>';
                        $actions .= '</form>';
                        $actions .= '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Supprimer définitivement cet utilisateur et toutes ses données ?\')">';
                        $actions .= '<input type="hidden" name="uid" value="' . $u['id_utili'] . '">';
                        $actions .= '<button type="submit" name="supprimer_user" class="icon-btn remove-btn" title="Supprimer"><i class="bi bi-trash"></i></button>';
                        $actions .= '</form></div>';
                    }

                    echo '<tr data-role="' . $u['role'] . '">';
                    echo '<td><h6 class="mb-0">' . $u['prenom'] . ' ' . $u['nom'] . '</h6></td>';
                    echo '<td>' . $u['email'] . '</td>';
                    echo '<td>' . ucfirst($u['role']) . '</td>';
                    echo '<td>' . $badge . '</td>';
                    echo '<td>' . date('d/m/Y', strtotime($u['date_inscription'])) . '</td>';
                    echo '<td>' . $actions . '</td>';
                    echo '</tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- PRODUITS -->
      <div id="produits" class="section d-none">
        <h2 class="mb-1">Produits</h2>
        <p class="text-muted-ink mb-4">Supervisez et supprimez les produits de la plateforme.</p>
        <div class="row g-3 mb-4">
          <?php
          echo '<div class="col-lg-4 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-boxes"></i></div><div class="content"><p class="title fw-bold">Total produits</p><h2 class="fw-bold">' . $nb_produits_total . '</h2></div></div></div>';
          echo '<div class="col-lg-4 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-exclamation-triangle"></i></div><div class="content"><p class="title fw-bold">Stock faible (≤5)</p><h2 class="fw-bold">' . $nb_stock_faible . '</h2></div></div></div>';
          echo '<div class="col-lg-4 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-x-circle"></i></div><div class="content"><p class="title fw-bold">Rupture</p><h2 class="fw-bold">' . $nb_rupture . '</h2></div></div></div>';
          ?>
        </div>
        <div class="products-page">
          <div class="table-scroll">
            <table class="product-table">
              <thead><tr><th>Produit</th><th>Catégorie</th><th>Boutique</th><th>Prix</th><th>Stock</th><th>Actions</th></tr></thead>
              <tbody>
                <?php
                if (count($produits) == 0) {
                    echo '<tr><td colspan="6" class="text-center text-muted-ink py-3">Aucun produit.</td></tr>';
                }
                foreach ($produits as $pr) {
                    if ($pr['Stock'] == 0)       { $stock_badge = '<span class="stock out">Rupture</span>'; }
                    else if ($pr['Stock'] <= 5)  { $stock_badge = '<span class="stock low">' . $pr['Stock'] . '</span>'; }
                    else                         { $stock_badge = '<span class="stock ok">' . $pr['Stock'] . '</span>'; }

                    echo '<tr>';
                    echo '<td><div class="product-info"><img src="' . $pr['Prod_img'] . '" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px;"><h6 class="mb-0">' . $pr['nom_Prod'] . '</h6></div></td>';
                    echo '<td>' . $pr['nom_Categ'] . '</td>';
                    echo '<td>' . $pr['nom_boutique'] . '</td>';
                    echo '<td class="price-text fw-bold">' . number_format($pr['Prix'], 2) . ' MAD</td>';
                    echo '<td>' . $stock_badge . '</td>';
                    echo '<td><div class="action-buttons">';
                    echo '<a href="../Produit details/Produit details.php?id=' . $pr['ID_Prod'] . '" target="_blank" class="icon-btn view-btn" title="Voir"><i class="bi bi-eye"></i></a>';
                    echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Supprimer ce produit ?\')">';
                    echo '<input type="hidden" name="id_prod" value="' . $pr['ID_Prod'] . '">';
                    echo '<button type="submit" name="supprimer_produit" class="icon-btn remove-btn" title="Supprimer"><i class="bi bi-trash"></i></button>';
                    echo '</form></div></td></tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- STOCK -->
      <div id="stock" class="section d-none">
        <h2 class="mb-1">Stock</h2>
        <p class="text-muted-ink mb-4">Mettez à jour les quantités en stock.</p>
        <?php
        if ($nb_rupture > 0 || $nb_stock_faible > 0) {
            echo '<div class="alert-box mb-3"><i class="bi bi-exclamation-circle me-2"></i>' . $nb_rupture . ' rupture(s), ' . $nb_stock_faible . ' stock(s) faible(s).</div>';
        }
        ?>
        <div class="products-page">
          <div class="table-scroll">
            <table class="product-table">
              <thead><tr><th>Produit</th><th>Boutique</th><th>Stock</th><th>Statut</th><th>Action</th></tr></thead>
              <tbody>
                <?php
                foreach ($produits as $pr) {
                    if ($pr['Stock'] == 0)      { $stock_badge = '<span class="stock out">Rupture</span>'; }
                    else if ($pr['Stock'] <= 5) { $stock_badge = '<span class="stock low">Faible</span>'; }
                    else                        { $stock_badge = '<span class="stock ok">OK</span>'; }

                    echo '<tr>';
                    echo '<td><div class="product-info"><img src="' . $pr['Prod_img'] . '" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px;"><h6 class="mb-0">' . $pr['nom_Prod'] . '</h6></div></td>';
                    echo '<td>' . $pr['nom_boutique'] . '</td>';
                    echo '<td><strong>' . $pr['Stock'] . '</strong></td>';
                    echo '<td>' . $stock_badge . '</td>';
                    echo '<td><button class="icon-btn view-btn btn-modifier-stock" data-id="' . $pr['ID_Prod'] . '" data-nom="' . $pr['nom_Prod'] . '" data-stock="' . $pr['Stock'] . '"><i class="bi bi-pencil"></i></button></td>';
                    echo '</tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- COMMANDES -->
      <div id="commandes" class="section d-none">
        <h2 class="mb-1">Commandes</h2>
        <p class="text-muted-ink mb-4">Supervisez et mettez à jour le statut des commandes.</p>
        <div class="row g-3 mb-4">
          <?php
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-cart-check"></i></div><div class="content"><p class="title fw-bold">Total</p><h2 class="fw-bold">' . count($commandes) . '</h2></div></div></div>';
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-hourglass-split"></i></div><div class="content"><p class="title fw-bold">En attente</p><h2 class="fw-bold">' . $nb_com_attente . '</h2></div></div></div>';
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-truck"></i></div><div class="content"><p class="title fw-bold">Livrées</p><h2 class="fw-bold">' . $nb_com_livrees . '</h2></div></div></div>';
          echo '<div class="col-lg-3 col-md-6"><div class="card-ca"><div class="icon"><i class="bi bi-x-octagon"></i></div><div class="content"><p class="title fw-bold">Annulées</p><h2 class="fw-bold">' . $nb_com_annulees . '</h2></div></div></div>';
          ?>
        </div>
        <div class="products-page">
          <div class="table-scroll">
            <table class="product-table">
              <thead><tr><th>N°</th><th>Client</th><th>Date</th><th>Total</th><th>Ville</th><th>Statut</th></tr></thead>
              <tbody>
                <?php
                if (count($commandes) == 0) {
                    echo '<tr><td colspan="6" class="text-center text-muted-ink py-3">Aucune commande.</td></tr>';
                }
                $statuts_com = ['en attente','confirmée','en préparation','expédiée','livré','annulé'];
                $labels_com  = ['En attente','Confirmée','En préparation','Expédiée','Livrée','Annulée'];
                foreach ($commandes as $c) {
                    $opts = '';
                    foreach ($statuts_com as $k => $s) {
                        $sel = ($c['status_com'] == $s) ? ' selected' : '';
                        $opts .= '<option value="' . $s . '"' . $sel . '>' . $labels_com[$k] . '</option>';
                    }
                    echo '<tr>';
                    echo '<td><strong>#' . $c['ID_Com'] . '</strong></td>';
                    echo '<td><h6 class="mb-0">' . $c['prenom'] . ' ' . $c['nom'] . '</h6><small class="text-muted-ink">' . $c['email'] . '</small></td>';
                    echo '<td>' . date('d/m/Y', strtotime($c['date_com'])) . '</td>';
                    echo '<td class="price-text fw-bold">' . number_format($c['prix_total'], 2) . ' MAD</td>';
                    echo '<td>' . ($c['ville_livraison'] ? $c['ville_livraison'] : '—') . '</td>';
                    echo '<td><form method="POST" style="display:inline;"><input type="hidden" name="id_com" value="' . $c['ID_Com'] . '"><select name="nouveau_statut" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">' . $opts . '</select><input type="hidden" name="changer_statut_com" value="1"></form></td>';
                    echo '</tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- PAIEMENTS -->
      <div id="paiements" class="section d-none">
        <h2 class="mb-1">Paiements</h2>
        <p class="text-muted-ink mb-4">Historique de tous les paiements enregistrés.</p>
        <div class="row g-3 mb-4">
          <?php
          echo '<div class="col-lg-6"><div class="card-ca"><div class="icon"><i class="bi bi-cash-stack"></i></div><div class="content"><p class="title fw-bold">Total encaissé</p><h2 class="fw-bold">' . number_format($total_paiements, 0, ',', ' ') . ' MAD</h2></div></div></div>';
          echo '<div class="col-lg-6"><div class="card-ca"><div class="icon"><i class="bi bi-receipt"></i></div><div class="content"><p class="title fw-bold">Nb paiements</p><h2 class="fw-bold">' . count($paiements) . '</h2></div></div></div>';
          ?>
        </div>
        <div class="products-page">
          <div class="table-scroll">
            <table class="product-table">
              <thead><tr><th>ID</th><th>Client</th><th>Commande</th><th>Mode</th><th>Montant</th><th>Date</th></tr></thead>
              <tbody>
                <?php
                if (count($paiements) == 0) {
                    echo '<tr><td colspan="6" class="text-center text-muted-ink py-3">Aucun paiement enregistré.</td></tr>';
                }
                foreach ($paiements as $p) {
                    echo '<tr>';
                    echo '<td>#' . $p['ID_Pay'] . '</td>';
                    echo '<td>' . $p['prenom'] . ' ' . $p['nom'] . '</td>';
                    echo '<td>#' . $p['ID_Com'] . '</td>';
                    echo '<td>' . $p['mode_pay'] . '</td>';
                    echo '<td class="price-text fw-bold">' . number_format($p['montant'], 2) . ' MAD</td>';
                    echo '<td>' . date('d/m/Y', strtotime($p['date_pay'])) . '</td>';
                    echo '</tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- RÉCLAMATIONS -->
      <div id="reclamations" class="section d-none">
        <h2 class="mb-1">Réclamations</h2>
        <p class="text-muted-ink mb-4">Traitez les réclamations soumises par les clients.</p>
        <div class="row g-3 mb-4">
          <?php
          echo '<div class="col-lg-6"><div class="card-ca"><div class="icon"><i class="bi bi-exclamation-diamond"></i></div><div class="content"><p class="title fw-bold">Total</p><h2 class="fw-bold">' . count($reclamations) . '</h2></div></div></div>';
          echo '<div class="col-lg-6"><div class="card-ca"><div class="icon"><i class="bi bi-clock-history"></i></div><div class="content"><p class="title fw-bold">Ouvertes</p><h2 class="fw-bold">' . $nb_reclam_ouvertes . '</h2></div></div></div>';
          ?>
        </div>
        <div class="products-page">
          <div class="table-scroll">
            <table class="product-table">
              <thead><tr><th>ID</th><th>Client</th><th>Commande</th><th>Description</th><th>Statut</th></tr></thead>
              <tbody>
                <?php
                if (count($reclamations) == 0) {
                    echo '<tr><td colspan="5" class="text-center text-muted-ink py-3">Aucune réclamation.</td></tr>';
                }
                foreach ($reclamations as $r) {
                    $opts  = '<option value="ouverte"' . ($r['status_reclam'] == 'ouverte' ? ' selected' : '') . '>Ouverte</option>';
                    $opts .= '<option value="résolue"' . ($r['status_reclam'] == 'résolue' ? ' selected' : '') . '>Résolue</option>';
                    $opts .= '<option value="rejetée"' . ($r['status_reclam'] == 'rejetée' ? ' selected' : '') . '>Rejetée</option>';
                    echo '<tr>';
                    echo '<td>#' . $r['ID_Reclam'] . '</td>';
                    echo '<td>' . $r['prenom'] . ' ' . $r['nom'] . '</td>';
                    echo '<td>#' . $r['ID_Com'] . '</td>';
                    echo '<td style="max-width:220px;font-size:0.85rem;">' . $r['description'] . '</td>';
                    echo '<td><form method="POST" style="display:inline;"><input type="hidden" name="id_reclam" value="' . $r['ID_Reclam'] . '"><select name="nouveau_statut" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">' . $opts . '</select><input type="hidden" name="changer_statut_reclam" value="1"></form></td>';
                    echo '</tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- CATÉGORIES (FIXED DISPLAY) -->
      <div id="categories" class="section d-none">
        <h2 class="mb-1">Catégories</h2>
        <p class="text-muted-ink mb-4">Ajoutez, modifiez ou supprimez les catégories de produits.</p>
        <div class="row g-3 mb-4">
          <?php
          echo '<div class="col-lg-6"><div class="card-ca"><div class="icon"><i class="bi bi-grid-fill"></i></div><div class="content"><p class="title fw-bold">Total catégories</p><h2 class="fw-bold">' . $nb_categories . '</h2></div></div></div>';
          echo '<div class="col-lg-6"><div class="card-ca"><div class="icon"><i class="bi bi-box-seam"></i></div><div class="content"><p class="title fw-bold">Produits classés</p><h2 class="fw-bold">' . array_sum(array_column($categories, 'nb_produits')) . '</h2></div></div></div>';
          ?>
        </div>
        <div class="content-card mb-4">
          <h6 class="fw-bold mb-3" style="color:var(--green);">Ajouter une catégorie</h6>
          <form method="POST" enctype="multipart/form-data">
            <div class="row g-2">
              <div class="col-md-3"><input type="text" name="nom_categ" class="form-control" placeholder="Nom" required></div>
              <div class="col-md-4"><input type="text" name="desc_categ" class="form-control" placeholder="Description (optionnel)"></div>
              <div class="col-md-3"><input type="file" name="image_categ" class="form-control" accept="image/*" required></div>
              <div class="col-md-2"><button type="submit" name="ajouter_categorie" class="btn btn-save text-white w-100">Ajouter</button></div>
            </div>
          </form>
        </div>
        <div class="products-page">
          <div class="table-scroll">
            <table class="product-table">
              <thead><tr><th>Image</th><th>Nom</th><th>Description</th><th>Produits</th><th>Actions</th></tr></thead>
              <tbody>
                <?php
                foreach ($categories as $cat) {
                    // FIXED: Use Categ_img not image_Categ
                    $img = !empty($cat['Categ_img'])
                        ? '<img src="' . $cat['Categ_img'] . '" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">'
                        : '<div style="width:40px;height:40px;background:#eee;border-radius:6px;"></div>';

                    $trash = $cat['nb_produits'] == 0
                        ? '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Supprimer cette catégorie ?\')"><input type="hidden" name="id_categ" value="' . $cat['ID_Categ'] . '"><button type="submit" name="supprimer_categorie" class="icon-btn remove-btn" title="Supprimer"><i class="bi bi-trash"></i></button></form>'
                        : '<span class="icon-btn" style="opacity:0.25;cursor:not-allowed;" title="Contient des produits"><i class="bi bi-trash"></i></span>';

                    $safe_nom  = str_replace('"', '&quot;', $cat['nom_Categ']);
                    $safe_desc = str_replace('"', '&quot;', $cat['description_Categ']);

                    echo '<tr>';
                    echo '<td>' . $img . '</td>';
                    echo '<td><strong>' . $cat['nom_Categ'] . '</strong></td>';
                    echo '<td>' . ($cat['description_Categ'] ? $cat['description_Categ'] : '—') . '</td>';
                    echo '<td>' . $cat['nb_produits'] . '</td>';
                    echo '<td><div class="action-buttons"><button class="icon-btn view-btn btn-modifier-categorie" data-id="' . $cat['ID_Categ'] . '" data-nom="' . $safe_nom . '" data-desc="' . $safe_desc . '" title="Modifier"><i class="bi bi-pencil"></i></button>' . $trash . '</div></td>';
                    echo '</tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- AVIS -->
      <div id="avis" class="section d-none">
        <h2 class="mb-1">Avis clients</h2>
        <p class="text-muted-ink mb-4">Modérez les avis et répondez aux clients.</p>

        <form method="GET" class="mb-4 d-flex align-items-center gap-2">
          <select name="boutique_id" class="form-select w-auto" onchange="this.form.submit()">
            <option value="0">Toutes les boutiques</option>
            <?php
            foreach ($boutiques as $b) {
                $sel = ($filtre_boutique == $b['ID_boutique']) ? ' selected' : '';
                echo '<option value="' . $b['ID_boutique'] . '"' . $sel . '>' . $b['nom_boutique'] . '</option>';
            }
            ?>
          </select>
          <?php
          if ($filtre_boutique > 0) {
              echo '<a href="Admin.php" class="btn btn-sm" style="border:1px solid rgba(42,36,30,0.2);border-radius:8px;color:var(--ink);">Réinitialiser</a>';
          }
          ?>
        </form>

        <div class="row g-3 mb-4">
          <?php
          echo '<div class="col-lg-6"><div class="card-ca"><div class="icon"><i class="bi bi-chat-dots"></i></div><div class="content"><p class="title fw-bold">Avis affichés</p><h2 class="fw-bold">' . $nb_avis_total . '</h2></div></div></div>';
          echo '<div class="col-lg-6"><div class="card-ca"><div class="icon"><i class="bi bi-shop"></i></div><div class="content"><p class="title fw-bold">Boutiques</p><h2 class="fw-bold">' . count($boutiques) . '</h2></div></div></div>';
          ?>
        </div>

        <?php
        if (count($avis_list) == 0) {
            echo '<div class="content-card"><p class="text-muted-ink text-center py-3">Aucun avis.</p></div>';
        }
        foreach ($avis_list as $av) {
            $stars = '';
            for ($i = 1; $i <= 5; $i++) {
                $stars .= '<i class="bi ' . ($i <= $av['note'] ? 'bi-star-fill' : 'bi-star') . '" style="color:var(--terracotta);font-size:0.8rem;"></i>';
            }

            echo '<div class="content-card mb-3">';

            // En-tête
            echo '<div class="d-flex justify-content-between align-items-start mb-2">';
            echo '<div><span class="fw-bold">' . $av['prenom'] . ' ' . $av['nom'] . '</span><span class="text-muted-ink ms-2" style="font-size:0.8rem;">' . $av['nom_boutique'] . ' — ' . $av['nom_Prod'] . '</span></div>';
            echo '<div class="d-flex align-items-center gap-2"><small class="text-muted-ink">' . date('d/m/Y', strtotime($av['date_avis'])) . '</small>';
            echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Supprimer cet avis et toutes ses réponses ?\')">';
            echo '<input type="hidden" name="id_avis" value="' . $av['ID_Avis'] . '">';
            echo '<button type="submit" name="supprimer_avis" class="icon-btn remove-btn" title="Supprimer l\'avis"><i class="bi bi-trash"></i></button>';
            echo '</form></div></div>';

            // Étoiles + commentaire
            echo '<div class="mb-1">' . $stars . '</div>';
            echo '<p style="font-size:0.9rem;" class="mb-3">' . $av['commentaire'] . '</p>';

            // Réponses
            if (isset($reponses_par_avis[$av['ID_Avis']])) {
                foreach ($reponses_par_avis[$av['ID_Avis']] as $rep) {
                    if ($rep['role'] == 'admin')       { $auteur = '<i class="bi bi-shield-check me-1"></i>Admin'; }
                    else if ($rep['role'] == 'producteur') { $auteur = '<i class="bi bi-shop me-1"></i>' . $rep['prenom'] . ' ' . $rep['nom']; }
                    else { $auteur = $rep['prenom'] . ' ' . $rep['nom']; }

                    echo '<div class="reponse-item d-flex justify-content-between align-items-start">';
                    echo '<div class="flex-grow-1">';
                    echo '<span class="reponse-auteur">' . $auteur . '</span>';

                    if ($rep['ID_utili'] == $id) {
                        $safe_msg = str_replace('"', '&quot;', $rep['message']);
                        echo '<span class="reponse-texte" id="reponse-texte-' . $rep['ID_Rep'] . '">' . $rep['message'] . '</span>';
                        echo '<form method="POST" class="reponse-edit-form d-none" id="reponse-edit-' . $rep['ID_Rep'] . '">';
                        echo '<input type="hidden" name="id_rep" value="' . $rep['ID_Rep'] . '">';
                        echo '<div class="d-flex gap-2 mt-1">';
                        echo '<input type="text" name="message_reponse_edit" class="form-control form-control-sm" value="' . $safe_msg . '">';
                        echo '<button type="submit" name="modifier_reponse" class="btn btn-sm btn-save text-white">OK</button>';
                        echo '<button type="button" class="btn btn-sm btn-annuler-reponse" data-id="' . $rep['ID_Rep'] . '" style="border:1px solid rgba(42,36,30,0.2);border-radius:8px;">✕</button>';
                        echo '</div></form>';
                    } else {
                        echo '<span class="reponse-texte">' . $rep['message'] . '</span>';
                    }

                    echo '</div>';
                    echo '<div class="d-flex gap-1 ms-2 flex-shrink-0">';
                    if ($rep['ID_utili'] == $id) {
                        echo '<button type="button" class="icon-btn view-btn btn-edit-reponse" data-id="' . $rep['ID_Rep'] . '" title="Modifier"><i class="bi bi-pencil"></i></button>';
                    }
                    echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Supprimer cette réponse ?\')">';
                    echo '<input type="hidden" name="id_rep" value="' . $rep['ID_Rep'] . '">';
                    echo '<button type="submit" name="supprimer_reponse" class="icon-btn remove-btn" title="Supprimer"><i class="bi bi-trash"></i></button>';
                    echo '</form>';
                    echo '</div></div>';
                }
            }

            // Formulaire répondre
            echo '<form method="POST" class="reponse-form mt-2">';
            echo '<input type="hidden" name="id_avis" value="' . $av['ID_Avis'] . '">';
            echo '<div class="d-flex gap-2">';
            echo '<input type="text" name="message_reponse" class="form-control form-control-sm" placeholder="Répondre à cet avis...">';
            echo '<button type="submit" name="ajouter_reponse" class="btn btn-sm btn-save text-white flex-shrink-0">Envoyer</button>';
            echo '</div></form>';
            echo '</div>';
        }
        ?>
      </div>

      <!-- MON PROFIL -->
      <div id="profil" class="section d-none">
        <h2 class="mb-1">Mon Profil</h2>
        <p class="text-muted-ink mb-4">Modifiez vos informations personnelles et votre mot de passe.</p>

        <div class="content-card mb-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="fw-bold mb-0" style="color:var(--green);">Informations personnelles</h6>
            <button type="button" id="btnModifierProfil" class="btn btn-save text-white btn-sm px-3">Modifier</button>
          </div>

          <!-- MODE AFFICHAGE -->
          <div id="profilDisplay">
            <div class="row g-3">
              <?php
              echo '<div class="col-md-6"><div class="profil-field"><span class="profil-label">Nom</span><span class="profil-value">' . $admin['nom'] . '</span></div></div>';
              echo '<div class="col-md-6"><div class="profil-field"><span class="profil-label">Prénom</span><span class="profil-value">' . $admin['prenom'] . '</span></div></div>';
              echo '<div class="col-md-6"><div class="profil-field"><span class="profil-label">Email</span><span class="profil-value">' . $admin['email'] . '</span></div></div>';
              echo '<div class="col-md-6"><div class="profil-field"><span class="profil-label">Ville</span><span class="profil-value">' . ($admin['ville'] ? $admin['ville'] : '—') . '</span></div></div>';
              echo '<div class="col-12"><div class="profil-field"><span class="profil-label">Adresse</span><span class="profil-value">' . ($admin['rue'] ? $admin['rue'] : '—') . '</span></div></div>';
              ?>
            </div>
          </div>

          <!-- MODE ÉDITION -->
          <div id="profilEdit" class="d-none">
            <form method="POST">
              <div class="row g-3">
                <div class="col-md-6">
                  <?php if (isset($err_profil['nom'])) echo '<small class="text-danger d-block mb-1">' . $err_profil['nom'] . '</small>'; ?>
                  <label class="form-label small fw-bold">Nom</label>
                  <?php echo '<input type="text" name="nom" class="form-control" value="' . $admin['nom'] . '">'; ?>
                </div>
                <div class="col-md-6">
                  <?php if (isset($err_profil['prenom'])) echo '<small class="text-danger d-block mb-1">' . $err_profil['prenom'] . '</small>'; ?>
                  <label class="form-label small fw-bold">Prénom</label>
                  <?php echo '<input type="text" name="prenom" class="form-control" value="' . $admin['prenom'] . '">'; ?>
                </div>
                <div class="col-md-6">
                  <?php if (isset($err_profil['email'])) echo '<small class="text-danger d-block mb-1">' . $err_profil['email'] . '</small>'; ?>
                  <label class="form-label small fw-bold">Email</label>
                  <?php echo '<input type="email" name="email" class="form-control" value="' . $admin['email'] . '">'; ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Ville</label>
                  <?php echo '<input list="villes-list" type="text" name="ville" class="form-control" value="' . $admin['ville'] . '">'; ?>
                  <?php echo '<datalist id="villes-list">' . $opts_villes . '</datalist>'; ?>
                </div>
                <div class="col-12">
                  <label class="form-label small fw-bold">Adresse</label>
                  <?php echo '<input type="text" name="rue" class="form-control" value="' . $admin['rue'] . '">'; ?>
                </div>
                <div class="col-12 d-flex gap-2">
                  <button type="submit" name="save_profil" class="btn btn-save text-white">Sauvegarder</button>
                  <button type="button" id="btnAnnulerProfil" class="btn btn-sm px-3" style="border:1px solid rgba(42,36,30,0.2);border-radius:8px;color:var(--ink);">Annuler</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="content-card">
          <h6 class="fw-bold mb-4" style="color:var(--green);">Changer mot de passe</h6>
          <form method="POST">
            <div class="row g-3">
              <div class="col-md-4">
                <?php if (isset($err_mdp['ancien'])) echo '<small class="text-danger d-block mb-1">' . $err_mdp['ancien'] . '</small>'; ?>
                <label class="form-label small fw-bold">Ancien mot de passe</label>
                <input type="password" name="ancien_mdp" class="form-control" placeholder="••••••">
              </div>
              <div class="col-md-4">
                <?php if (isset($err_mdp['nouveau'])) echo '<small class="text-danger d-block mb-1">' . $err_mdp['nouveau'] . '</small>'; ?>
                <label class="form-label small fw-bold">Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" class="form-control" placeholder="••••••">
              </div>
              <div class="col-md-4">
                <?php if (isset($err_mdp['confirmer'])) echo '<small class="text-danger d-block mb-1">' . $err_mdp['confirmer'] . '</small>'; ?>
                <label class="form-label small fw-bold">Confirmer</label>
                <input type="password" name="confirmer_mdp" class="form-control" placeholder="••••••">
              </div>
              <div class="col-12">
                <button type="submit" name="save_mdp" class="btn btn-save text-white">Modifier</button>
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

    </div>
  </div>
</div>

<!-- MODAL MODIFIER CATÉGORIE -->
<div class="modal fade" id="modalModifierCategorie" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Modifier la catégorie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id_categ" id="input-id-categ">
          <div class="mb-3"><label class="form-label small fw-bold">Nom</label><input type="text" name="nom_categ_edit" id="input-nom-categ" class="form-control" required></div>
          <div class="mb-3"><label class="form-label small fw-bold">Description</label><input type="text" name="desc_categ_edit" id="input-desc-categ" class="form-control"></div>
          <div class="mb-3"><label class="form-label small fw-bold">Nouvelle Image (optionnel)</label><input type="file" name="image_categ_edit" class="form-control" accept="image/*"></div>
          <button type="submit" name="modifier_categorie" class="btn btn-save text-white w-100">Enregistrer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL MODIFIER STOCK -->
<div class="modal fade" id="modalModifierStock" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Modifier le stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted-ink mb-3" id="modal-stock-nom" style="font-size:0.875rem;"></p>
        <form method="POST">
          <input type="hidden" name="id_prod" id="input-stock-id-prod">
          <div class="mb-3"><label class="form-label small fw-bold">Nouveau stock</label><input type="number" name="new_stock" id="input-new-stock" class="form-control" min="0" required></div>
          <button type="submit" name="modifier_stock" class="btn btn-save text-white w-100">Enregistrer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
  <div id="adminToast" class="toast align-items-center border-0" role="alert" aria-live="assertive">
    <div class="d-flex">
      <div class="toast-body fw-bold" id="adminToastMsg"></div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="Admin.js"></script>
<script>
  var toastData = document.getElementById('toast-data');
  var msg  = toastData ? toastData.getAttribute('data-msg')  : '';
  var type = toastData ? toastData.getAttribute('data-type') : '';
  if (msg && msg.trim() != '') {
    var toastEl  = document.getElementById('adminToast');
    var toastMsg = document.getElementById('adminToastMsg');
    toastMsg.textContent = msg;
    toastEl.style.backgroundColor = type == 'error' ? '#c0392b' : '#2d4a2d';
    toastEl.style.color = '#f5ede0';
    new bootstrap.Toast(toastEl, { delay: 3500 }).show();
  }
</script>
</body>
</html>