<?php
$idProducteur = $_SESSION['id_utili'];
if($_SERVER['REQUEST_METHOD']=="POST"){
    extract($_POST);
    if(isset($envoyer_reponse)){

        if (!empty($reponse) && !empty($id_avis)) {
            try{
                // FIXED: Check if THIS producer already has a response for this avis
                $check = $pdo->prepare("SELECT ID_Rep FROM reponse WHERE ID_Avis = ? AND ID_utili = ?");
                $check->execute([$id_avis, $idProducteur]);
                $tab=$check->fetch(PDO::FETCH_ASSOC);
                if ($tab) {
                    // Update producer's own response
                    $req = $pdo->prepare("UPDATE reponse SET message = ? WHERE ID_Avis = ? AND ID_utili = ?");
                    $req->execute([$reponse, $id_avis, $idProducteur]);
                }else{
                    // Check if admin already responded (to prevent overwriting)
                    $adminCheck = $pdo->prepare("SELECT ID_Rep FROM reponse WHERE ID_Avis = ? AND ID_utili IN (SELECT id_utili FROM utilisateur WHERE role = 'admin')");
                    $adminCheck->execute([$id_avis]);
                    if($adminCheck->fetch()){
                        // Admin already responded, maybe show a message or allow producer to add their own
                        // For now, we'll let the producer add their own response too
                    }
                    // Add producer's response
                    $req = $pdo->prepare("INSERT INTO reponse (message, ID_utili, ID_Avis) VALUES (?, ?, ?)");
                    $req->execute([$reponse, $idProducteur, $id_avis]);
                }
                $openSection = "avis";

            }catch (PDOException $e) {
                echo "Erreur lors de l'enregistrement : " . addslashes($e->getMessage()) . "');</script>";
            }
        }
    }
}

//info tb
$les_avis = [];
try {
    $reAvis = $pdo->prepare("SELECT 
            a.ID_Avis, a.note, a.commentaire, a.date_avis,
            p.nom_Prod, p.Prod_img, p.ID_Prod,
            u.nom AS client_nom, u.prenom AS client_prenom,
            r.message AS reponse_producteur,
            r.ID_Rep AS reponse_id,
            r.ID_utili AS reponse_auteur_id,
            u2.nom AS auteur_nom,
            u2.prenom AS auteur_prenom,
            u2.role AS auteur_role
        FROM avis a
        JOIN produit p ON a.ID_Prod = p.ID_Prod
        JOIN boutique b ON p.ID_boutique = b.ID_boutique
        JOIN utilisateur u ON a.ID_utili = u.ID_utili
        LEFT JOIN reponse r ON a.ID_Avis = r.ID_Avis
        LEFT JOIN utilisateur u2 ON r.ID_utili = u2.id_utili
        WHERE b.ID_utili = ?  
        ORDER BY a.date_avis DESC
    ");
    $reAvis->execute([$idProducteur]);
    $les_avis = $reAvis->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des avis : " . $e->getMessage();  
}

?>

<div class="section d-none" id="avis">
    <div class="products-page">
        <div class="stock-header">
            <div>
                <h2>Avis Clients</h2>
                <p class="subtitle">Consultez et gérez les avis laissés par vos clients.</p>
            </div>
            <div class="header-actions">
                <button class="export-btn"><i class="bi bi-download me-2"></i>Exporter</button>
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
                    <option>Tous les produits</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Client</th>
                        <th>Note</th>
                        <th>Commentaire / Réponse</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($les_avis)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Aucun avis trouvé pour vos produits.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($les_avis as $av): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img alt="<?= $av['nom_Prod']?>" src="<?= $av['Prod_img']?>" />
                                        <div>
                                            <h6><?= $av['nom_Prod'] ?></h6>
                                            <small>#PRD-<?= $av['ID_Prod'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $av['client_prenom'] . ' ' . $av['client_nom'] ?></td>
                                <td>
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $av['note']) {
                                            echo '<i class="bi bi-star-fill text-warning me-1"></i>';
                                        } else {
                                            echo '<i class="bi bi-star text-warning me-1"></i>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="text-muted mb-1"><?= $av['commentaire']?></div>
                                    
                                    <?php 
                                    // FIXED: Only show producer's own responses, not admin responses
                                    if (!empty($av['reponse_producteur']) && $av['reponse_auteur_id'] == $idProducteur): 
                                    ?>
                                        <div class="p-2 rounded mt-2" style="background-color: #f4eee2; border-left: 3px solid var(--gm-green); font-size: 0.9rem;">
                                            <strong style="color: var(--gm-green);"><i class="bi bi-reply-fill me-1"></i>Votre réponse :</strong> 
                                            <span class="text-dark"><?= $av['reponse_producteur'] ?></span>
                                            <small class="d-block text-muted" style="font-size: 0.7rem;">Réponse envoyée par vous</small>
                                        </div>
                                    <?php elseif (!empty($av['reponse_producteur']) && $av['reponse_auteur_id'] != $idProducteur): ?>
                                        <div class="p-2 rounded mt-2" style="background-color: #e8f0fe; border-left: 3px solid #1a73e8; font-size: 0.9rem;">
                                            <strong style="color: #1a73e8;"><i class="bi bi-shield-check me-1"></i>Réponse admin :</strong> 
                                            <span class="text-dark"><?= $av['reponse_producteur'] ?></span>
                                            <small class="d-block text-muted" style="font-size: 0.7rem;">Réponse de l'administrateur</small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($av['date_avis'])) ?></td>
                                <td>
                                    <?php 
                                    // FIXED: Only show action button if no admin response exists OR if producer already responded
                                    $hasProducerResponse = (!empty($av['reponse_producteur']) && $av['reponse_auteur_id'] == $idProducteur);
                                    $hasAdminResponse = (!empty($av['reponse_producteur']) && $av['reponse_auteur_id'] != $idProducteur);
                                    
                                    if (!$hasAdminResponse || $hasProducerResponse):
                                    ?>
                                        <button class="btn-update btn-repondre-modal" 
                                                data-id="<?= $av['ID_Avis'] ?>" 
                                                data-produit="<?= $av['nom_Prod'] ?>"
                                                data-actuel="<?= $hasProducerResponse ? $av['reponse_producteur'] : '' ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#reponseModal">
                                            <?= $hasProducerResponse ? '<i class="bi bi-pencil-square me-1"></i>Modifier' : '<i class="bi bi-reply me-1"></i>Répondre' ?>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.8rem;">
                                            <i class="bi bi-check-circle text-success me-1"></i>Traité par admin
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="custom-pagination">
            <button class="pagination-arrow">&laquo;</button>
            <button class="pagination-number active">1</button>
            <button class="pagination-arrow">&raquo;</button>
        </div>
    </div>
</div>

<!-- FIXED MODAL -->
<div class="modal fade" id="reponseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content reponse-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-chat-left-text-fill me-2"></i>
                    Répondre à l'avis
                </h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <p class="mb-3">
                        Produit :
                        <strong id="modal-product-name"></strong>
                    </p>
                    <input type="hidden"
                           name="id_avis"
                           id="modal-id-avis">
                    <div class="mb-3">
                        <label class="form-label">
                            Votre message :
                        </label>
                        <textarea
                            class="form-control"
                            name="reponse"
                            id="modal-reponse-text"
                            rows="4"
                            placeholder="Écrivez votre réponse ici..."
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit"
                            name="envoyer_reponse"
                            class="btn btn-send">
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // FIXED: Properly handle the modal population
    $('.btn-repondre-modal').on('click', function() {
        var idAvis = $(this).data('id');
        var produit = $(this).data('produit');
        var actuel = $(this).data('actuel') || '';
        
        $('#modal-id-avis').val(idAvis);
        $('#modal-product-name').text(produit);
        $('#modal-reponse-text').val(actuel);
        
        // Update button text
        if (actuel.trim() !== '') {
            $('button[name="envoyer_reponse"]').html('<i class="bi bi-pencil-square me-1"></i>Modifier');
        } else {
            $('button[name="envoyer_reponse"]').html('<i class="bi bi-send me-1"></i>Envoyer');
        }
    });
    
    // Reset modal when closed
    $('#reponseModal').on('hidden.bs.modal', function() {
        $('#modal-reponse-text').val('');
    });
});
</script>