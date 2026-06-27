                <!-- MODIFIER -->
                <?php
                include("../config/database.php");
                $id_utili = $_SESSION['id_utili'];
                $reqBout = $pdo->prepare("SELECT ID_boutique 
                                        FROM boutique 
                                        WHERE ID_utili = ?
                ");
                $reqBout->execute([$id_utili]);
                $idbou = $reqBout->fetchColumn();

                if($_SERVER['REQUEST_METHOD']=="POST"){
                    extract($_POST);
                    if(isset($proEdit)){
                        
                        $err= [];
                        #verification des donnees 
                        if(!isset($nomprod) || empty($nomprod)) $err['nomprod']='Veuillez saisir nom de produit';
                        if(!isset($prix) || empty($prix )) $err['prix']='Veuillez saisir prix de produit';
                        elseif($prix<=0 ) $err['prix'] = "le prix ne doit pas etre negatif ou null" ; 
                        if(!isset($cat) || empty($cat )) $err['cat']='veuillez choisir une catgorie ';
                        if(!isset($qte) || $qte=="" ) $err['qte']='Veuillez entrer la quantite';
                        elseif($qte<0 ) $err['qte'] = "la quantite ne doit pas etre negatif" ;
                        if(!isset($des) || empty($des)) $err['des']='Veuillez saisir description'; 
                        if(!isset($idbou) || empty($idbou)) $err['idbou']='Veuillez saisir id boutique'; 
                        #Verification img
                        $exts=['image/png','image/jpg','image/jpeg','image/gif','image/tiff','image/jfif'];
                        if($_FILES['img']['error']!=0) $err['img']="Erreur lors du téléchargement de l'image";
                        elseif(!in_array($_FILES['img']['type'],$exts))$err['img']="Veuillez choisir une image au format GIF, TIFF, PNG, JPG ou JPEG.";
                        elseif($_FILES['img']['size']>40*1024*1024) $err['img']="La taille de l'image ne doit pas dépasser 40 Mo.";

                        if(!empty($err)){
                            $openSection = "modifier-produit";
                        }

                        if(empty($err)){
                            #nettoyage des donnees 
                            $nomprod= htmlspecialchars(trim($nomprod)); 
                            $des= htmlspecialchars(trim($des));
                            #deplacement de l'image du dossier tmp au dossier de l'application 
                            $ru=move_uploaded_file($_FILES['img']['tmp_name'],"./photo/".$_FILES['img']['name']);
                            if($ru==False ) {$err['img']="l'image pas bien ete chargé";exit;}
                            else {
                                try {
                                    $ri = $pdo->prepare("UPDATE produit SET nom_Prod=? ,description_Prod=? ,Prix=? ,Stock=? ,Prod_img=? ,ID_Categ=?
                                                        WHERE ID_Prod=? ");
                                    $ri= $ri->execute([$nomprod,$des,$prix,$qte,"./photo/".$_FILES['img']['name'],$cat,$_GET['id']]);
                                    if($ri == False ){
                                        $_SESSION['echec'] = "Echeck la modification de Produit!";
                                    }
                                    else {
                                        $_SESSION['success'] = "Produit modifie avec succes !";
                                    }
                                    $openSection = "produits";
                                }catch(PDOException $e) {die ("erreur insertion prod : ".$e->getMessage());}
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
                            <?php 
                            if(isset($_GET['id'])){
                                include("../config/database.php");
                                try{
                                    $resid= $pdo->prepare("SELECT * FROM produit WHERE ID_Prod =? ");
                                    $resid->execute([$_GET['id']]);
                                    $tprod = $resid->fetch(PDO::FETCH_ASSOC);
            
                                }
                                catch(PDOException $e) {die("Erreur de selection prod:".$e->getMessage());}
                            }
                            ?>
                            <form method="POST" action="<?php $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
                                <div class="row g-4">
                                    <h5 class="form-section-title">Informations produit</h5>
                                    <div class="col-12">
                                        <label class="form-label">Images du produit</label>
                                         <?php if (isset($err['img'])) echo "<div class='text-danger small mt-1'>" . $err['img'] . "</div>" ?>
                                        <div class="upload-box text-center text-center p-4 border border-2 border-dashed rounded-3 position-relative ">
                                            <i class="bi bi-cloud-arrow-up display-4 text-secondary mb-2"></i>
                                            <h5 class="mt-2 fw-semibold text-dark">Glissez vos images ici</h5>
                                            <p class="text-muted small">ou cliquez pour sélectionner</p>
                                            <input type="hidden" name="lastimg" value="<?=$tprod['Prod_img']?>">
                                            <div class="image-preview-wrapper my-3">
                                                <img id="preview" src="<?=$tprod['Prod_img']?>" class="img-thumbnail rounded shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
                                            </div>
                                            <input type="file" name="img" id="imgInput" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" />
                                        </div>

                                        <script>
                                        document.getElementById('imgInput').addEventListener('change', function(){
                                            const file = this.files[0];
                                            if(file){
                                                document.getElementById('preview').src = URL.createObjectURL(file);
                                            }
                                        });
                                        </script>
                                    </div>

                                    <div class="col-md-6">
                                         <?php if (isset($err['nomprod'])) echo "<div class='text-danger small mt-1'>" . $err['nomprod'] . "</div>" ?>
                                        <label class="form-label">Nom du produit</label>
                                        <input class="form-control" name="nomprod" placeholder="Nom du produit" type="text" value = "<?= $tprod['nom_Prod'] ?>" />
                                    </div>

                                    <div class="col-md-6">
                                         <?php if (isset($err['cat'])) echo "<div class='text-danger small mt-1'>" . $err['cat'] . "</div>" ?>
                                        <label class="form-label">Catégorie</label>
                                        <select class="form-select" name="cat">
                                            <option disabled selected>Toutes catégories</option>
                                            <?php 
                                            include("../config/database.php"); 
                                            try{
                                                $recat = $pdo->query("SELECT ID_Categ, nom_Categ FROM categorie");
                                                $tab_cat = $recat->fetchAll(PDO::FETCH_NUM);
                                                foreach($tab_cat as $categ){
                                                    if($categ[0]==$tprod['ID_Categ']) $s="SELECTED"; else $s="";
                                                    echo "<option value='$categ[0]' $s>$categ[1]</option>";
                                                }
                                            }
                                            catch(PDOException $e){
                                                die("Erreur chargement categorie :".$e->getMessage());
                                            }

                                            ?>
                                        </select>
                                    </div>

                                    <h5 class="form-section-title">Stock &amp; Prix</h5>

                                     
                                    <div class="col-md-4">
                                        <?php if (isset($err['prix'])) echo "<div class='text-danger small mt-1'>" . $err['prix'] . "</div>" ?>
                                        <label class="form-label">Prix (MAD)</label>
                                        <input class="form-control" name="prix" placeholder="0.00" type="number" value = "<?= $tprod['Prix'] ?>" />
                                    </div>

                                    <div class="col-md-4">
                                        <?php if (isset($err['qte'])) echo "<div class='text-danger small mt-1'>" . $err['qte'] . "</div>" ?>
                                        <label class="form-label">Quantité stock</label>
                                        <input class="form-control" name="qte" placeholder="0" type="number" value = "<?= $tprod['Stock'] ?>"/>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">ID Produit</label>
                                        <input class="form-control" disabled="" type="text"value="#PRD-<?= $tprod['ID_Prod'] ?>"/>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">ID Boutique</label>
                                        <input class="form-control" disabled=""  type="text" name="idbou" value = "<?= $tprod['ID_boutique'] ?>"/>
                                    </div>

                                    <h5 class="form-section-title">Description</h5>
                                    <div class="col-12">
                                        <?php if (isset($err['des'])) echo "<div class='text-danger small mt-1'>" . $err['des'] . "</div>" ?>
                                        <label class="form-label">Description</label>
                                       <textarea class="form-control" name="des" placeholder="Description du produit..."><?= $tprod['description_Prod'] ?></textarea>
                                    </div>

                                    <h5 class="form-section-title">Publication</h5>

                                    <div class="col-md-6">
                                        <label class="form-label">Statut</label>
                                        <input class="form-control" disabled="" type="text" value = "<?= $tprod['statut'] ?>" />
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-3 mt-4">
                                            <button class="add-btn" type="submit" name="proEdit"><i class="bi bi-check-circle me-2"></i>Modifier produit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
