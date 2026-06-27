<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=greenmarket', 'root', '');
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>