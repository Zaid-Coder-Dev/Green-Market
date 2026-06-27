                <!-- VOIR PRODUIT -->
                <?php 
                if(isset($_GET['id'])){
                    include("../config/database.php");
                    try{
                        $resid= $pdo->prepare("SELECT p.*, c.nom_Categ FROM produit p
                                                JOIN categorie c ON p.ID_Categ = c.ID_Categ
                                                WHERE p.ID_Prod = ? ");
                        $resid->execute([$_GET['id']]);
                        $tprod = $resid->fetch(PDO::FETCH_ASSOC);

                    }
                    catch(PDOException $e) {die("Erreur de selection prod:".$e->getMessage());}
                }
                ?>
                <div class="section d-none" id="voir-produit">
                    <div class="products-page">
                        <div class="stock-header">
                            <div>
                                <h2 class="mb-1">Détails du produit</h2>
                                <p class="subtitle mb-0">Consultez toutes les informations du produit.</p>
                            </div>
                            <button class="add-btn" data-section="produits" type="button">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </button>
                        </div>
                        <div class="content-card">
                            <div class="row g-4">
                                <div class="col-lg-5">
                                    <img alt="<?=$tprod['nom_Prod']?>" class="main-product-img" src="<?=$tprod['Prod_img']?>" />
                                </div>
                                <div class="col-lg-7">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h3 class="fw-bold mb-2"><?=$tprod['nom_Prod']?></h3>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <?php
                                                $stock = $tprod['Stock'];
                                                if($stock == 0){
                                                    echo "<span class='stock out'>Rupture</span>";
                                                }
                                                elseif($stock <= 15){
                                                    echo "<span class='stock low'>Stock faible ($stock)</span>";
                                                }
                                                else{
                                                    echo "<span class='stock ok'>$stock en stock</span>";
                                                }
                                                ?>
                                                <span class="product-category"><?=$tprod['nom_Categ']?></span>
                                            </div>
                                        </div>
                                        <span>#PRD-<?=$tprod['ID_Prod']?></span>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="view-box"><small>Catégorie</small>
                                                <h6><?=$tprod['nom_Categ']?></h6>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="view-box"><small>Prix</small>
                                                <h6><?=$tprod['Prix']?> MAD</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="view-box"><small>Quantité stock</small>
                                                <h6><?=$tprod['Stock']?> unités</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="view-box"><small>Date ajout</small>
                                                <h6><?=$tprod['date_ajout_Prod']?></h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="view-description mt-4">
                                        <h5 class="form-section-title">Description</h5>
                                        <p><?=$tprod['description_Prod']?></p>
                                    </div>
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-4">
                                            <div class="view-box text-center"><small>Ventes</small>
                                                <h5 class="fw-bold">320</h5>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="view-box text-center"><small>Avis</small>
                                                <h5 class="fw-bold">4.7 <i class="bi bi-star-fill text-warning"></i></h5>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="view-box text-center"><small>Commandes</small>
                                                <h5 class="fw-bold">120</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
