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

    <?php
    try {
        $id_utili = $_SESSION['id_utili'];
        
        // Get the producer's boutique ID
        $reqBout = $pdo->prepare("SELECT ID_boutique FROM boutique WHERE ID_utili = ?");
        $reqBout->execute([$id_utili]);
        $idbou = $reqBout->fetchColumn();
        
        // Get real statistics
        $statsQuery = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT c.ID_Com) as total_commandes,
                SUM(CASE WHEN c.status_com = 'en attente' THEN 1 ELSE 0 END) as en_attente,
                SUM(CASE WHEN c.status_com = 'livrée' THEN 1 ELSE 0 END) as livrees,
                SUM(CASE WHEN c.status_com = 'annulé' THEN 1 ELSE 0 END) as annulees
            FROM commande c
            JOIN ligne_commande lc ON c.ID_Com = lc.ID_Com
            JOIN produit p ON lc.ID_Prod = p.ID_Prod
            JOIN boutique b ON p.ID_boutique = b.ID_boutique
            WHERE b.ID_boutique = ?
        ");
        $statsQuery->execute([$idbou]);
        $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);
        
        $total_commandes = $stats['total_commandes'] ?? 0;
        $en_attente = $stats['en_attente'] ?? 0;
        $livrees = $stats['livrees'] ?? 0;
        $annulees = $stats['annulees'] ?? 0;
        
    } catch (PDOException $e) {
        die("Erreur de statistiques : " . $e->getMessage());
    }
    ?>

    <!-- REAL STATISTICS CARDS -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-cart-check"></i></div>
                <div class="content">
                    <p class="title fw-bold">Total Commandes</p>
                    <h2 class="fw-bold"><?= $total_commandes ?></h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="content">
                    <p class="title fw-bold">En attente</p>
                    <h2 class="fw-bold"><?= $en_attente ?></h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-truck"></i></div>
                <div class="content">
                    <p class="title fw-bold">Livrées</p>
                    <h2 class="fw-bold"><?= $livrees ?></h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-x-octagon"></i></div>
                <div class="content">
                    <p class="title fw-bold">Annulées</p>
                    <h2 class="fw-bold"><?= $annulees ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="products-page">
        <div class="stock-header">
            <div>
                <h2>Commandes Reçues <span class="product-count">(<?= $total_commandes ?>)</span></h2>
                <p class="subtitle">Suivez et gérez les commandes de vos clients.</p>
            </div>
            <div class="header-actions">
                <button class="export-btn" onclick="exportCommandes()"><i class="bi bi-download me-2"></i>Exporter</button>
            </div>
        </div>
        <div class="top-bar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input placeholder="Rechercher une commande..." type="text" class="search" id="searchCommande" onkeyup="filterCommandes()"/>
            </div>
            <div class="filter-box">
                <i class="bi bi-funnel"></i>
                <select id="filterStatus" onchange="filterCommandes()">
                    <option value="all">Toutes les commandes</option>
                    <option value="en attente">En attente</option>
                    <option value="en cours">En cours</option>
                    <option value="confirmée">Confirmée</option>
                    <option value="expédiée">Expédiée</option>
                    <option value="livrée">Livrées</option>
                    <option value="annulé">Annulées</option>
                </select>
            </div>
        </div>

        <?php
        try {
            // Get all orders with real data
            $rqs = $pdo->prepare("
                SELECT DISTINCT
                    c.ID_Com,
                    c.date_com,
                    c.prix_total,
                    c.status_com,
                    u.nom,
                    u.prenom,
                    u.email
                FROM commande c
                JOIN utilisateur u ON c.ID_utili = u.ID_utili
                JOIN ligne_commande lc ON c.ID_Com = lc.ID_Com
                JOIN produit p ON lc.ID_Prod = p.ID_Prod
                JOIN boutique b ON p.ID_boutique = b.ID_boutique
                WHERE b.ID_boutique = ?
                ORDER BY c.date_com DESC
            ");
            $rqs->execute([$idbou]);            
            $tab_order = $rqs->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($tab_order)) {
                echo "<div class='table-responsive' style='max-height: 500px; overflow-y: auto;'>";
                echo "<table class='product-table' id='commandeTable'>
                        <thead style='position: sticky; top: 0; background: #f8f5f0; z-index: 10;'>
                            <tr>
                                <th>ID / Client</th>
                                <th>Date</th>
                                <th>Prix Total</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id='commandeBody'>";
                
                // Show only first 10 initially
                $displayCount = 0;
                $totalOrders = count($tab_order);
                $showLimit = 10;
                
                foreach ($tab_order as $index => $order) {
                    $displayClass = ($index < $showLimit) ? '' : 'd-none extra-order';
                    $displayCount++;
                    
                    echo "<tr class='order-row " . $displayClass . "' data-status='" . strtolower($order['status_com']) . "' data-search='" . strtolower($order['nom'] . ' ' . $order['prenom'] . ' #' . $order['ID_Com']) . "'>";
                    echo "<td>
                            <div class='product-info'>
                                <div>
                                    <h6>Commande #" . $order['ID_Com'] . "</h6>
                                    <small class='text-muted'>" . $order['nom'] . " " . $order['prenom'] . "</small>
                                    <br><small class='text-muted' style='font-size: 0.7rem;'>" . $order['email'] . "</small>
                                </div>
                            </div>
                          </td>";
                    echo "<td>" . date('d/m/Y H:i', strtotime($order['date_com'])) . "</td>";
                    echo "<td class='fw-bold'>" . number_format($order['prix_total'], 2) . " MAD</td>";
                    
                    $status = strtolower($order['status_com']);
                    $statusDisplay = ucfirst($status);
                    $badgeClass = 'stock';
                    
                    if ($status == "en attente") {
                        $badgeClass = 'stock low';
                    } elseif ($status == "en cours" || $status == "confirmée" || $status == "expédiée") {
                        $badgeClass = 'stock enc';
                    } elseif ($status == "livrée") {
                        $badgeClass = 'stock ok';
                    } elseif ($status == "annulé") {
                        $badgeClass = 'stock out';
                    }
                    
                    echo "<td><span class='" . $badgeClass . "'>" . $statusDisplay . "</span></td>";
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
                
                echo "</tbody></table>";
                
                // Show "Show More" button if there are more than 10 orders
                if ($totalOrders > $showLimit) {
                    echo "<div class='text-center mt-3'>";
                    echo "<button class='btn btn-save text-white' id='showMoreBtn' onclick='showMoreOrders()'>";
                    echo "<i class='bi bi-chevron-down me-2'></i>Afficher plus (" . ($totalOrders - $showLimit) . " restant)";
                    echo "</button>";
                    echo "</div>";
                }
                
                echo "</div>";
            } else {
                echo "<div class='text-center p-4 text-muted'>Aucune commande trouvée.</div>";
            }
        } 
        catch (PDOException $e) {
            die("Erreur de sélection des commandes : " . $e->getMessage());
        }
        ?>
    </div>
</div>

<!-- JavaScript for filtering and show more -->
<script>
let showAll = false;

function showMoreOrders() {
    showAll = true;
    const extraOrders = document.querySelectorAll('.extra-order');
    extraOrders.forEach(row => {
        row.classList.remove('d-none');
    });
    document.getElementById('showMoreBtn').style.display = 'none';
}

function filterCommandes() {
    const searchTerm = document.getElementById('searchCommande').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
    const rows = document.querySelectorAll('.order-row');
    
    rows.forEach(row => {
        const searchData = row.getAttribute('data-search') || '';
        const rowStatus = row.getAttribute('data-status') || '';
        
        const matchesSearch = searchData.includes(searchTerm);
        const matchesStatus = statusFilter === 'all' || rowStatus === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Reset "show more" if filtering
    if (!showAll) {
        const extraOrders = document.querySelectorAll('.extra-order');
        extraOrders.forEach(row => {
            if (row.style.display !== 'none') {
                row.classList.add('d-none');
            }
        });
    }
}

function exportCommandes() {
    // Simple export function - you can enhance this
    const table = document.getElementById('commandeTable');
    if (!table) return;
    
    let csv = 'ID Commande,Client,Date,Prix Total,Statut\n';
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        if (row.style.display === 'none') return;
        const cells = row.querySelectorAll('td');
        if (cells.length >= 4) {
            const idClient = cells[0].textContent.trim().replace(/,/g, ';');
            const date = cells[1].textContent.trim();
            const prix = cells[2].textContent.trim();
            const statut = cells[3].textContent.trim();
            csv += `${idClient},${date},${prix},${statut}\n`;
        }
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'commandes_export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>

<style>
/* Make table scrollable and sticky header */
.products-page .table-responsive {
    max-height: 500px;
    overflow-y: auto;
    border-radius: 12px;
    border: 1px solid rgba(42, 36, 30, 0.08);
}

.products-page .table-responsive thead th {
    position: sticky;
    top: 0;
    background: #f8f5f0;
    z-index: 10;
    border-bottom: 2px solid rgba(42, 36, 30, 0.1);
}

/* Fix for the sticky header shadow */
.products-page .table-responsive thead th {
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Ensure the table scrolls nicely */
.products-page .table-responsive::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.products-page .table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.products-page .table-responsive::-webkit-scrollbar-thumb {
    background: var(--green);
    border-radius: 10px;
}

.products-page .table-responsive::-webkit-scrollbar-thumb:hover {
    background: #1a3a1a;
}
</style>