<?php
$idProducer = $_SESSION['id_utili'];

// Confirmer paiement
if(isset($_GET['confirm'])){
    try{
        $req = $pdo->prepare("UPDATE commande SET status_com = 'livrée' WHERE ID_Com = ?");
        $req->execute([$_GET['confirm']]);
        $_SESSION['conP_success'] = "Le paiement de la commande #CMD".$_GET['confirm']." a été confirmé avec succès.";
        $openSection = 'paimnt';
    }catch(Exception $e){ 
        $_SESSION['conP_error'] = "Erreur : " . $e->getMessage();
    }
}

// Payment historique
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

    <?php if(isset($_SESSION['conP_error'])): ?>
    <div class='alert alert-danger alert-dismissible fade show' role='alert'>
        <?php echo $_SESSION['conP_error'] ?>
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>
    <?php unset($_SESSION['conP_error']) ?>
    <?php endif ?>

    <!-- Real data from database -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-cash-stack"></i></div>
                <div class="content">
                    <p class="title fw-bold">Total Revenus</p>
                    <h2 class="fw-bold"><?= number_format($totalPaiement, 2) ?> MAD</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-check-circle"></i></div>
                <div class="content">
                    <p class="title fw-bold">Transactions Réussies</p>
                    <h2 class="fw-bold"><?= $nbPaye ?></h2>
                    <small class="text-muted">sur <?= $nbTotalTransactions ?> total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="content">
                    <p class="title fw-bold">En attente</p>
                    <h2 class="fw-bold"><?= $nbAttente ?></h2>
                    <small class="text-muted">sur <?= $nbTotalTransactions ?> total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-arrow-counterclockwise"></i></div>
                <div class="content">
                    <p class="title fw-bold">Remboursements</p>
                    <h2 class="fw-bold"><?= $nbRembourse ?></h2>
                    <small class="text-muted">sur <?= $nbTotalTransactions ?> total</small>
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
                <button class="export-btn" onclick="exportPayments()"><i class="bi bi-download me-2"></i>Exporter</button>
            </div>
        </div>
        
        <div class="top-bar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input id="searchPay" placeholder="Rechercher par client, commande..." type="text" class="search" onkeyup="filterPayments()" />
            </div>
            <div class="filter-box">
                <i class="bi bi-funnel"></i>
                <select id="filterStatus" onchange="filterPayments()">
                    <option value="">Tous statuts</option>
                    <option value="payé">Payé</option>
                    <option value="en attente">En attente</option>
                    <option value="échoué">Échoué</option>
                </select>
            </div>
        </div>

        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="product-table">
                <thead style="position: sticky; top: 0; background: #f8f5f0; z-index: 10;">
                    <tr>
                        <th>ID Paiement</th>
                        <th>ID Commande</th>
                        <th>Client</th>
                        <th>Montant (Votre Part)</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentBody">
                    <?php if (empty($all_pay)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-credit-card-2-back d-block mb-2" style="font-size: 2rem; color: var(--gm-muted);"></i>
                                Aucun paiement reçu pour le moment.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($all_pay as $pay): 
                            $statusClass = '';
                            $statusLabel = '';
                            $dataStatus = '';
                            
                            if($pay['status_com'] == 'livrée') {
                                $statusClass = 'stock ok';
                                $statusLabel = 'Payé';
                                $dataStatus = 'payé';
                            } elseif($pay['status_com'] == 'en attente' || $pay['status_com'] == 'en cours') {
                                $statusClass = 'stock low';
                                $statusLabel = 'En attente';
                                $dataStatus = 'en attente';
                            } else {
                                $statusClass = 'stock out';
                                $statusLabel = 'Échoué';
                                $dataStatus = 'échoué';
                            }
                        ?>
                            <tr class="payment-row" data-status="<?= $dataStatus ?>" data-search="<?= strtolower($pay['client_prenom'] . ' ' . $pay['client_nom'] . ' #CMD' . $pay['ID_Com']) ?>">
                                <td>#PAY<?= $pay['ID_Pay'] ?></td>
                                <td>#CMD<?= $pay['ID_Com'] ?></td>
                                <td>
                                    <h6 class="mb-0"><?= $pay['client_prenom'] . ' ' . $pay['client_nom'] ?></h6>
                                    <small class="text-muted">#C-<?= $pay['client_id'] ?></small>
                                </td>
                                <td class="fw-bold text-success"><?= number_format($pay['montant_vendeur'], 2) ?> MAD</td>
                                <td><?= ucfirst($pay['mode_pay']) ?></td>
                                <td><span class="<?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                <td><?= date('d/m/Y', strtotime($pay['date_pay'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($pay['status_com'] == "livrée"): ?>
                                            <a href="facture.php?id=<?= $pay['ID_Com'] ?>" 
                                               target="_blank"
                                               class="icon-btn edit-btn"
                                               title="Télécharger la facture">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="icon-btn edit-btn"
                                                    onclick="confirmPayment(<?= $pay['ID_Com'] ?>)"
                                                    title="Confirmer le paiement">
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
        
        <?php if (!empty($all_pay)): ?>
        <div class="text-muted mt-2" id="paymentCount">
            <?= count($all_pay) ?> paiement(s) trouvé(s)
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterPayments() {
    const searchInput = document.getElementById('searchPay');
    const filterSelect = document.getElementById('filterStatus');
    const rows = document.querySelectorAll('.payment-row');
    const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
    const filterValue = filterSelect ? filterSelect.value : '';
    let visibleCount = 0;
    
    rows.forEach(row => {
        const searchData = row.getAttribute('data-search') || '';
        const status = row.getAttribute('data-status') || '';
        
        const matchesSearch = searchData.includes(searchValue);
        const matchesFilter = filterValue === '' || status === filterValue;
        
        if (matchesSearch && matchesFilter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update count
    const countEl = document.getElementById('paymentCount');
    if (countEl) {
        countEl.textContent = visibleCount + ' paiement(s) trouvé(s)';
    }
}

function confirmPayment(comId) {
    if (confirm('Confirmer le paiement de la commande #CMD' + comId + ' ?')) {
        window.location = '?confirm=' + comId + '&section=paimnt';
    }
}

function exportPayments() {
    const table = document.querySelector('.product-table');
    if (!table) return;
    
    let csv = 'ID Paiement,ID Commande,Client,Montant,Méthode,Statut,Date\n';
    const rows = document.querySelectorAll('.payment-row');
    rows.forEach(row => {
        if (row.style.display === 'none') return;
        const cells = row.querySelectorAll('td');
        if (cells.length >= 7) {
            const idPay = cells[0].textContent.trim();
            const idCom = cells[1].textContent.trim();
            const client = cells[2].textContent.trim().replace(/,/g, ';');
            const montant = cells[3].textContent.trim();
            const methode = cells[4].textContent.trim();
            const statut = cells[5].textContent.trim();
            const date = cells[6].textContent.trim();
            csv += `${idPay},${idCom},${client},${montant},${methode},${statut},${date}\n`;
        }
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'paiements_export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Handle Enter key for search
document.getElementById('searchPay')?.addEventListener('keyup', function(e) {
    if (e.key === 'Escape') {
        this.value = '';
        filterPayments();
        this.blur();
    }
});
</script>

<style>
/* Custom styles using your palette */
#searchPay {
    border-radius: 20px;
    padding: 8px 16px;
    border: 1px solid #ddd;
}

#searchPay:focus {
    outline: none;
    border-color: var(--gm-green, #2d4a2d);
    box-shadow: 0 0 0 3px rgba(45, 74, 45, 0.1);
}

.payment-row {
    transition: background-color 0.2s ease;
}

.payment-row:hover {
    background-color: #f8f5f0;
}

#paymentCount {
    font-size: 0.9rem;
    opacity: 0.8;
}
</style>