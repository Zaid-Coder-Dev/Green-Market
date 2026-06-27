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
                            src="<?=$cat['Categ_img']?>"
                            style="width:100px; height:100px; object-fit:cover; border-radius:12px; border: 1px solid #eee;"
                            alt="<?= $cat['nom_Categ']?>"
                        >
                    </div>

                    <div class="col-md-10">
                        <span class="text-muted text-uppercase small fw-bold">Espace Producteur — Catalogue</span>
                        <h3 class="mt-1 mb-2 text-dark fw-bold"><?= $cat['nom_Categ']?></h3>
                        <p class="text-secondary mb-0 small">
                            <?= !empty($cat['description_Categ']) ?$cat['description_Categ'] : '<em>Aucune description disponible.</em>' ?>
                        </p>
                    </div>

                </div>
            </div>

            
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-5 mb-3">
                <h4 class="m-0 fw-bold text-dark" style="font-size: 1.25rem;">Produits de cette catégorie</h4>
                
               
                <form method="GET" id="searchForm">

                    <input type="hidden" name="section" value="voir-categorie">
                    <input type="hidden" name="idcat" value="<?= $id_cat ?>">

                    <input 
                        type="search" 
                        id="searchInput"
                        name="search"
                        class="form-control"
                        placeholder="Rechercher..."
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                    >

                </form>
            </div>

        
            <?php if (!empty($produits)): ?>
                <div class="list-group shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <?php foreach ($produits as $p): ?>
                        <div class="list-group-item list-group-item-action py-3 px-4 border-0 border-bottom">
                            <div class="d-flex align-items-center justify-content-between">
                                
                                <div class="d-flex align-items-center">
                                    <img 
                                        src="<?=$p['Prod_img']?>
                                        width="65"
                                        height="65"
                                        class="rounded me-3 border"
                                        style="object-fit: cover;"
                                    >
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark"><?= $p['nom_Prod']?></h6>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <span class="text-success fw-bold fs-5"><?=$p['Prix']?> <span style="font-size: 0.8rem;">DH</span></span>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
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
document.getElementById("searchInput").addEventListener("keyup", function(){

    document.getElementById("searchForm").submit();

});
</script>