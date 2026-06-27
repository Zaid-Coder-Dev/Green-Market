<?php
session_start();
include("../connexion.php");

if(!isset($_SESSION['id_utili'])){
    header("Location: auth.php");
    exit;
}

$id_utili = $_SESSION['id_utili'];

$message_success = "";
$message_error = "";

if($_SERVER['REQUEST_METHOD']=="POST"){
  extract($_POST);
  if (isset($create)) {

      if(!isset($nom) || empty($nom)) $err['nom']='Veuillez saisir nom de boutique'; 
      else{
        try{
            $req = $pdo->prepare("SELECT nom_boutique FROM boutique WHERE ID_utili = ? ");
            $req->execute([$id_utili]);
            if($req->rowCount()!=0) $err['nom']="cette nom deja existe ";
        }catch(PDOException $e){
            die('error delection reference :'.$e->getMessage());
        }
      }
      if(!isset($categorie) || empty($categorie)) $err['categorie']='veuillez choisir une catgorie ';
      if(!isset($ville) || empty($ville)) $err['ville']='veuillez choisir une Ville ';
      if(!isset($des) || empty($des)) $err['des']='Veuillez saisir description';
      if(!isset($tel) || empty($tel)) $err['tel']='Veuillez saisir votre numéro de téléphone.';
      if(!isset($insta) || empty($insta)) $err['insta']='Veuillez saisir le lien de votre compte Instagram.';
      if(!isset($fb) || empty($fb)) $err['fb']='Veuillez saisir le lien de votre page Facebook.';
      
      $exts=['image/png','image/jpg','image/jpeg','image/gif','image/tiff','image/jfif'];
      if($_FILES['logo']['error']!=0) $err['logo']="Erreur lors du téléchargement de logo";
      elseif(!in_array($_FILES['logo']['type'],$exts))$err['logo']="Veuillez choisir une logo au format GIF, TIFF, PNG, JPG ou JPEG.";
      elseif($_FILES['logo']['size']>40*1024*1024) $err['logo']="La taille de l'image ne doit pas dépasser 40 Mo.";
      
      if(isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {

        foreach($_FILES['images']['error'] as $key => $error){
          if($error != 0){
              $err['images'] = "Erreur lors du téléchargement de l'image";
              break;
          }
          elseif(!in_array($_FILES['images']['type'][$key], $exts)){
              $err['images'] = "Format d'image non autorisé";
              break;
          }
          elseif($_FILES['images']['size'][$key] > 40*1024*1024){
              $err['images'] = "La taille de l'image ne doit pas dépasser 40 Mo.";
              break;
          }
      }
    }

      if(empty($err)){
        $nom= htmlspecialchars(trim($nom)); 
        $ville= htmlspecialchars(trim($ville)); 
        $des= htmlspecialchars(trim($des));

        $chemin = "../uploads/boutiques_images/";
        $ru=move_uploaded_file($_FILES['logo']['tmp_name'],$chemin.$_FILES['logo']['name']);
        if($ru==False ) {$err['logo']="Erreur lors du téléchargement du logo.";exit;}
        else{
          
          try {
              $rebou = $pdo->prepare("INSERT INTO boutique (nom_boutique, description_boutique, ville, logo, telephone, FB_link, Insta_link, ID_utili) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
              $rebou = $rebou->execute([$nom, $des, $ville, $chemin.$_FILES['logo']['name'], $tel, $fb, $insta, $id_utili]);
              if($rebou == False ){
                  $message_error = "Erreur lors de l'enregistrement :";
              }
              else {
                  $chemin = "../assets/images/boutiques/";
                  foreach($_FILES['images']['tmp_name'] as $key => $tmp){
                    $rues=move_uploaded_file($_FILES['images']['tmp_name'][$key],$chemin.$_FILES['images']['name'][$key]);
                    if($ru==False ) {$err['logo']="Erreur lors du téléchargement du image.";exit;}
                     else{
                      $id_boutique = $pdo->lastInsertId();
                      $reimg = $pdo->prepare("INSERT INTO image_boutique (ID_boutique, image)VALUES (?, ?)");
                      $reimg->execute([$id_boutique,$chemin.$_FILES['images']['name'][$key]]);
                     }
                  }
                  $_SESSION['success'] = "Votre boutique a été créée avec succès !";
                  header("Location: ../Profile Producteur/Producteur.php");
              }
        }catch(PDOException $e) {die ("erreur creer un boutique : ".$e->getMessage());}
        }
      }
  }
}     
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
  <link rel="stylesheet" href="creer-boutique.css">
</head>
<body>

  <section class="py-4 page-header">
    <div class="container">
      <small class="text-uppercase label-orange">Producteur</small>
      <h2 class="mb-0">Créer ma boutique</h2>
      <p class="text-muted mb-0">Remplissez les informations ci-dessous pour lancer votre boutique sur Green Market</p>
    </div>
  </section>

  <div class="container my-5">
    
    <?php if(!empty($message_error)): ?>
      <div class='alert alert-success alert-dismissible fade show' role='alert'>
        <?= $message_error ?>
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
      </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">

      <div class="form-card mb-4">
        <div class="form-card-header">
          <div class="form-card-icon">
            <i class="bi bi-shop"></i>
          </div>
          <div>
            <h4 class="form-card-title">1. Informations de base</h4>
            <p class="form-card-subtitle">Donnez une identité professionnelle à votre boutique.</p>
          </div>
          <span class="form-badge">Requis</span>
        </div>

        <div class="row g-3 mt-2">
          <div class="col-12">
            <label class="form-label fw-bold">Nom de la boutique</label>
            <?php if (isset($err['nom'])) echo "<div class='text-danger small mt-1'>" . $err['nom'] . "</div>" ?>
            <div class="input-group-custom">
              <i class="bi bi-shop input-icon"></i>
              
              <input type="text" class="custom-input" name="nom" placeholder="Ex: Coopérative Tiznit" required>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label fw-bold">Ville</label>
            <div class="input-group-custom">
              <?php if (isset($err['ville'])) echo "<div class='text-danger small mt-1'>" . $err['ville'] . "</div>" ?>
              <i class="bi bi-geo-alt input-icon"></i>
              
              <select class="custom-input" name="ville" required>
                <option value="">Sélectionner une ville</option>
                <option value="Casablanca">Casablanca</option>
                <option value="Rabat">Rabat</option>
                <option value="Marrakech">Marrakech</option>
                <option value="Fès">Fès</option>
                <option value="Agadir">Agadir</option>
                <option value="Tiznit">Tiznit</option>
                <option value="Essaouira">Essaouira</option>
                <option value="Ouarzazate">Ouarzazate</option>
              </select>
            </div>
          </div>
          
          <div class="col-12 col-md-6">
            <label class="form-label fw-bold">Catégorie principale</label>
            <div class="input-group-custom">
              <?php if (isset($err['categorie'])) echo "<div class='text-danger small mt-1'>" . $err['categorie'] . "</div>" ?>
              <i class="bi bi-grid input-icon"></i>
              
              <select class="custom-input" name="categorie">
                <option disabled selected>Toutes catégories</option>
                <?php 
                  include("../connexion.php");
                  try{
                      $recat = $pdo->query("SELECT ID_Categ, nom_Categ FROM categorie");
                      $tab_cat = $recat->fetchAll(PDO::FETCH_NUM);
                      foreach($tab_cat as $categ){
                          echo "<option value='$categ[0]'>$categ[1]</option>";
                      }
                  }
                  catch(PDOException $e){die("Erreur chargement categorie :".$e->getMessage());}
                ?>
              </select>
            </div>
          </div>
          
          <div class="col-12">
            <label class="form-label fw-bold">description de la boutique</label>
            <?php if (isset($err['des'])) echo "<div class='text-danger small mt-1'>" . $err['des'] . "</div>" ?>
            <div class="textarea-group-custom">
              
              <textarea class="custom-textarea" name="des" rows="4" placeholder="Décrivez votre boutique, vos produits et votre savoir-faire artisanal..." required></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-card-header">
          <div class="form-card-icon">
            <i class="bi bi-image"></i>
          </div>
          <div>
            <h4 class="form-card-title">2. Logo de la boutique</h4>
            <p class="form-card-subtitle">Ajoutez un logo professionnel pour représenter votre boutique.</p>
          </div>
          <span class="form-badge">PNG / JPG</span>
        </div>
        <?php if (isset($err['logo'])) echo "<div class='text-danger small mt-1'>" . $err['logo'] . "</div>" ?>
        <div class="upload-zone mt-3" id="uploadArea" style="position: relative; cursor: pointer;">
          <input type="file" id="logoInput" name="logo" hidden accept="image/*">
          <div class="upload-content" id="uploadContent">
            <div class="upload-icon-circle">
              <i class="bi bi-cloud-upload"></i>
            </div>
            <h6 class="mt-3 mb-1">Glissez votre logo ici</h6>
            <p class="text-muted small">ou cliquez pour parcourir vos fichiers</p>
            <button type="button" class="btn btn-sm btn-parcourir mt-2" id="browseBtn">
              <i class="bi bi-folder2-open me-1"></i>Parcourir
            </button>
          </div>
          <img id="previewLogo" src="" alt="Logo preview" style="display: none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; border-radius: 14px;">
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-card-header">
          <div class="form-card-icon"><i class="bi bi-images"></i></div>
            <div>
              <h4 class="form-card-title">3. Images de la boutique</h4>
              <p class="form-card-subtitle">Ces images seront affichées sur votre page boutique.</p>
            </div>
            <button type="button" class="btn btn-sm btn-add-img" id="addImagesBtn">
              <i class="bi bi-plus-lg me-1"></i>Ajouter
            </button>
            <input type="file" id="imagesInput" name="images[]" multiple hidden accept="image/*">
        </div>
        <?php if (isset($err['images'])) echo "<div class='text-danger small mt-1'>" . $err['images'] . "</div>" ?>

        <div class="images-layout mt-3">
          <div class="images-grid" id="imagesGrid">
             <p class="text-muted small p-3" id="noImagesText">Aucune image sélectionnée</p>
          </div>

          <div class="preview-card">
            <p class="fw-bold mb-2" style="color: var(--green);">Aperçu</p>
            <div class="preview-image-box">
              <img id="previewImage" src="" alt="Aperçu" style="display:none;">
              <p class="text-muted small" id="noPreviewText">Sélectionnez des images pour voir l'aperçu</p>
              <button type="button" class="preview-arrow left" id="prevBtn" style="display:none;"><i class="bi bi-chevron-left"></i></button>
              <button type="button" class="preview-arrow right" id="nextBtn" style="display:none;"><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-card-header">
          <div class="form-card-icon">
            <i class="bi bi-telephone"></i>
          </div>
          <div>
            <h4 class="form-card-title">4. Contact &amp; Réseaux sociaux</h4>
            <p class="form-card-subtitle">Ces informations seront visibles par les clients sur votre boutique.</p>
          </div>
          <span class="form-badge">Public</span>
        </div>

        <div class="row g-3 mt-2">
          <div class="col-12 col-md-6">
            <label class="form-label fw-bold">Téléphone</label>
            <?php if (isset($err['tel'])) echo "<div class='text-danger small mt-1'>" . $err['tel'] . "</div>" ?>
            <div class="input-group-custom">
              <i class="bi bi-telephone input-icon"></i>
              <input type="text" class="custom-input" name="tel" placeholder="+212 6 00 00 00 00">
            </div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label fw-bold">instagram</label>
            <?php if (isset($err['insta'])) echo "<div class='text-danger small mt-1'>" . $err['insta'] . "</div>" ?>
            <div class="input-group-custom">
              <i class="bi bi-instagram input-icon"></i>
              <input type="text" class="custom-input" name="insta" placeholder="@votre_boutique">
            </div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label fw-bold">facebook</label>
            <?php if (isset($err['fb'])) echo "<div class='text-danger small mt-1'>" . $err['fb'] . "</div>" ?>
            <div class="input-group-custom">
              <i class="bi bi-facebook input-icon"></i>
              <input type="text" class="custom-input" name="fb" placeholder="fb.com/votre_boutique">
            </div>
          </div>
        </div>
      </div>


      <div class="rules-card mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-shield-check fs-5" style="color: var(--green);"></i>
          <h5 class="mb-0" style="color: var(--green);">Règles de la plateforme</h5>
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
        <a href="../Producteur/Producteur.html" class="btn btn-annuler">Annuler</a>
        <button type="submit" name="create" class="btn btn-creer" id="createBtn">
          <i class="bi bi-check-lg me-1"></i>Créer ma boutique
        </button>
      </div>

    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script > // 1. Logic dyal Logo Upload
    const uploadArea = document.getElementById('uploadArea');
    const logoInput = document.getElementById('logoInput');
    const browseBtn = document.getElementById('browseBtn');
    const previewLogo = document.getElementById('previewLogo');
    const uploadContent = document.getElementById('uploadContent');

    browseBtn.addEventListener('click', (e) => { e.stopPropagation(); logoInput.click(); });
    uploadArea.addEventListener('click', () => { logoInput.click(); });

    logoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewLogo.src = e.target.result;
                previewLogo.style.display = 'block';
                uploadContent.style.opacity = '0';
            }
            reader.readAsDataURL(file);
        }
    });

    // 2. Logic dyal Multi-Images (Images de la boutique) & Gallery Slider
    const addImagesBtn = document.getElementById('addImagesBtn');
    const imagesInput = document.getElementById('imagesInput');
    const imagesGrid = document.getElementById('imagesGrid');
    const noImagesText = document.getElementById('noImagesText');
    const previewImage = document.getElementById('previewImage');
    const noPreviewText = document.getElementById('noPreviewText');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    let selectedFiles = [];
    let currentPreviewIndex = 0;

    addImagesBtn.addEventListener('click', () => { imagesInput.click(); });

    imagesInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        if(files.length > 0) {
            noImagesText.style.display = 'none';
        }
        
        files.forEach(file => {
            selectedFiles.push(file);
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const imgIndex = selectedFiles.length - 1;
                
                // Creer element dyal image Grid
                const imgDiv = document.createElement('div');
                imgDiv.className = 'market-image';
                imgDiv.setAttribute('data-index', imgIndex);
                imgDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Image boutique">
                    <div class="image-actions">
                        <button type="button" class="img-action-btn delete-btn" onclick="removeGalleryImage(${imgIndex})"><i class="bi bi-trash"></i></button>
                    </div>
                `;
                imagesGrid.appendChild(imgDiv);
                
                // Trigerry l-aperçu l-awal
                if(selectedFiles.length === 1) {
                    updatePreview(0);
                }
            }
            reader.readAsDataURL(file);
        });
    });

    function updatePreview(index) {
        if(selectedFiles.length > 0 && selectedFiles[index]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
                noPreviewText.style.display = 'none';
                
                // Afficher les flèches si plusieurs images
                if(selectedFiles.length > 1) {
                    prevBtn.style.display = 'block';
                    nextBtn.style.display = 'block';
                } else {
                    prevBtn.style.display = 'none';
                    nextBtn.style.display = 'none';
                }
            }
            reader.readAsDataURL(selectedFiles[index]);
            currentPreviewIndex = index;
        } else {
            previewImage.style.display = 'none';
            noPreviewText.style.display = 'block';
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        }
    }

    // Navigation Carousel
    nextBtn.addEventListener('click', () => {
        let nextIndex = currentPreviewIndex + 1;
        if(nextIndex >= selectedFiles.length) nextIndex = 0;
        updatePreview(nextIndex);
    });

    prevBtn.addEventListener('click', () => {
        let prevIndex = currentPreviewIndex - 1;
        if(prevIndex < 0) prevIndex = selectedFiles.length - 1;
        updatePreview(prevIndex);
    });

    // Fonction bach tmseh tswira mn l-gallery qbel l-upload
    window.removeGalleryImage = function(index) {
        selectedFiles.splice(index, 1);
        
        // Re-render grid
        imagesGrid.innerHTML = '';
        if(selectedFiles.length === 0) {
            noImagesText.style.display = 'block';
            updatePreview(0);
        } else {
            selectedFiles.forEach((file, i) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgDiv = document.createElement('div');
                    imgDiv.className = 'market-image';
                    imgDiv.setAttribute('data-index', i);
                    imgDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Image boutique">
                        <div class="image-actions">
                            <button type="button" class="img-action-btn delete-btn" onclick="removeGalleryImage(${i})"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                    imagesGrid.appendChild(imgDiv);
                }
                reader.readAsDataURL(file);
            });
            updatePreview(0);
        }
    };
  </script>
  
</body>
</html>