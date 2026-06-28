<!-- AJOUTER PRODUIT -->
<?php
$id_utili = $_SESSION['id_utili'];
$reqBout = $pdo->prepare("SELECT ID_boutique FROM boutique WHERE ID_utili = ?");
$reqBout->execute([$id_utili]);
$idbou = $reqBout->fetchColumn();

if (!$idbou) {
    $err['general'] = "Vous n'avez pas encore de boutique. Créez votre boutique d'abord.";
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    extract($_POST);
    if (isset($proAdd)) {
        $err = [];
        if (!isset($nomprod) || empty($nomprod)) $err['nomprod'] = 'Veuillez saisir nom de produit';
        if (!isset($prix) || empty($prix)) $err['prix'] = 'Veuillez saisir prix de produit';
        elseif ($prix <= 0) $err['prix'] = "le prix ne doit pas etre negatif ou null";
        if (!isset($cat) || empty($cat)) $err['cat'] = 'veuillez choisir une categorie';
        if (!isset($qte) || empty($qte)) $err['qte'] = 'Veuillez entrer la quantite';
        elseif ($qte < 0) $err['qte'] = "la quantite ne doit pas etre negatif";
        if (!isset($des) || empty($des)) $err['des'] = 'Veuillez saisir description';

        // Check main image
        $exts = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/tiff', 'image/jfif', 'image/webp'];
        if ($_FILES['img']['error'] != 0) $err['img'] = "Erreur lors du téléchargement de l'image principale";
        elseif (!in_array($_FILES['img']['type'], $exts)) $err['img'] = "Veuillez choisir une image au format GIF, TIFF, PNG, JPG ou JPEG.";
        elseif ($_FILES['img']['size'] > 40 * 1024 * 1024) $err['img'] = "La taille de l'image ne doit pas dépasser 40 Mo.";

        // Check gallery images
        $gallery_images = [];
        if (isset($_FILES['gallery_images'])) {
            $total_files = count($_FILES['gallery_images']['name']);
            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['gallery_images']['error'][$i] == 0) {
                    if (!in_array($_FILES['gallery_images']['type'][$i], $exts)) {
                        $err['gallery'] = "L'image " . ($i+1) . " a un format non supporté.";
                        break;
                    } elseif ($_FILES['gallery_images']['size'][$i] > 40 * 1024 * 1024) {
                        $err['gallery'] = "L'image " . ($i+1) . " dépasse 40 Mo.";
                        break;
                    } else {
                        $gallery_images[] = $i;
                    }
                }
            }
        }

        if (!empty($err)) {
            $openSection = "ajouter-produit";
        }

        if (empty($err)) {
            $nomprod = trim($nomprod);
            $des = trim($des);
            
            // Upload main image
            $imgName = time() . '_' . basename($_FILES['img']['name']);
            $uploadDir = '../uploads/produits_images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $ru = move_uploaded_file($_FILES['img']['tmp_name'], $uploadDir . $imgName);
            if ($ru == false) {
                $err['img'] = "L'image n'a pas pu être chargée";
                $openSection = "ajouter-produit";
            } else {
                try {
                    $pdo->beginTransaction();
                    
                    // Insert product
                    $ri = $pdo->prepare("INSERT INTO produit(nom_Prod, description_Prod, Prix, Stock, Prod_img, ID_boutique, ID_Categ) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $ri->execute([$nomprod, $des, $prix, $qte, 'uploads/produits_images/' . $imgName, $idbou, $cat]);
                    $product_id = $pdo->lastInsertId();
                    
                    // Upload gallery images
                    if (!empty($gallery_images)) {
                        $rg = $pdo->prepare("INSERT INTO produit_image (image, ID_Prod) VALUES (?, ?)");
                        foreach ($gallery_images as $index) {
                            $gallery_name = time() . '_' . $index . '_' . basename($_FILES['gallery_images']['name'][$index]);
                            $gallery_upload = move_uploaded_file($_FILES['gallery_images']['tmp_name'][$index], $uploadDir . $gallery_name);
                            if ($gallery_upload) {
                                $rg->execute(['uploads/produits_images/' . $gallery_name, $product_id]);
                            }
                        }
                    }
                    
                    $pdo->commit();
                    $_SESSION['success'] = "Produit inséré avec succès !";
                    $openSection = "produits";
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    die("erreur insertion produit : " . $e->getMessage());
                }
            }
        }
    }
}
?>
<div class="section d-none" id="ajouter-produit">
    <div class="products-page">
        <div class="stock-header">
            <div>
                <h2 class="mb-1">Ajouter un produit</h2>
                <p class="subtitle mb-0">Ajoutez facilement un nouveau produit.</p>
            </div>
            <button class="add-btn" data-section="produits" type="button">
                <i class="bi bi-arrow-left me-2"></i>Retour
            </button>
        </div>
        <div class="content-card">
            <?php if (isset($err['general'])) { ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= $err['general'] ?>
                    <a href="../Create Market/Create Market.php" class="alert-link ms-2">Créer ma boutique</a>
                </div>
            <?php } ?> 
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row g-4">
                    <h5 class="form-section-title">Informations produit</h5>
                    
                    <!-- Main Image -->
                    <div class="col-12">
                        <label class="form-label">Image principale du produit <span class="text-danger">*</span></label>
                        <?php if (isset($err['img'])) echo "<div class='text-danger small mt-1'>" . $err['img'] . "</div>" ?>
                        <div class="upload-box text-center">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <h5 class="mt-3">Glissez votre image principale ici</h5>
                            <p>ou cliquez pour sélectionner</p>
                            <input type="file" name="img" accept="image/*" required />
                        </div>
                    </div>

                    <!-- Gallery Images (Multiple) -->
                    <div class="col-12">
                        <label class="form-label">Images supplémentaires (galerie) <span class="text-muted">(optionnel, jusqu'à 5 images)</span></label>
                        <?php if (isset($err['gallery'])) echo "<div class='text-danger small mt-1'>" . $err['gallery'] . "</div>" ?>
                        <div class="upload-box text-center" style="border-style: dashed;">
                            <i class="bi bi-images"></i>
                            <h5 class="mt-3">Ajoutez des images de galerie</h5>
                            <p>Vous pouvez sélectionner plusieurs images</p>
                            <input type="file" name="gallery_images[]" accept="image/*" multiple />
                            <small class="text-muted d-block mt-2">Formats acceptés: PNG, JPG, JPEG, GIF, WebP (Max 5 images)</small>
                        </div>
                        <div id="galleryPreview" class="row g-2 mt-3"></div>
                    </div>

                    <div class="col-md-6">
                        <?php if (isset($err['nomprod'])) echo "<div class='text-danger small mt-1'>" . $err['nomprod'] . "</div>" ?>
                        <label class="form-label">Nom du produit <span class="text-danger">*</span></label>
                        <input class="form-control" name="nomprod" placeholder="Nom du produit" type="text" value="<?= isset($nomprod) ? $nomprod : '' ?>" required />
                    </div>

                    <div class="col-md-6">
                        <?php if (isset($err['cat'])) echo "<div class='text-danger small mt-1'>" . $err['cat'] . "</div>" ?>
                        <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                        <select class="form-select" name="cat" required>
                            <option value="" disabled selected>Sélectionnez une catégorie</option>
                            <?php
                            try {
                                $recat = $pdo->query("SELECT ID_Categ, nom_Categ FROM categorie ORDER BY nom_Categ");
                                $tab_cat = $recat->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($tab_cat as $categ) {
                                    $selected = (isset($cat) && $cat == $categ['ID_Categ']) ? 'selected' : '';
                                    echo "<option value='" . $categ['ID_Categ'] . "' $selected>" . $categ['nom_Categ'] . "</option>";
                                }
                            } catch (PDOException $e) {
                                die("Erreur chargement categorie : " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>

                    <h5 class="form-section-title">Stock &amp; Prix</h5>

                    <div class="col-md-4">
                        <?php if (isset($err['prix'])) echo "<div class='text-danger small mt-1'>" . $err['prix'] . "</div>" ?>
                        <label class="form-label">Prix (MAD) <span class="text-danger">*</span></label>
                        <input class="form-control" name="prix" placeholder="0.00" type="number" step="0.01" min="0" value="<?= isset($prix) ? $prix : '' ?>" required />
                    </div>

                    <div class="col-md-4">
                        <?php if (isset($err['qte'])) echo "<div class='text-danger small mt-1'>" . $err['qte'] . "</div>" ?>
                        <label class="form-label">Quantité stock <span class="text-danger">*</span></label>
                        <input class="form-control" name="qte" placeholder="0" type="number" min="0" value="<?= isset($qte) ? $qte : '' ?>" required />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">ID Boutique</label>
                        <input class="form-control" disabled type="text" value="<?= $idbou ?>" />
                    </div>

                    <h5 class="form-section-title">Description</h5>
                    <div class="col-12">
                        <?php if (isset($err['des'])) echo "<div class='text-danger small mt-1'>" . $err['des'] . "</div>" ?>
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="des" placeholder="Description du produit..." rows="4" required><?= isset($des) ? $des : '' ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <button class="add-btn" type="submit" name="proAdd">
                                <i class="bi bi-check-circle me-2"></i>Ajouter produit
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Gallery image preview
document.querySelector('input[name="gallery_images[]"]').addEventListener('change', function(e) {
    const preview = document.getElementById('galleryPreview');
    preview.innerHTML = '';
    const files = this.files;
    
    if (files.length > 5) {
        alert('Vous ne pouvez sélectionner que 5 images maximum.');
        this.value = '';
        return;
    }
    
    for (let i = 0; i < files.length; i++) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-2';
            col.innerHTML = `
                <div class="position-relative">
                    <img src="${e.target.result}" class="img-fluid rounded" style="height: 80px; object-fit: cover; width: 100%;">
                    <span class="position-absolute top-0 start-100 translate-middle badge bg-primary rounded-pill">${i+1}</span>
                </div>
            `;
            preview.appendChild(col);
        }
        reader.readAsDataURL(files[i]);
    }
});

// Main image preview
document.querySelector('input[name="img"]').addEventListener('change', function(e) {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.upload-box');
            // Remove existing preview if any
            const existingPreview = preview.querySelector('img');
            if (existingPreview) {
                existingPreview.remove();
            }
            // Add new preview
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxHeight = '150px';
            img.style.marginTop = '10px';
            img.style.borderRadius = '8px';
            preview.appendChild(img);
            
            // Hide the icon and text
            const icon = preview.querySelector('i');
            const h5 = preview.querySelector('h5');
            const p = preview.querySelector('p');
            if (icon) icon.style.display = 'none';
            if (h5) h5.style.display = 'none';
            if (p) p.style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<style>
.upload-box {
    border: 2px dashed #d1c7b8;
    border-radius: 12px;
    padding: 30px 20px;
    background: #faf8f5;
    transition: all 0.2s ease;
    position: relative;
    cursor: pointer;
}

.upload-box:hover {
    border-color: #2d4a2d;
    background: #f5f0ea;
}

.upload-box input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.upload-box i {
    font-size: 2.5rem;
    color: #2d4a2d;
    opacity: 0.6;
}

.upload-box h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
}

.upload-box p {
    font-size: 0.85rem;
    color: #888;
    margin-bottom: 0;
}

#galleryPreview img {
    border: 1px solid #eee;
}

#galleryPreview .badge {
    font-size: 0.6rem;
    padding: 2px 6px;
}
</style>