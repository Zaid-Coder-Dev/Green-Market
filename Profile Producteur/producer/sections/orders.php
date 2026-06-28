<div class="section d-none" id="commandes">
    
    <?php
    if(isset($_SESSION['succ'])){
        echo "
        <div class='alert alert-success alert-dismissible fade show' role='alert'>
            ".$_SESSION['succ']."
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        unset($_SESSION['succ']);
    }
    elseif(isset($_SESSION['echou'])){
        echo "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            ".$_SESSION['echou']."
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
        unset($_SESSION['echou']);
    }
    ?>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-cart-check"></i></div>
                <div class="content">
                    <p class="title fw-bold">Total Commandes</p>
                    <h2 class="fw-bold">32</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="content">
                    <p class="title fw-bold">En attente</p>
                    <h2 class="fw-bold">18</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-truck"></i></div>
                <div class="content">
                    <p class="title fw-bold">Livrées</p>
                    <h2 class="fw-bold">25</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-x-octagon"></i></div>
                <div class="content">
                    <p class="title fw-bold">Annulées</p>
                    <h2 class="fw-bold">12</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="products-page">
        <div class="stock-header">
            <div>
                <h2>Commandes Reçues <span class="product-count">(32)</span></h2>
                <p class="subtitle">Suivez et gérez les commandes de vos clients.</p>
            </div>
            <div class="header-actions">
                <button class="export-btn"><i class="bi bi-download me-2"></i>Exporter</button>
            </div>
        </div>
        <div class="top-bar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input placeholder="Rechercher une commande..." type="text" class="search"/>
            </div>
            <div class="filter-box">
                <i class="bi bi-funnel"></i>
                <select>
                    <option>Toutes les commandes</option>
                    <option>Livrées</option>
                    <option>En attente</option>
                    <option>Annulées</option>
                </select>
            </div>
        </div>

        <?php
        try {
            include(__DIR__ . '../../../../connexion.php');
            $id_utili = $_SESSION['id_utili'];
            $reqBout = $pdo->prepare("SELECT ID_boutique 
                                    FROM boutique 
                                    WHERE ID_utili = ?
            ");
            $reqBout->execute([$id_utili]);
            $idbou = $reqBout->fetchColumn();
            
            $rqs = $pdo->prepare("SELECT DISTINCT
                                                            c.ID_Com,
                                                            c.date_com,
                                                            c.prix_total,
                                                            c.status_com,
                                                            u.nom,
                                                            u.prenom
                                                        FROM commande c
                                                        JOIN utilisateur u
                                                            ON c.ID_utili = u.ID_utili
                                                        JOIN ligne_commande lc
                                                            ON c.ID_Com = lc.ID_Com
                                                        JOIN produit p
                                                            ON lc.ID_Prod = p.ID_Prod
                                                        JOIN boutique b
                                                            ON p.ID_boutique = b.ID_boutique
                                                        WHERE b.ID_boutique = ?
                                                        ORDER BY c.date_com DESC

                                                                    ");

            $rqs->execute([$idbou]);            
            $tab_order = $rqs->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($tab_order)) {
                echo "<div class='table-responsive'>
                        <table class='product-table'>
                            <thead>
                                <tr>
                                    <th>ID / Client</th>
                                    <th>Date</th>
                                    <th>Prix Total</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>";
                            
                foreach ($tab_order as $order) {
                    echo "<tr>";
                    echo "<td>
                            <div class='product-info'>
                                <div>
                                    <h6>Commande # " . $order['ID_Com'] . "</h6>
                                    <small class='text-muted'>" . $order['nom'] . " " . $order['prenom'] . "</small>
                                </div>
                            </div>
                          </td>";
                    echo "<td>" . $order['date_com'] . "</td>";
                    echo "<td class='fw-bold '>" . $order['prix_total'] . " MAD</td>";
                    $status = $order['status_com'];
                    if ($status == "en attente") {
                        echo "<td><span class='stock low'>En attente</span></td>";
                    }
                    elseif ($status == "en cours") {
                        echo "<td><span class='stock enc'>En cours</span></td>";
                    }
                    elseif ($status == "livrée") {
                        echo "<td><span class='stock ok'>Livrée</span></td>";
                    }
                    else {
                        echo "<span class='stock out'>".$order['status_com']."</span>";
                    }


                    echo "<td>
                            <div class='action-buttons'>
                                <button class='icon-btn view-btn' 
                                    onclick=\"window.location='?id=" . $order['ID_Com'] . "&section=voir-commande'\" 
                                    title='Voir les produits'>
                                    <i class='bi bi-eye'></i>
                                </button>
                            </div>
                        </td>
                    </tr>";
                          
                }
                echo "</tbody></table></div>";
            } else {
                echo "<div class='text-center p-4 text-muted'>Aucune commande trouvée.</div>";
            }
        } 
        catch (PDOException $e) {
            die("Erreur de sélection des commandes : " . $e->getMessage());
        }
        ?>
        
        <div class="custom-pagination">
            <button class="pagination-arrow">«</button>
            <button class="pagination-number active">1</button>
            <button class="pagination-number">2</button>
            <button class="pagination-number">3</button>
            <button class="pagination-arrow">»</button>
        </div>
    </div>
</div>