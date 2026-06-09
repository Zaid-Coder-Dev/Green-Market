<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=GreenMarket', 'root', '');
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>