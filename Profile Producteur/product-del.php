<?php 
session_start();

if(isset($_GET['idp'])){
    extract($_GET);
    include("../connexion.php");
    try{
    $reqd = $pdo->prepare("DELETE FROM produit WHERE ID_Prod = ? ");
    $r = $reqd->execute([$idp]);
    if($r == False ) {
        header("Location: producer-dashboard.php?section=produits&msgerr=Echec de suppression");
        exit;
    }else {
        header("Location: producer-dashboard.php?section=produits&msgs=Produit supprimé avec succes");
        exit;
    }}

    catch(PDOException $e) {die("erreur de suppression :".$e->getMessage());}
}



?>