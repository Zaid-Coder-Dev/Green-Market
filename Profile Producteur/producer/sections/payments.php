<?php
$idProducer = $_SESSION['id_utili'];
//confirmer
if(isset($_GET['confirm'])){
    try{
        $req = $pdo->prepare("UPDATE commande
                              SET status_com = 'livrée'
                              WHERE ID_Com = ?
                            ");
        $req->execute([$_GET['confirm']]);
        $_SESSION['conP_success'] = "Le paiement de la commande #CMD".$_GET['confirm']." a été confirmé avec succès.";
        $openSection = 'paimnt';
    }catch(Exception $e){ die("Erreur : " . $e->getMessage());}
}

//payment historique
$totalPaiement = 0;
$nbPaye = 0;
$nbAttente = 0;
$nbRembourse = 0; 
$all_pay = [];

try {
    $repay = $pdo->prepare("SELECT 
                                pay.ID_Pay,
                                pay.mode_pay,
                                pay.date_pay,
                                c.ID_Com,
                                c.status_com,
                                u.nom AS client_nom,
                                u.prenom AS client_prenom,
                                u.ID_utili AS client_id,
                                SUM(lc.Quantite * lc.Prix_Unitaire) AS montant_vendeur
                            FROM paiement pay
                            JOIN commande c ON pay.ID_Com = c.ID_Com
                            JOIN ligne_commande lc ON c.ID_Com = lc.ID_Com
                            JOIN produit p ON lc.ID_Prod = p.ID_Prod
                            JOIN boutique b ON p.ID_boutique = b.ID_boutique
                            JOIN utilisateur u ON c.ID_utili = u.ID_utili
                            WHERE b.ID_utili = ?
                            GROUP BY pay.ID_Pay, pay.mode_pay, pay.date_pay, c.ID_Com, c.status_com, u.nom, u.prenom, u.ID_utili
                            ORDER BY pay.date_pay DESC");

    $repay->execute([$idProducer]);
    $all_pay = $repay->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all_pay as $pay) {
        $totalPaiement += $pay['montant_vendeur'];
        if ($pay['status_com'] == "livrée") {
            $nbPaye++;
        } elseif ($pay['status_com'] == "en attente" || $pay['status_com'] == "en cours") {
            $nbAttente++;
        } else {
            $nbRembourse++;
        }
    }
    $nbTotalTransactions = count($all_pay);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<div class="section d-none" id="paimnt">
    <?php if(isset($_SESSION['conP_success'])): ?>
    <div class='alert alert-success alert-dismissible fade show' role='alert'>
            <?php echo $_SESSION['conP_success'] ?>
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>
    <?php unset($_SESSION['conP_success']) ?>
    <?php endif ?>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-cash-stack"></i></div>
                <div class="content">
                    <p class="title fw-bold">Total Revenus</p>
                    <h2 class="fw-bold"><?= $totalPaiement?> MAD</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-check-circle"></i></div>
                <div class="content">
                    <p class="title fw-bold">Transactions Réussies</p>
                    <h2 class="fw-bold"><?= $nbPaye ?> <span style="font-size: 0.8rem; font-weight: normal;" class="text-muted">/ <?= $nbTotalTransactions ?></span></h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="content">
                    <p class="title fw-bold">En attente</p>
                    <h2 class="fw-bold"><?= $nbAttente ?><span style="font-size: 0.8rem; font-weight: normal;" class="text-muted">/ <?= $nbTotalTransactions ?></span></h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-arrow-counterclockwise"></i></div>
                <div class="content">
                    <p class="title fw-bold">Remboursements</p>
                    <h2 class="fw-bold"><?= $nbRembourse ?><span style="font-size: 0.8rem; font-weight: normal;" class="text-muted">/ <?= $nbTotalTransactions ?></span></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="products-page">
        <div class="stock-header">
            <div>
                <h2>Paiements</h2>
                <p class="subtitle">Visualisez vos revenus et paiements reçus.</p>
            </div>
            <div class="header-actions">
                <button class="export-btn"><i class="bi bi-download me-2"></i>Exporter</button>
            </div>
        </div>
        
        <div class="top-bar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input id="searchPay" placeholder="Rechercher par client, commande..." type="text" class="search" />
            </div>
            <div class="filter-box">
                <i class="bi bi-funnel"></i>
                <select id="filterStatus">
                    <option value="">Tous statuts</option>
                    <option value="payé">Payé</option>
                    <option value="en attente">En attente</option>
                    <option value="échoué">Échoué</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>ID Paiement</th>
                        <th>ID Commande</th>
                        <th>       Client       </th>
                        <th>Montant (Votre Part)</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_pay)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-credit-card-2-back d-block mb-2" style="font-size: 2rem; color: var(--gm-muted);"></i>
                                Aucun paiement reçu pour le moment.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($all_pay as $pay): ?>
                            <tr class="payment-row" data-status="<?php 
                                            if($pay['status_com'] == 'livrée') echo 'payé';
                                            elseif($pay['status_com'] == 'en attente' || $pay['status_com'] == 'en cours') echo 'en attente';
                                            else echo 'échoué';
                                            ?>">
                                <td>#PAY<?= $pay['ID_Pay'] ?></td>
                                <td>#CMD<?= $pay['ID_Com'] ?></td>
                                <td>
                                    <h6 class="mb-0"><?= $pay['client_prenom'] . ' ' . $pay['client_nom'] ?></h6>
                                    <small>#C-<?= $pay['client_id'] ?></small>
                                </td>
                                <td class="fw-bold text-success"><?= $pay['montant_vendeur'] ?> MAD</td>
                                <td><?= $pay['mode_pay'] ?></td>
                                <td>
                                    <?php if ($pay['status_com'] == "livrée"): ?>
                                        <span class="stock ok">Payé</span>
                                    <?php elseif ($pay['status_com'] == "en attente" || $pay['status_com'] == "en cours"): ?>
                                        <span class="stock low">En attente</span>
                                    <?php else: ?>
                                        <span class="stock out" style="color: #d17946; background: #fdf2eb;">Échoué</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($pay['date_pay'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($pay['status_com'] == "livrée"): ?>
                                            <a href="facture.php?id=<?= $pay['ID_Com'] ?>" 
                                            target="_blank"
                                            class="icon-btn edit-btn">
                                            <i class="bi bi-download"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="icon-btn edit-btn"
                                                    onclick="window.location='?confirm=<?= $pay['ID_Com'] ?>&section=paimnt'">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>
document.addEventListener("DOMContentLoaded", function () {
    
    const searchInput = document.getElementById("searchPay");
    const filterSelect = document.getElementById("filterStatus");
    const rows = document.querySelectorAll(".payment-row");

    function filterTable() {
        const searchValue = searchInput ? searchInput.value.toLowerCase() : "";
        const filterValue = filterSelect ? filterSelect.value : "";
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const status = row.getAttribute("data-status");
            if (text.includes(searchValue) && (filterValue === "" || status === filterValue)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
    if (searchInput) searchInput.addEventListener("keyup", filterTable);
    if (filterSelect) filterSelect.addEventListener("change", filterTable);
;
});
</script>