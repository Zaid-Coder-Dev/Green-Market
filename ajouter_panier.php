<?php
session_start();
require_once 'connexion.php';
require_once 'functions.php';

require_login();

if ($_SESSION['role'] != 'client') {
    header('Location: Home Page/Home.php');
    exit();
}

$id_prod = 0;
if (isset($_POST['id_prod'])) {
    $id_prod = $_POST['id_prod'];
}

$quantite = 1;
if (isset($_POST['quantite'])) {
    $quantite = (int)$_POST['quantite'];
    if ($quantite < 1) {
        $quantite = 1;
    }
}
$retour = 'Home Page/Home.php';
if (isset($_POST['retour'])) {
    $retour = $_POST['retour'];
}

if ($id_prod == 0) {
    header('Location: ' . $retour);
    exit();
}

$id_utili = $_SESSION['id_utili'];

// CHERCHER LE PANIER DE L'UTILISATEUR
try {
    $req1 = $pdo->prepare("SELECT * FROM Panier WHERE ID_utili = ?");
    $req1->execute([$id_utili]);
    $panier = $req1->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// CREER LE PANIER S'IL N'EXISTE PAS
if (!$panier) {
    try {
        $req2 = $pdo->prepare("INSERT INTO Panier (ID_utili) VALUES (?)");
        $req2->execute([$id_utili]);
        $id_panier = $pdo->lastInsertId();
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
} else {
    $id_panier = $panier['ID_Panier'];
}

// VERIFIER SI LE PRODUIT EST DEJA DANS LE PANIER
try {
    $req3 = $pdo->prepare("SELECT * FROM Ligne_panier WHERE ID_Panier = ? AND ID_Prod = ?");
    $req3->execute([$id_panier, $id_prod]);
    $ligne = $req3->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

if ($ligne) {
    try {
        $req4 = $pdo->prepare("UPDATE Ligne_panier SET Quantite = Quantite + ? WHERE ID_Panier = ? AND ID_Prod = ?");
        $req4->execute([$quantite, $id_panier, $id_prod]);
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
} else {
    try {
        $req5 = $pdo->prepare("INSERT INTO Ligne_panier (ID_Panier, ID_Prod, Quantite) VALUES (?, ?, ?)");
        $req5->execute([$id_panier, $id_prod, $quantite]);
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}

header('Location: ' . $retour);
exit();
?>