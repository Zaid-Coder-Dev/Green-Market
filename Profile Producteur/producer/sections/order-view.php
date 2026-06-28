<?php
include(__DIR__ . '../../../../connexion.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    extract($_POST);
    if(isset($updateStatu)){
        $nouvel_statut = trim($status_com);
        try {
            
            $rest = $pdo->prepare("SELECT status_com FROM commande WHERE ID_Com = ?");
            $rest->execute([$id_com]);
            $stat_data = $rest->fetch(PDO::FETCH_ASSOC);

            if ($stat_data) {
                $ancien_status = strtolower($stat_data['status_com']);
                $allow_update = false;

                if ($ancien_status == 'en attente' && $nouvel_statut == 'en cours') {
                    $allow_update = true;
                } elseif ($ancien_status == 'en cours' && $nouvel_statut == 'livrée') {
                    $allow_update = true;
                }

                if ($allow_update) {
                    $req = $pdo->prepare("UPDATE commande SET status_com = ? WHERE ID_Com = ?");
                    $req->execute([$nouvel_statut, $id_com]);
                    $_SESSION['succ'] = "Le statut a été mis à jour vers '" . ucfirst($nouvel_statut) . "'.";
                    $openSection="commandes";
                } else {
                    $_SESSION['echou'] = "Action non autorisée. Transition de '" . ucfirst($ancien_status) . "' vers '" . ucfirst($nouvel_statut) . "' impossible.";
                }
            }
        }catch(PDOException $e) {die("erreur de suppression :".$e->getMessage());}
        
        
    }
    
}
?>

<div class="section d-none" id="voir-commande">
    <?php
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id_com =$_GET['id'];

        try {
            $recom = $pdo->prepare("SELECT c.*, u.nom, u.prenom, u.email, u.ville AS ville_client 
                                    FROM commande c 
                                    JOIN utilisateur u ON c.ID_utili = u.ID_utili 
                                    WHERE c.ID_Com = ?");
            $recom->execute([$id_com]);
            $commande = $recom->fetch(PDO::FETCH_ASSOC);

            $reprod = $pdo->prepare("SELECT p.nom_Prod, p.Prod_img, lc.Quantite, lc.Prix_Unitaire 
                                     FROM ligne_commande lc 
                                     JOIN produit p ON lc.ID_Prod = p.ID_Prod 
                                     WHERE lc.ID_Com = ?");
            $reprod->execute([$id_com]);
            $produits = $reprod->fetchAll(PDO::FETCH_ASSOC);

            if ($commande) {
                extract($commande,EXTR_SKIP);
                $status = strtolower($commande['status_com']);
                ?>
                <div class="products-page">
                    <div class="stock-header">
                        <div>
                            <h2>Détails de la Commande</h2>
                            <p class="subtitle">Gestion et suivi de la commande #CMD-<?php echo $id_com; ?></p>
                        </div>
                        <div class="header-actions">
                            <button class="export-btn" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimer
                            </button>
                            <button class="add-btn" data-section="commandes" type="button">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </button>
                        </div>
                    </div>

                    <div class="view-description mb-4">
                        <div class="row align-items-center">

                            <div class="col-md-7">
                                <h4 class="fw-bold mb-3" style="color: #2d4a2d; font-family: 'Playfair Display', serif;">
                                    Commande #CMD-<?php echo $id_com; ?>
                                </h4>
                                <div class="row g-3 text-muted" style="font-size: 14px;">
                                    <div class="col-sm-6">
                                        <i class="bi bi-person-fill me-1"></i> 
                                        <strong>Client :</strong> <?php echo $commande['nom'] . ' ' . $commande['prenom']; ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <i class="bi bi-calendar-event-fill me-1"></i> 
                                        <strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($commande['date_com'])); ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <i class="bi bi-cash-stack me-1"></i> 
                                        <strong>Total :</strong> <span class="fw-bold text-success"><?php echo $commande['prix_total'] ; ?> MAD</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <i class="bi bi-info-circle-fill me-1"></i> 
                                        <strong>Statut Actuel :</strong> 
                                        <?php 
                                        if ($status == 'en attente') echo "<span class='stock low'>En attente</span>";
                                        elseif ($status == 'en cours') echo "<span class='stock enc'>En cours</span>";
                                        elseif ($status == 'livrée' || $status == 'confirmée') echo "<span class='stock ok'>Livrée</span>";
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-5 mt-3 mt-md-0">
                                <?php if ($status == 'livrée'): ?>
                                    <div class="p-3 text-center text-muted" style="background: #e8f5e9; border-radius: 14px; border: 1px solid #a5d6a7; font-size: 14px;">
                                        <i class="bi bi-lock-fill text-success me-1"></i> Cette commande est clôturée (Livrée).
                                    </div>
                                <?php else: ?>
                                    <form method="POST" action="" class="p-3 style-form" style="background: rgba(255,255,255,0.5); border-radius: 14px; border: 1px solid #ead4b6;">
                                        <input type="hidden" name="id_com" value="<?php echo $id_com; ?>">
                                        <label class="form-label mb-2" style="font-size: 13px;"><i class="bi bi-pencil-square"></i> Passer à l'étape suivante</label>
                                        <div class="d-flex gap-2">
                                            <select name="status_com" class="form-select flex-grow-1" style="height: 44px; padding: 0 10px; font-size: 14px;">
                                                <?php if ($status == 'en attente'): ?>
                                                    <option value="en attente" selected disabled>En attente (Actuel)</option>
                                                    <option value="en cours">En cours</option>
                                                <?php elseif ($status == 'en cours'): ?>
                                                    <option value="en cours" selected disabled>En cours (Actuel)</option>
                                                    <option value="livrée">Livrée</option>
                                                <?php endif; ?>
                                            </select>
                                            <button type="submit" name="updateStatu" class="add-btn" style="height: 44px; padding: 0 15px; font-size: 13px; font-weight: 600;">
                                                Valider
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="order-timeline mt-4 pt-3 border-top d-flex gap-2" style="flex-direction: row; justify-content: space-between; flex-wrap: wrap;">
                            <div class="timeline-item p-2 flex-grow-1 text-center <?php echo ($status == 'en attente') ? 'active-timeline' : 'done'; ?>">
                                <i class="bi bi-clock-history me-1"></i> En attente
                            </div>
                            <div class="timeline-item p-2 flex-grow-1 text-center <?php echo ($status == 'en cours') ? 'active-timeline' : (($status == 'livrée') ? 'done' : ''); ?>">
                                <i class="bi bi-truck me-1"></i> En cours
                            </div>
                            <div class="timeline-item p-2 flex-grow-1 text-center <?php echo ($status == 'livrée') ? 'active-timeline' : ''; ?>">
                                <i class="bi bi-check-circle-fill me-1"></i> Livrée
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="content-card">
                                <h5 class="form-section-title mb-4" style="color: #2d4a2d; font-family: 'Playfair Display', serif;"><i class="bi bi-box-seam me-2"></i>Produits commandés</h5>
                                
                                <div class="row g-3">
                                    <?php foreach ($produits as $p): ?>
                                        <?php 
                                        extract($p, EXTR_SKIP); 
                                        $prod_total = $Prix_Unitaire * $Quantite; 
                                        ?>
                                        <div class="col-12">
                                            <div class="view-box d-flex align-items-center gap-3 flex-wrap flex-sm-nowrap">
                                                <div class="product-info" style="gap: 0;">
                                                    <img src="<?php echo $Prod_img; ?>" alt="<?php echo $nom_Prod; ?>">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="fw-bold mb-1" style="color: #2d4a2d; margin:0; font-size: 15px;"><?php echo $nom_Prod; ?></h6>
                                                    <div class="text-muted mt-1 small d-flex gap-3 flex-wrap">
                                                        <span>Prix: <strong><?php echo $Prix_Unitaire; ?> MAD</strong></span>
                                                        <span>Quantité: <strong><?php echo $Quantite; ?></strong></span>
                                                    </div>
                                                </div>
                                                <div class="text-sm-end w-100 w-sm-auto pt-2 pt-sm-0">
                                                    <small class="text-muted d-block small text-uppercase" style="font-size: 10px;">Sous-total</small>
                                                    <span class="fw-bold text-success" style="font-size: 15px;"><?php echo $prod_total; ?> MAD</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="text-end mt-4 pt-3 border-top">
                                    <p class="text-muted mb-1 small">Montant Total à Payer</p>
                                    <h3 class="fw-bold" style="color: #2d4a2d; font-family: 'Playfair Display', serif; font-size: 28px;"><?php echo $commande['prix_total']; ?> MAD</h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 d-flex flex-column gap-4">
                            <div class="view-box" style="background: #fff;">
                                <h6 class="form-section-title mb-3" style="font-size: 16px; font-family: 'Playfair Display', serif;"><i class="bi bi-person-circle me-2" style="color: #2d4a2d;"></i>Client</h6>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar" style="width: 42px; height: 42px; font-size: 1rem;">
                                        <?php echo strtoupper(substr($nom, 0, 1)); ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h6 class="mb-0 fw-bold" style="color: #2d4a2d; font-size: 15px;"><?php echo $nom . ' ' . $prenom ; ?></h6>
                                        <small class="text-muted text-break" style="font-size: 12px;"><?php echo $email; ?></small>
                                    </div>
                                </div>
                                <div class="small text-muted pt-2 mt-3 border-top" style="font-size: 13px;">
                                    <i class="bi bi-building me-1"></i> Ville d'origine : <strong class="text-dark"><?php echo $ville_client; ?></strong>
                                </div>
                            </div>

                            <div class="view-box" style="background: #fff;">
                                <h6 class="form-section-title mb-3" style="font-size: 16px; font-family: 'Playfair Display', serif;"><i class="bi bi-geo-alt-fill me-2" style="color: #e07b39;"></i>Livraison</h6>
                                <div class="delivery-grid" style="margin: 0; grid-template-columns: 1fr;">
                                    <div class="delivery-card">
                                        <div class="delivery-icon"><i class="bi bi-house-door"></i></div>
                                        <div class="delivery-info">
                                            <small>Adresse de livraison</small>
                                            <h6><?php echo $adresse_livraison; ?></h6>
                                        </div>
                                    </div>
                                    <div class="delivery-card">
                                        <div class="delivery-icon"><i class="bi bi-building"></i></div>
                                        <div class="delivery-info">
                                            <small>Ville & Code Postal</small>
                                            <h6><?php echo $ville_livraison . ' - ' . $code_postal_livraison; ?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            } else {
                echo "<div class='alert alert-danger m-3'>Commande introuvable.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger m-3'>Erreur : " . $e->getMessage() . "</div>";
        }
    }
    ?>
</div>