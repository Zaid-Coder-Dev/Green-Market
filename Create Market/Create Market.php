<?php
session_start();
require_once '../connexion.php';
require_once '../functions.php';

require_login();

if ($_SESSION['role'] != 'producteur') {
    header('Location: ../Home Page/Home.php');
    exit();
}

$id_utili = $_SESSION['id_utili'];
$err = [];

// Fetch all categories for the form
$categories = [];
try {
    $req = $pdo->prepare("SELECT ID_Categ, nom_Categ FROM Categorie ORDER BY nom_Categ");
    $req->execute();
    $categories = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $err['db'] = "Erreur : " . $e->getMessage();
}

if (isset($_POST['create'])) {
    $nom   = $_POST['nom'];
    $ville = $_POST['ville'];
    $des   = $_POST['des'];
    $tel   = isset($_POST['tel'])   ? $_POST['tel']   : '';
    $insta = isset($_POST['insta']) ? $_POST['insta'] : '';
    $fb    = isset($_POST['fb'])    ? $_POST['fb']    : '';
    $categs = isset($_POST['categorie']) ? $_POST['categorie'] : [];

    if (empty($nom))   { $err['nom']   = "Veuillez saisir le nom de la boutique."; }
    if (empty($ville)) { $err['ville'] = "Veuillez choisir une ville."; }
    if (empty($des))   { $err['des']   = "Veuillez saisir une description."; }
    if (empty($categs)) { $err['categorie'] = "Veuillez sélectionner au moins une catégorie."; }

    // Check if user already has a boutique
    if (empty($err)) {
        try {
            $req = $pdo->prepare("SELECT ID_boutique FROM Boutique WHERE ID_utili = ?");
            $req->execute([$id_utili]);
            if ($req->fetch()) {
                $err['nom'] = "Vous avez déjà une boutique enregistrée.";
            }
        } catch (PDOException $e) {
            $err['db'] = "Erreur : " . $e->getMessage();
        }
    }

    $exts = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];
    $logo_path   = '';
    $banner_path = '';

    if (empty($err)) {
        // Logo (petit, obligatoire)
        if ($_FILES['logo']['error'] != 0) {
            $err['logo'] = "Le logo est obligatoire.";
        } else if (!in_array($_FILES['logo']['type'], $exts)) {
            $err['logo'] = "Format non autorisé. PNG ou JPG uniquement.";
        } else if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
            $err['logo'] = "Taille max : 5 Mo.";
        } else {
            $ext      = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('logo_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], '../uploads/boutiques_images/' . $filename)) {
                $err['logo'] = "Erreur lors de l'enregistrement du logo.";
            } else {
                $logo_path = '../uploads/boutiques_images/' . $filename;
            }
        }

        // Bannière (grande, optionnelle)
        if ($_FILES['banner']['error'] == 0) {
            if (!in_array($_FILES['banner']['type'], $exts)) {
                $err['banner'] = "Format non autorisé. PNG ou JPG uniquement.";
            } else if ($_FILES['banner']['size'] > 5 * 1024 * 1024) {
                $err['banner'] = "Taille max : 5 Mo.";
            } else {
                $ext      = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('banner_') . '.' . $ext;
                if (!move_uploaded_file($_FILES['banner']['tmp_name'], '../uploads/boutiques_images/' . $filename)) {
                    $err['banner'] = "Erreur lors de l'enregistrement de la bannière.";
                } else {
                    $banner_path = '../uploads/boutiques_images/' . $filename;
                }
            }
        }
    }

    if (empty($err)) {
        try {
            $req = $pdo->prepare("INSERT INTO Boutique (nom_boutique, description_boutique, ville, logo, image_banner, telephone, FB_link, Insta_link, ID_utili) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $req->execute([$nom, $des, $ville, $logo_path, $banner_path, $tel, $fb, $insta, $id_utili]);
            
            // Store selected categories in session for later use when adding products
            $_SESSION['boutique_categories'] = $categs;
            $_SESSION['success'] = "Votre boutique a été créée avec succès !";
            
            header('Location: ../Producteur/Producteur.php');
            exit();
        } catch (PDOException $e) {
            // Check if it's a duplicate entry error (unique constraint violation)
            if ($e->errorInfo[1] == 1062) {
                $err['nom'] = "Vous avez déjà une boutique enregistrée.";
            } else {
                $err['db'] = "Erreur : " . $e->getMessage();
            }
        }
    }
}

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
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Green Market – Créer ma boutique</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="Create Market.css">
</head>
<body>

<?php render_navbar('logo'); ?>

<section class="py-4 page-header">
  <div class="container">
    <small class="text-uppercase label-orange">Producteur</small>
    <h2 class="mb-0">Créer ma boutique</h2>
    <p class="text-muted mb-0">Remplissez les informations ci-dessous pour lancer votre boutique sur Green Market</p>
  </div>
</section>

<div class="container my-5">

  <?php if (isset($err['db'])) echo '<div class="alert-error-custom mb-4">' . $err['db'] . '</div>'; ?>

  <form action="" method="POST" enctype="multipart/form-data">

    <!-- 1. INFOS DE BASE -->
    <div class="form-card mb-4">
      <div class="form-card-header">
        <div class="form-card-icon"><i class="bi bi-shop"></i></div>
        <div>
          <h4 class="form-card-title">1. Informations de base</h4>
          <p class="form-card-subtitle">Donnez une identité professionnelle à votre boutique.</p>
        </div>
        <span class="form-badge">Requis</span>
      </div>
      <div class="row g-3 mt-2">
        <div class="col-12">
          <label class="form-label fw-bold">Nom de la boutique</label>
          <?php if (isset($err['nom'])) echo '<div class="text-danger small mt-1">' . $err['nom'] . '</div>'; ?>
          <div class="input-group-custom">
            <i class="bi bi-shop input-icon"></i>
            <?php echo '<input type="text" class="custom-input" name="nom" placeholder="Ex: Coopérative Tiznit" value="' . (isset($_POST['nom']) ? $_POST['nom'] : '') . '">'; ?>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label fw-bold">Ville</label>
          <?php if (isset($err['ville'])) echo '<div class="text-danger small mt-1">' . $err['ville'] . '</div>'; ?>
          <div class="input-group-custom">
            <i class="bi bi-geo-alt input-icon"></i>
            <?php echo '<input list="villes-list" class="custom-input" name="ville" placeholder="Votre ville..." value="' . (isset($_POST['ville']) ? $_POST['ville'] : '') . '">'; ?>
            <?php echo '<datalist id="villes-list">' . $opts_villes . '</datalist>'; ?>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label fw-bold">Description</label>
          <?php if (isset($err['des'])) echo '<div class="text-danger small mt-1">' . $err['des'] . '</div>'; ?>
          <div class="textarea-group-custom">
            <?php echo '<textarea class="custom-textarea" name="des" rows="4" placeholder="Décrivez votre boutique, vos produits et votre savoir-faire artisanal...">' . (isset($_POST['des']) ? $_POST['des'] : '') . '</textarea>'; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. CATÉGORIES -->
    <div class="form-card mb-4">
      <div class="form-card-header">
        <div class="form-card-icon"><i class="bi bi-tags"></i></div>
        <div>
          <h4 class="form-card-title">2. Catégories de produits</h4>
          <p class="form-card-subtitle">Sélectionnez les catégories que vous allez proposer dans votre boutique.</p>
        </div>
        <span class="form-badge">Au moins 1</span>
      </div>
      <?php if (isset($err['categorie'])) echo '<div class="text-danger small mt-2">' . $err['categorie'] . '</div>'; ?>
      <div class="row g-3 mt-2">
        <div class="col-12">
          <div class="d-flex flex-wrap gap-3">
            <?php
            $selected_categs = isset($_POST['categorie']) ? $_POST['categorie'] : [];
            foreach ($categories as $c) {
                $checked = in_array($c['ID_Categ'], $selected_categs) ? 'checked' : '';
                echo '<div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categorie[]" value="' . $c['ID_Categ'] . '" id="categ_' . $c['ID_Categ'] . '" ' . $checked . '>
                    <label class="form-check-label" for="categ_' . $c['ID_Categ'] . '">' . $c['nom_Categ'] . '</label>
                </div>';
            }
            ?>
          </div>
          <small class="text-muted-ink d-block mt-2">Sélectionnez au moins une catégorie. Vous pourrez en ajouter d'autres plus tard.</small>
        </div>
      </div>
    </div>

    <!-- 3. LOGO (petit) -->
    <div class="form-card mb-4">
      <div class="form-card-header">
        <div class="form-card-icon"><i class="bi bi-image"></i></div>
        <div>
          <h4 class="form-card-title">3. Logo de la boutique</h4>
          <p class="form-card-subtitle">Petit logo carré affiché sur les cartes produit et la barre de recherche.</p>
        </div>
        <span class="form-badge">PNG / JPG</span>
      </div>
      <?php if (isset($err['logo'])) echo '<div class="text-danger small mt-2">' . $err['logo'] . '</div>'; ?>
      <div class="logo-upload-row mt-3">
        <div class="logo-upload-zone" id="uploadAreaLogo">
          <input type="file" id="logoInput" name="logo" hidden accept="image/*">
          <img id="previewLogo" src="" alt="Logo" style="display:none;">
          <div id="uploadContentLogo" class="upload-content-small">
            <i class="bi bi-cloud-upload" style="font-size:1.4rem;color:var(--green);"></i>
            <p class="mb-1 small fw-bold mt-1">Logo</p>
            <button type="button" class="btn btn-sm btn-parcourir" id="browseBtnLogo">Parcourir</button>
          </div>
        </div>
        <p class="text-muted-ink small mt-2">Carré recommandé — PNG ou JPG — max 5 Mo</p>
      </div>
    </div>

    <!-- 4. BANNIÈRE (grande) -->
    <div class="form-card mb-4">
      <div class="form-card-header">
        <div class="form-card-icon"><i class="bi bi-image"></i></div>
        <div>
          <h4 class="form-card-title">4. Bannière de la boutique</h4>
          <p class="form-card-subtitle">Image d'en-tête affichée en haut de votre page boutique. Optionnelle.</p>
        </div>
        <span class="form-badge">Optionnel</span>
      </div>
      <?php if (isset($err['banner'])) echo '<div class="text-danger small mt-2">' . $err['banner'] . '</div>'; ?>
      <div class="banner-upload-zone mt-3" id="uploadAreaBanner">
        <input type="file" id="bannerInput" name="banner" hidden accept="image/*">
        <img id="previewBanner" src="" alt="Bannière" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:12px;">
        <div id="uploadContentBanner" class="upload-content">
          <div class="upload-icon-circle">
            <i class="bi bi-cloud-upload"></i>
          </div>
          <h6 class="mt-3 mb-1">Glissez votre bannière ici</h6>
          <p class="text-muted small">ou cliquez pour parcourir — format paysage recommandé</p>
          <button type="button" class="btn btn-sm btn-parcourir mt-2" id="browseBtnBanner">
            <i class="bi bi-folder2-open me-1"></i>Parcourir
          </button>
        </div>
      </div>
    </div>

    <!-- 5. CONTACT -->
    <div class="form-card mb-4">
      <div class="form-card-header">
        <div class="form-card-icon"><i class="bi bi-telephone"></i></div>
        <div>
          <h4 class="form-card-title">5. Contact &amp; Réseaux sociaux</h4>
          <p class="form-card-subtitle">Ces informations seront visibles par les clients. Toutes optionnelles.</p>
        </div>
        <span class="form-badge">Optionnel</span>
      </div>
      <div class="row g-3 mt-2">
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Téléphone</label>
          <div class="input-group-custom">
            <i class="bi bi-telephone input-icon"></i>
            <?php echo '<input type="text" class="custom-input" name="tel" placeholder="+212 6 00 00 00 00" value="' . (isset($_POST['tel']) ? $_POST['tel'] : '') . '">'; ?>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Instagram</label>
          <div class="input-group-custom">
            <i class="bi bi-instagram input-icon"></i>
            <?php echo '<input type="text" class="custom-input" name="insta" placeholder="@votre_boutique" value="' . (isset($_POST['insta']) ? $_POST['insta'] : '') . '">'; ?>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label fw-bold">Facebook</label>
          <div class="input-group-custom">
            <i class="bi bi-facebook input-icon"></i>
            <?php echo '<input type="text" class="custom-input" name="fb" placeholder="fb.com/votre_boutique" value="' . (isset($_POST['fb']) ? $_POST['fb'] : '') . '">'; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- RÈGLES -->
    <div class="rules-card mb-4">
      <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-shield-check fs-5" style="color:var(--green);"></i>
        <h5 class="mb-0" style="color:var(--green);">Règles de la plateforme</h5>
      </div>
      <ul class="rules-list">
        <li>Utilisez des images de produits de haute qualité.</li>
        <li>Ne publiez pas de contenu protégé par des droits d'auteur.</li>
        <li>Gardez votre identité de marque propre et professionnelle.</li>
        <li>Les produits bio et écoresponsables sont privilégiés.</li>
        <li>Respectez les standards de la communauté Green Market.</li>
      </ul>
    </div>

    <div class="d-flex justify-content-end gap-3 mb-5">
      <a href="../Producteur/Producteur.php" class="btn btn-annuler">Annuler</a>
      <button type="submit" name="create" class="btn btn-creer">
        <i class="bi bi-check-lg me-1"></i>Créer ma boutique
      </button>
    </div>

  </form>
</div>

<?php render_footer(); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="Create Market.js"></script>
</body>
</html>