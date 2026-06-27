<?php 
session_start();

if(isset($_GET['idp'])){
    extract($_GET);
    include("../config/database.php");
    try{
    $reqd = $pdo->prepare("DELETE FROM produit WHERE ID_Prod = ? ");
    $r = $reqd->execute([$idp]);
    if($r == False ) {
        $_SESSION['echec'] = "Erreur : impossible d'ajouter le produit.";
        header("Location: producer-dashboard.php?section=produits");
        exit;
    }else {
        $_SESSION['success'] = "Produit supprimé avec succès !";
        var_dump($_SESSION);
        header("Location: producer-dashboard.php?section=produits");
        exit;
    }}

    catch(PDOException $e) {die("erreur de suppression :".$e->getMessage());}
}



?>