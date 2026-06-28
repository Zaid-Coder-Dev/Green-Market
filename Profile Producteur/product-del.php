<?php
session_start();
require_once('../connexion.php');

if (isset($_GET['idp'])) {
    $idp = $_GET['idp'];
    try {
        $reqd = $pdo->prepare("DELETE FROM produit WHERE ID_Prod = ?");
        $r = $reqd->execute([$idp]);
        if ($r == false) {
            $_SESSION['echec'] = "Erreur : impossible de supprimer le produit.";
        } else {
            $_SESSION['success'] = "Produit supprimé avec succès !";
        }
    } catch (PDOException $e) {
        die("erreur de suppression : " . $e->getMessage());
    }
}
header("Location: producer-dashboard.php?section=produits");
exit;
?>