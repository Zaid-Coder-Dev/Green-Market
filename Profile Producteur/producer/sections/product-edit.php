<!-- MODIFIER PRODUIT -->
<?php
$id_utili = $_SESSION['id_utili'];
$reqBout = $pdo->prepare("SELECT ID_boutique FROM boutique WHERE ID_utili = ?");
$reqBout->execute([$id_utili]);
$idbou = $reqBout->fetchColumn();

$tprod = [];
if (isset($_GET['id'])) {
    try {
        $resid = $pdo->prepare("SELECT * FROM produit WHERE ID_Prod = ?");
        $resid->execute([$_GET['id']]);
        $tprod = $resid->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur de selection produit : " . $e->getMessage());
    }
}

$err = [];
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    extract($_POST);
    if (isset($proEdit)) {
        if (!isset($nomprod) || empty($nomprod)) $err['nomprod'] = 'Veuillez saisir nom de produit';
        if (!isset($prix) || empty($prix)) $err['prix'] = 'Veuillez saisir prix de produit';
        elseif ($prix <= 0) $err['prix'] = "le prix ne doit pas etre negatif ou null";
        if (!isset($cat) || empty($cat)) $err['cat'] = 'veuillez choisir une categorie';
        if (!isset($qte) || $qte == "") $err['qte'] = 'Veuillez entrer la quantite';
        elseif ($qte < 0) $err['qte'] = "la quantite ne doit pas etre negatif";
        if (!isset($des) || empty($des)) $err['des'] = 'Veuillez saisir description';

        $nouvelleImg = isset($lastimg) ? $lastimg : '';
        if ($_FILES['img']['error'] == 0) {
            $exts = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif', 'image/tiff', 'image/jfif'];
            if (!in_array($_FILES['img']['type'], $exts)) $err['img'] = "Format d'image invalide.";
            elseif ($_FILES['img']['size'] > 40 * 1024 * 1024) $err['img'] = "La taille de l'image ne doit pas dépasser 40 Mo.";
        }

        if (!empty($err)) {
            $openSection = "modifier-produit";
        }

        if (empty($err)) {
            $nomprod = trim($nomprod);
            $des = trim($des);

            if ($_FILES['img']['error'] == 0 && empty($err['img'])) {
                $imgName = time() . '_' . basename($_FILES['img']['name']);
                $uploadDir = '../uploads/produits_images/';
                $ru = move_uploaded_file($_FILES['img']['tmp_name'], $uploadDir . $imgName);
                if ($ru == false) {
                    $err['img'] = "L'image n'a pas pu être chargée";
                    $openSection = "modifier-produit";
                } else {
                    $nouvelleImg = $uploadDir . $imgName;
                }
            }

            if (empty($err)) {
                try {
                    $ri = $pdo->prepare("UPDATE produit SET nom_Prod=?, description_Prod=?, Prix=?, Stock=?, Prod_img=?, ID_Categ=? WHERE ID_Prod=?");
                    $ri->execute([$nomprod, $des, $prix, $qte, $nouvelleImg, $cat, $_GET['id']]);
                    $_SESSION['success'] = "Produit modifié avec succès !";
                    $openSection = "produits";
                } catch (PDOException $e) {
                    die("erreur modification produit : " . $e->getMessage());
                }
            }
        }
    }
}
?>
<div class="section d-none" id="modifier-produit">
    <div class="products-page">
        <div class="stock-header">
            <div>
                <h2 class="mb-1">Modifier un produit</h2>
                <p class="subtitle mb-0">Modifiez facilement les informations du produit.</p>
            </div>
            <button class="add-btn" data-section="produits" type="button">
                <i class="bi bi-arrow-left me-2"></i>Retour
            </button>
        </div>
        <div class="content-card">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row g-4">
                    <h5 class="form-section-title">Informations produit</h5>
                    <div class="col-12">
                        <label class="form-label">Image du produit</label>
                        <?php if (isset($err['img'])) echo "<div class='text-danger small mt-1'>" . $err['img'] . "</div>" ?>
                        <div class="upload-box text-center p-4 border border-2 rounded-3 position-relative">
                            <i class="bi bi-cloud-arrow-up display-4 text-secondary mb-2"></i>
                            <h5 class="mt-2 fw-semibold">Glissez votre image ici</h5>
                            <p class="text-muted small">ou cliquez pour sélectionner</p>
                            <input type="hidden" name="lastimg" value="<?= isset($tprod['Prod_img']) ? $tprod['Prod_img'] : '' ?>">
                            <div class="image-preview-wrapper my-3">
                                <img id="preview" src="<?= isset($tprod['Prod_img']) ? $tprod['Prod_img'] : '' ?>" class="img-thumbnail rounded" style="width:150px;height:150px;object-fit:cover;">
                            </div>
                            <input type="file" name="img" id="imgInput" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer;" />
                        </div>
                        <script>
                        document.getElementById('imgInput').addEventListener('change', function() {
                            var file = this.files[0];
                            if (file) {
                                document.getElementById('preview').src = URL.createObjectURL(file);
                            }
                        });
                        </script>
                    </div>

                    <div class="col-md-6">
                        <?php if (isset($err['nomprod'])) echo "<div class='text-danger small mt-1'>" . $err['nomprod'] . "</div>" ?>
                        <label class="form-label">Nom du produit</label>
                        <input class="form-control" name="nomprod" placeholder="Nom du produit" type="text" value="<?= isset($tprod['nom_Prod']) ? $tprod['nom_Prod'] : '' ?>" />
                    </div>

                    <div class="col-md-6">
                        <?php if (isset($err['cat'])) echo "<div class='text-danger small mt-1'>" . $err['cat'] . "</div>" ?>
                        <label class="form-label">Catégorie</label>
                        <select class="form-select" name="cat">
                            <option disabled>Toutes catégories</option>
                            <?php
                            try {
                                $recat = $pdo->query("SELECT ID_Categ, nom_Categ FROM categorie");
                                $tab_cat = $recat->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($tab_cat as $categ) {
                                    $sel = ($categ['ID_Categ'] == (isset($tprod['ID_Categ']) ? $tprod['ID_Categ'] : 0)) ? 'selected' : '';
                                    echo "<option value='" . $categ['ID_Categ'] . "' $sel>" . $categ['nom_Categ'] . "</option>";
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
                        <label class="form-label">Prix (MAD)</label>
                        <input class="form-control" name="prix" placeholder="0.00" type="number" step="0.01" value="<?= isset($tprod['Prix']) ? $tprod['Prix'] : '' ?>" />
                    </div>

                    <div class="col-md-4">
                        <?php if (isset($err['qte'])) echo "<div class='text-danger small mt-1'>" . $err['qte'] . "</div>" ?>
                        <label class="form-label">Quantité stock</label>
                        <input class="form-control" name="qte" placeholder="0" type="number" value="<?= isset($tprod['Stock']) ? $tprod['Stock'] : '' ?>" />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">ID Produit</label>
                        <input class="form-control" disabled type="text" value="#PRD-<?= isset($tprod['ID_Prod']) ? $tprod['ID_Prod'] : '' ?>" />
                    </div>

                    <h5 class="form-section-title">Description</h5>
                    <div class="col-12">
                        <?php if (isset($err['des'])) echo "<div class='text-danger small mt-1'>" . $err['des'] . "</div>" ?>
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="des" placeholder="Description du produit..."><?= isset($tprod['description_Prod']) ? $tprod['description_Prod'] : '' ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <button class="add-btn" type="submit" name="proEdit">
                                <i class="bi bi-check-circle me-2"></i>Modifier produit
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>