<div class="section d-none" id="produits">
    <?php
    if(isset($_SESSION['success'])){
        echo "
        <div class='alert alert-success alert-dismissible fade show' role='alert'>
            ".$_SESSION['success']."
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        unset($_SESSION['success']);
    }
    elseif(isset($_SESSION['echec'])){
        echo "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            ".$_SESSION['echec']."
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        unset($_SESSION['echec']);
    }
    $id_utili = $_SESSION['id_utili'];

    ?>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-box-seam"></i></div>
                <div class="content">
                    <p class="title fw-bold">Total Produits</p>
                    <h2 class="fw-bold">80</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="content">
                    <p class="title fw-bold">Stock faible</p>
                    <h2 class="fw-bold">30</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-x-circle"></i></div>
                <div class="content">
                    <p class="title fw-bold">Rupture</p>
                    <h2 class="fw-bold">15</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-grid"></i></div>
                <div class="content">
                    <p class="title fw-bold">Top Catégorie</p>
                    <h2 class="fw-bold">355</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="products-page">
        <div class="stock-header">
            <div>
                <h2>Mes Produits <span class="product-count">(24)</span></h2>
                <p class="subtitle">Gérez facilement vos produits et leur stock.</p>
            </div>
            <div class="header-actions">
                <button class="export-btn"><i class="bi bi-download me-2"></i>Exporter</button>
                <button class="add-btn" data-section="ajouter-produit"><i class="bi bi-plus-lg me-2"></i>Ajouter un produit</button>
            </div>
        </div>
        <div class="top-bar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input placeholder="Rechercher un produit..." type="text" class="search" />
            </div>
            <div class="filter-box">
                <i class="bi bi-funnel"></i>
                <select>
                    <option>Toutes catégories</option>
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
        </div>

        <?php
        try{
            $rqs = $pdo->prepare("SELECT p.ID_Prod,p.nom_Prod,p.Prix,p.Stock,p.Prod_img,c.nom_Categ
                                FROM produit p
                                JOIN categorie c 
                                    ON p.ID_Categ = c.ID_Categ
                                JOIN boutique b
                                    ON p.ID_boutique = b.ID_boutique
                                WHERE b.ID_utili = ?
                            ");
                            
            $rqs->execute([$id_utili]);                
            $tab_prod=  $rqs->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($tab_prod)){
            echo"<div class='table-responsive'>
                    <table class='product-table'>
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            foreach($tab_prod as $prod){
                echo"<tr>";
                echo"<td><div class='product-info'>
                    <img alt='".$prod['nom_Prod']."' src='".$prod['Prod_img']."' />
                    <div><h6>".$prod['nom_Prod']."</h6><small>#PRD".$prod['ID_Prod']."</small></div> </div></td>";
                echo"<td>".$prod['nom_Categ']."</td>";
                echo"<td>".$prod['Prix']." MAD</td>";
                
                $stock = $prod['Stock'];
                if($stock == 0){
                    echo "<td><span class='stock out'>Rupture</span></td>";
                }
                elseif($stock <= 15){
                    echo "<td><span class='stock low'>Stock faible ($stock)</span></td>";
                }
                else{
                    echo "<td><span class='stock ok'>$stock en stock</span></td>";
                }
                
                
                echo"<td><div class='action-buttons'>
                        <button class='icon-btn view-btn'
                            onclick=\"window.location='?id=".$prod['ID_Prod']."&section=voir-produit'\"
                            title='Voir détails'>
                            <i class='bi bi-eye'></i>
                        </button>

                        <button class='icon-btn edit-btn' 
                            data-section='modifier-produit'
                            title='Modifier'
                            onclick=\"window.location='?id=".$prod['ID_Prod']."&section=modifier-produit'\">
                            <i class='bi bi-pencil'></i>
                        </button>

                        <a href='#'
                           class='icon-btn remove-btn'
                           data-url='product-del.php?idp=".$prod['ID_Prod']."'
                           data-bs-toggle='modal'
                           data-bs-target='#deleteModal'
                           title='Supprimer'>
                           <i class='bi bi-trash'></i>
                        </a>
                    </div></td></tr>";          
            }
            echo"</tbody></table></div>";
            }else{
                echo"<div class='text-center p-4 text-muted'>Aucune produit trouvée.</div>";
            }
        }
        catch(PDOException $e){
            die("Erreur de selection prod :".$e->getMessage());
        }
        ?>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmation de suppression</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="bi bi-exclamation-triangle text-danger display-4 mb-3"></i>
                <p class="fs-5">Voulez-vous vraiment supprimer ce produit ?</p>
                <span class="text-muted small">Cette action est irréversible.</span>
            </div>
            <div class="modal-footer justify-content-center bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Annuler</button>
                <a href="#" id="deleteLink" class="btn btn-danger px-4">Supprimer</a>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault(); 
        let targetUrl = this.getAttribute('data-url');
        document.getElementById('deleteLink').href = targetUrl;
    });
});
</script>