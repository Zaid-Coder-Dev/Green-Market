<?php

$produits = [];

if (isset($_GET['idcat'])) {
    
    $id_cat = $_GET['idcat'];

    $req = $pdo->prepare("SELECT * FROM categorie WHERE ID_Categ = ?");
    $req->execute([$id_cat]);
    $cat = $req->fetch(PDO::FETCH_ASSOC);

    if ($cat) {

        $idProducer = $_SESSION['id_utili'];
        $reqProd = $pdo->prepare("
            SELECT p.*
            FROM produit p
            JOIN boutique b ON p.ID_boutique = b.ID_boutique
            WHERE p.ID_Categ = ?
            AND b.ID_utili = ?
            ORDER BY p.nom_Prod ASC
        ");
        $reqProd->execute([$id_cat, $idProducer]);
        $produits = $reqProd->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="section d-none" id="voir-categorie">
    <div class="container-fluid products-page py-3">
        <div class="mb-4">
            <a href="?section=categorie" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>

        <?php if ($cat): ?>
            <div class="card shadow-sm border-0 p-4 mb-4 bg-white" style="border-radius: 12px;">
                <div class="row align-items-center">
                    
                    <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
                        <img 
                            src="<?= '' . $cat['Categ_img'] ?>"
                            style="width:100px; height:100px; object-fit:cover; border-radius:12px; border: 1px solid #eee;"
                            alt="<?= $cat['nom_Categ'] ?>"
                        >
                    </div>

                    <div class="col-md-10">
                        <span class="text-muted text-uppercase small fw-bold">Espace Producteur — Catalogue</span>
                        <h3 class="mt-1 mb-2 text-dark fw-bold"><?= $cat['nom_Categ'] ?></h3>
                        <p class="text-secondary mb-0 small">
                            <?= !empty($cat['description_Categ']) ? $cat['description_Categ'] : '<em>Aucune description disponible.</em>' ?>
                        </p>
                    </div>

                </div>
            </div>

            
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-5 mb-3">
                <h4 class="m-0 fw-bold text-dark" style="font-size: 1.25rem;">Produits de cette catégorie</h4>
                
                <!-- FIXED: Removed form submit, just use input with onkeyup filter -->
                <div>
                    <input 
                        type="search" 
                        id="searchInput"
                        class="form-control"
                        placeholder="Rechercher..."
                        onkeyup="filterProducts()"
                    >
                </div>
            </div>

        
            <?php if (!empty($produits)): ?>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="product-table" id="productTable">
                        <thead style="position: sticky; top: 0; background: #f8f5f0; z-index: 10;">
                            <tr>
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Prix</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody id="productBody">
                            <?php foreach ($produits as $p): ?>
                                <tr class="product-row">
                                    <td>
                                        <img 
                                            src="<?= '' . $p['Prod_img'] ?>"
                                            width="50"
                                            height="50"
                                            style="object-fit: cover; border-radius: 6px;"
                                            alt="<?= $p['nom_Prod'] ?>"
                                        >
                                    </td>
                                    <td class="product-name"><?= $p['nom_Prod'] ?></td>
                                    <td class="product-desc"><?= $p['description_Prod'] ?></td>
                                    <td><?= $p['Prix'] ?> MAD</td>
                                    <td><?= $p['Stock'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-muted mt-2" id="resultCount">
                    <?= count($produits) ?> produit(s) trouvé(s)
                </div>
            <?php else: ?>
                <div class="alert alert-info border-0 shadow-sm text-center py-4" style="border-radius: 12px;">
                    Aucun produit dans cette catégorie.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                <div>Catégorie introuvable.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterProducts() {
    var input = document.getElementById('searchInput');
    var filter = input.value.toLowerCase();
    var rows = document.querySelectorAll('.product-row');
    var visibleCount = 0;
    
    rows.forEach(function(row) {
        var name = row.querySelector('.product-name').textContent.toLowerCase();
        var desc = row.querySelector('.product-desc').textContent.toLowerCase();
        
        if (name.includes(filter) || desc.includes(filter)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update result count
    document.getElementById('resultCount').textContent = visibleCount + ' produit(s) trouvé(s)';
}

// Optional: Add a clear button or escape key functionality
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Escape') {
        this.value = '';
        filterProducts();
        this.blur();
    }
});
</script>

<style>
/* Add some nice styles */
#searchInput {
    border-radius: 20px;
    padding: 8px 16px;
    border: 1px solid #ddd;
    min-width: 250px;
}

#searchInput:focus {
    outline: none;
    border-color: var(--gm-green, #2d4a2d);
    box-shadow: 0 0 0 3px rgba(45, 74, 45, 0.1);
}

.product-row {
    transition: background-color 0.2s ease;
}

.product-row:hover {
    background-color: #f8f5f0;
}

#resultCount {
    font-size: 0.9rem;
    opacity: 0.8;
}
</style>