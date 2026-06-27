<?php
include("../config/database.php");
if (!isset($_SESSION['id_utili'])) {
    header("Location: auth.php");
    exit();
}
$idUser = $_SESSION['id_utili'];

$nomUser = $_SESSION['prenom'];

$reqBoutique = $pdo->prepare("SELECT ID_boutique FROM boutique WHERE ID_utili = ?");
$reqBoutique->execute([$idUser]);
$boutique = $reqBoutique->fetch(PDO::FETCH_ASSOC);

$idBoutique = $boutique['ID_boutique'] ;


$ca = 0;
$commandes = 0;
$produits = 0;
$note = 0;
$nbAvis = 0;
$stockFaible = 0;
$rupture = 0;
$avisSansRep = 0;
$topProduits = [];

if ($idBoutique > 0) {
    // Chiffre d'affaires total
    $req = $pdo->prepare("
        SELECT IFNULL(SUM(lc.Quantite * lc.Prix_Unitaire), 0)
        FROM ligne_commande lc
        JOIN produit p ON lc.ID_Prod = p.ID_Prod
        WHERE p.ID_boutique = ?
    ");
    $req->execute([$idBoutique]);
    $ca = $req->fetchColumn();

    // Nombre de commandes reçues 
    $req = $pdo->prepare("
        SELECT COUNT(DISTINCT lc.ID_Com)
        FROM ligne_commande lc
        JOIN produit p ON lc.ID_Prod = p.ID_Prod
        WHERE p.ID_boutique = ?
    ");
    $req->execute([$idBoutique]);
    $commandes = $req->fetchColumn();

    // Nombre total de produits actifs
    $req = $pdo->prepare("SELECT COUNT(*) FROM produit WHERE ID_boutique = ?");
    $req->execute([$idBoutique]);
    $produits = $req->fetchColumn();

    // Moyenne des avis et total des avis
    $req = $pdo->prepare("
        SELECT ROUND(AVG(a.note), 1) as moyenne, COUNT(a.ID_Avis) as total
        FROM avis a
        JOIN produit p ON a.ID_Prod = p.ID_Prod
        WHERE p.ID_boutique = ?
    ");
    $req->execute([$idBoutique]);
    $infosAvis = $req->fetch(PDO::FETCH_ASSOC);
    $note = $infosAvis['moyenne'] ?? 0;
    $nbAvis = $infosAvis['total'] ?? 0;

    // Produits avec stock faible (Plus grand que 0 et moins de 5)
    $req = $pdo->prepare("SELECT COUNT(*) FROM produit WHERE ID_boutique = ? AND Stock > 0 AND Stock < 5");
    $req->execute([$idBoutique]);
    $stockFaible = $req->fetchColumn();

    // Produits en rupture de stock (Stock = 0)
    $req = $pdo->prepare("SELECT COUNT(*) FROM produit WHERE ID_boutique = ? AND Stock = 0");
    $req->execute([$idBoutique]);
    $rupture = $req->fetchColumn();

    // Nombre d'avis laissés sans réponse
    $req = $pdo->prepare("
        SELECT COUNT(*)
        FROM avis a
        JOIN produit p ON a.ID_Prod = p.ID_Prod
        LEFT JOIN reponse r ON a.ID_Avis = r.ID_Avis
        WHERE p.ID_boutique = ? AND r.ID_Rep IS NULL
    ");
    $req->execute([$idBoutique]);
    $avisSansRep = $req->fetchColumn();

    // Les top 3 produits les plus vendus
    $req = $pdo->prepare("
        SELECT 
            p.ID_Prod,
            p.nom_Prod,
            p.Prod_img,
            SUM(lc.Quantite) as ventes,
            SUM(lc.Quantite * lc.Prix_Unitaire) as ca_produit
        FROM produit p
        JOIN ligne_commande lc ON p.ID_Prod = lc.ID_Prod
        WHERE p.ID_boutique = ?
        GROUP BY p.ID_Prod, p.nom_Prod, p.Prod_img
        ORDER BY ventes DESC
        LIMIT 3
    ");
    $req->execute([$idBoutique]);
    $topProduits = $req->fetchAll(PDO::FETCH_ASSOC);
}
?>



<div class="section" id="dashboard">
    <header class="header mb-4">
        <div>
            <h2 class="mb-1">Dashboard</h2>
            <p class="text-muted mb-0">Bonjour <?=$nomUser;?>, voici un aperçu de votre activité. 👋</p>
        </div>
    </header>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-bag-fill"></i></div>
                <div class="content">
                    <p class="title fw-bold">Chiffre d'affaires</p>
                    <h2 class="fw-bold"><?=$ca, 2, ',', ' ' ?> MAD</h2>
                    <p class="growth">Total des ventes</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-cart-fill"></i></div>
                <div class="content">
                    <p class="title fw-bold">Commandes Reçues</p>
                    <h2 class="fw-bold"><?= $commandes; ?></h2>
                    <p class="growth">Gérées</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-box-seam"></i></div>
                <div class="content">
                    <p class="title fw-bold">Produits</p>
                    <h2 class="fw-bold"><?= $produits; ?></h2>
                    <p class="growth">En ligne</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card-ca h-100">
                <div class="icon"><i class="bi bi-star-fill"></i></div>
                <div class="content">
                    <p class="title fw-bold">Satisfaction</p>
                    <h2 class="fw-bold"><?= $note; ?> / 5</h2>
                    <p class="growth">Basé sur <?= $nbAvis; ?> avis</p>
                </div>
            </div>
        </div>
    </div>

    <section class="charts mb-4">
        <div class="chart"><i class="bi bi-graph-up-arrow me-2"></i>Chiffre d'affaires mensuel (12 derniers mois)</div>
        <div class="quick-view">
            <h3 class="section-title">Aperçu rapide</h3>
            
            <div class="quick-item warning">
                <div class="icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <p>Stock faible</p>
                <span><?= $stockFaible; ?> produits</span>
            </div>
            
            <div class="quick-item danger">
                <div class="icon"><i class="bi bi-x-circle-fill"></i></div>
                <p>En rupture de stock</p>
                <span><?= $rupture; ?> produits</span>
            </div>
            
            <div class="quick-item info">
                <div class="icon"><i class="bi bi-star-fill"></i></div>
                <p>Avis non répondus</p>
                <span><?= $avisSansRep; ?> avis</span>
            </div>
        </div>
    </section>

    <div class="table-box">
        <div class="section-title">Produits les plus vendus</div>
        <div class="table-responsive">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Ventes</th>
                        <th>Chiffre d'affaires</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($topProduits) > 0): ?>
                        <?php foreach($topProduits as $p): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img src="<?= $p['Prod_img'] ?>" alt="<?= $p['nom_Prod']; ?>" />
                                        <div>
                                            <h6><?= $p['nom_Prod'] ?></h6>
                                            <small>#PRD-<?= $p['ID_Prod']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $p['ventes']; ?></td>
                                <td><?= $p['ca_produit'] ?> MAD</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Aucune vente enregistrée pour le moment.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>