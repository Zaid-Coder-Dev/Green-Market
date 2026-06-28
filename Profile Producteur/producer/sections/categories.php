<div class="section d-none" id="categorie">
<?php
include(__DIR__ . '../../../../connexion.php');
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
            <button class="export-btn">
                <i class="bi bi-download me-2"></i>Exporter
            </button>
        </div>
    </div>


    <div class="top-bar">
            <div class="search-box">
            <i class="bi bi-search"></i>
            <input id="searchcate" placeholder="Rechercher une catégorie..." 
            type="text" class="search"/>
            </div>
    </div>



    <div class="table-responsive">
        <table class="product-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Nb Produits</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>


            <tbody>
                <?php foreach($categories as $cat){ ?>
                    <tr>
                        <td>#CAT-<?= $cat['ID_Categ'] ?></td>
                        <td><img src="<?php echo $cat['Categ_img'] ?> " alt="<?php echo $cat['nom_Categ']?>"></td>
                        <td><?= $cat['nom_Categ']?></td>
                        <td><?= $cat['nb_produits'] ?></td>
                        <td><?=$cat['description_Categ']?></td>
                        <td>
                            <button 
                                onclick="window.location='?idcat=<?= $cat['ID_Categ'] ?>&section=voir-categorie'"
                                class="btn-update">
                                <i class="bi bi-eye"></i>
                            </button>
                            
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("searchcate");
    const rows = document.querySelectorAll("#categorie tbody tr");

    function filterTable() {
        const searchValue = searchInput.value.toLowerCase();
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if(text.includes(searchValue)){
                row.style.display = "";
            }else{
                row.style.display = "none";
            }
        });
    }
    if(searchInput){
        searchInput.addEventListener("keyup", filterTable);
    }
});
</script>