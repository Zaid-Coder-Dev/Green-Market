                <!-- AJOUTER PRODUIT -->
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
                    if(isset($proAdd)){
                            
                        $err= [];
                        #verification des donnees 
                        if(!isset($nomprod) || empty($nomprod)) $err['nomprod']='Veuillez saisir nom de produit';
                        if(!isset($prix) || empty($prix )) $err['prix']='Veuillez saisir prix de produit';
                        elseif($prix<=0 ) $err['prix'] = "le prix ne doit pas etre negatif ou null" ;  
                        if(!isset($cat) || empty($cat )) $err['cat']='veuillez choisir une catgorie ';   
                        if(!isset($qte) || empty($qte )) $err['qte']='Veuillez entrer la quantite';
                        elseif($qte<0 ) $err['qte'] = "la quantite ne doit pas etre negatif" ;
                        if(!isset($des) || empty($des)) $err['des']='Veuillez saisir description'; 
                        
                        #Verification img
                        $exts=['image/png','image/jpg','image/jpeg','image/gif','image/tiff','image/jfif'];
                        if($_FILES['img']['error']!=0) $err['img']="Erreur lors du téléchargement de l'image";
                        elseif(!in_array($_FILES['img']['type'],$exts))$err['img']="Veuillez choisir une image au format GIF, TIFF, PNG, JPG ou JPEG.";
                        elseif($_FILES['img']['size']>40*1024*1024) $err['img']="La taille de l'image ne doit pas dépasser 40 Mo.";

                        if(!empty($err)){
                            $openSection = "ajouter-produit";
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
                                    $ri = $pdo->prepare("INSERT INTO produit(nom_Prod,description_Prod,Prix,Stock,Prod_img,ID_boutique,ID_Categ)
                                                            VALUES (?, ?, ?, ?, ?, ?, ?)");
                                    $ri= $ri->execute([$nomprod,$des,$prix,$qte,"./photo/".$_FILES['img']['name'],$idbou,$cat]);
                                        if($ri == False ){
                                            $_SESSION['echec'] = "Erreur : impossible d'ajouter le produit.";
                                        }
                                        else {
                                            $_SESSION['success'] = "Produit inseré avec succes !";
                                        }
                                        $openSection = "produits";
                                        
                                }catch(PDOException $e) {die ("erreur insertion produit : ".$e->getMessage());}
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
                            <form method="POST" action="<?php $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
                                <div class="row g-4">
                                    <h5 class="form-section-title">Informations produit</h5>
                                    <div class="col-12">

                                        <label class="form-label">Images du produit</label>
                                         <?php if (isset($err['img'])) echo "<div class='text-danger small mt-1'>" . $err['img'] . "</div>" ?>
                                        <div class="upload-box text-center">
                                            <i class="bi bi-cloud-arrow-up"></i>
                                            <h5 class="mt-3">Glissez vos images ici</h5>
                                            <p>ou cliquez pour sélectionner</p>
                                            <input  multiple="" type="file" name="img" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                         <?php if (isset($err['nomprod'])) echo "<div class='text-danger small mt-1'>" . $err['nomprod'] . "</div>" ?>
                                        <label class="form-label">Nom du produit</label>
                                        <input class="form-control" name="nomprod" placeholder="Nom du produit" type="text" />
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
                                                    echo "<option value='$categ[0]'>$categ[1]</option>";
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
                                        <input class="form-control" name="prix" placeholder="0.00" type="number" />
                                    </div>

                                    <div class="col-md-4">
                                        <?php if (isset($err['qte'])) echo "<div class='text-danger small mt-1'>" . $err['qte'] . "</div>" ?>
                                        <label class="form-label">Quantité stock</label>
                                        <input class="form-control" name="qte" placeholder="0" type="number" />
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">ID Produit</label>
                                        <?php
                                        try {
                                            $stmt = $pdo->query("SELECT AUTO_INCREMENT 
                                                                FROM information_schema.TABLES
                                                                WHERE TABLE_SCHEMA = DATABASE()
                                                                AND TABLE_NAME='produit'");

                                            $nextId = $stmt->fetchColumn();
                                            $nextId+=1;
                                        } catch(PDOException $e){
                                            $nextId = 1;
                                        }
                                        ?>
                                        <input class="form-control" disabled="" type="text" value="#PRD-<?= $nextId ?>" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">ID Boutique</label>
                                        <input class="form-control" disabled="" type="text" value="<?= $idbou ?>" />
                                        
                                    </div>

                                    <h5 class="form-section-title">Description</h5>
                                    <div class="col-12">
                                        <?php if (isset($err['des'])) echo "<div class='text-danger small mt-1'>" . $err['des'] . "</div>" ?>
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="des" placeholder="Description du produit..."></textarea>
                                    </div>

                                    <h5 class="form-section-title">Publication</h5>

                                    <div class="col-md-6">
                                        <label class="form-label">Statut</label>
                                        <input class="form-control" disabled="" type="text" value="En attente" />
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-3 mt-4">
                                            <button class="add-btn" type="submit" name="proAdd"><i class="bi bi-check-circle me-2"></i>Ajouter produit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>