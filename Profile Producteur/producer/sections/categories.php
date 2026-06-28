<div class="section d-none" id="categorie">
<?php

try {
    $req = $pdo->query("SELECT 
            c.ID_Categ,
            c.nom_Categ,
            c.description_Categ,
            c.Categ_img,
            COUNT(p.ID_Prod) AS nb_produits
        FROM categorie c
        LEFT JOIN produit p 
        ON c.ID_Categ = p.ID_Categ
        GROUP BY c.ID_Categ
        ORDER BY c.ID_Categ DESC
    ");
    $categories = $req->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e){die("Erreur : ".$e->getMessage());}
?>


<div class="products-page">

    <div class="stock-header">
        <div>
            <h2>Catégories</h2>
            <p class="subtitle">Gérez facilement les catégories de vos produits.</p>
        </div>
        <div class="header-actions">
            <button class="export-btn" onclick="exportCategories()">
                <i class="bi bi-download me-2"></i>Exporter
            </button>
        </div>
    </div>


    <div class="top-bar">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input id="searchcate" placeholder="Rechercher une catégorie..." 
            type="text" class="search" onkeyup="filterCategories()"/>
        </div>
    </div>



    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="product-table">
            <thead style="position: sticky; top: 0; background: #f8f5f0; z-index: 10;">
                <tr>
                    <th>ID</th>
                    <th style="width: 80px;">Image</th>
                    <th>Nom</th>
                    <th>Nb Produits</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>


            <tbody id="categoryBody">
                <?php foreach($categories as $cat){ 
                    // Fix image path
                    $imgPath = !empty($cat['Categ_img']) ?  $cat['Categ_img'] : 'assets/default-category.jpg';
                ?>
                    <tr class="category-row" data-search="<?= strtolower($cat['nom_Categ'] . ' ' . $cat['description_Categ']) ?>">
                        <td>#CAT-<?= $cat['ID_Categ'] ?></td>
                        <td>
                            <img 
                                src="<?= $imgPath ?>" 
                                alt="<?= htmlspecialchars($cat['nom_Categ']) ?>"
                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;"
                                
                            >
                        </td>
                        <td><strong><?= htmlspecialchars($cat['nom_Categ']) ?></strong></td>
                        <td>
                            <span class="badge" style="background: var(--gm-green, #2d4a2d); color: white; padding: 4px 12px; border-radius: 20px;">
                                <?= $cat['nb_produits'] ?>
                            </span>
                        </td>
                        <td style="max-width: 300px;">
                            <?= !empty($cat['description_Categ']) ? htmlspecialchars(substr($cat['description_Categ'], 0, 60)) . (strlen($cat['description_Categ']) > 60 ? '...' : '') : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td>
                            <button 
                                onclick="window.location='?idcat=<?= $cat['ID_Categ'] ?>&section=voir-categorie'"
                                class="btn-update"
                                title="Voir les produits de cette catégorie">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    <?php if (!empty($categories)): ?>
    <div class="text-muted mt-2" id="categoryCount">
        <?= count($categories) ?> catégorie(s) trouvée(s)
    </div>
    <?php endif; ?>
</div>

<script>
function filterCategories() {
    const searchInput = document.getElementById("searchcate");
    const rows = document.querySelectorAll(".category-row");
    const searchValue = searchInput.value.toLowerCase();
    let visibleCount = 0;
    
    rows.forEach(row => {
        const searchData = row.getAttribute('data-search') || '';
        const text = row.innerText.toLowerCase();
        
        if (text.includes(searchValue) || searchData.includes(searchValue)) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = "none";
        }
    });
    
    // Update count
    const countEl = document.getElementById('categoryCount');
    if (countEl) {
        countEl.textContent = visibleCount + ' catégorie(s) trouvée(s)';
    }
}

function exportCategories() {
    const rows = document.querySelectorAll('.category-row');
    let csv = 'ID,Nom,Description,Nb Produits\n';
    
    rows.forEach(row => {
        if (row.style.display === 'none') return;
        const cells = row.querySelectorAll('td');
        if (cells.length >= 5) {
            const id = cells[0].textContent.trim();
            const nom = cells[2].textContent.trim().replace(/,/g, ';');
            const desc = cells[4].textContent.trim().replace(/,/g, ';');
            const nb = cells[3].textContent.trim();
            csv += `${id},${nom},${desc},${nb}\n`;
        }
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'categories_export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Escape key to clear search
document.getElementById("searchcate")?.addEventListener('keyup', function(e) {
    if (e.key === 'Escape') {
        this.value = '';
        filterCategories();
        this.blur();
    }
});
</script>

<style>
/* Make images consistent size */
#categorie .product-table td:first-child {
    font-weight: 500;
    color: var(--gm-dark, #333);
}

#categorie .product-table img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #eee;
    transition: transform 0.2s ease;
}

#categorie .product-table img:hover {
    transform: scale(1.5);
    z-index: 999;
    position: relative;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

#categorie .product-table td {
    vertical-align: middle;
}

#categorie .table-responsive {
    border-radius: 12px;
    border: 1px solid rgba(42, 36, 30, 0.08);
}

#categorie .table-responsive::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

#categorie .table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#categorie .table-responsive::-webkit-scrollbar-thumb {
    background: var(--gm-green, #2d4a2d);
    border-radius: 10px;
}

#categorie .table-responsive::-webkit-scrollbar-thumb:hover {
    background: #1a3a1a;
}

#categoryCount {
    font-size: 0.9rem;
    opacity: 0.8;
}

.badge {
    font-weight: 400;
}

.btn-update {
    background: var(--gm-green, #2d4a2d);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.btn-update:hover {
    background: #1a3a1a;
    transform: scale(1.05);
}

.btn-update i {
    font-size: 1rem;
}
</style>