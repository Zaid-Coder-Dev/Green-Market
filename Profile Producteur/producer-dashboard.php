<?php
session_start();
require_once('../connexion.php');
require_once('../functions.php');
require_role('producteur');

$openSection = '';
if (isset($_GET['section'])) {
    $openSection = $_GET['section'];
}

$req = $pdo->prepare("SELECT prenom FROM utilisateur WHERE id_utili = ?");
$req->execute([$_SESSION['id_utili']]);
$u = $req->fetch(PDO::FETCH_ASSOC);
$_SESSION['prenom'] = $u['prenom'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Green Market - Producteur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="producteur.css" rel="stylesheet" />
</head>
<body>
    <?php render_navbar('logo'); ?>

    <div class="container py-4">
        <div class="row g-4">
            <?php include('./producer/sidebar.php'); ?>
            <div class="col-12 col-md-9">
                <?php include('./producer/sections.php'); ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        var section = "<?php echo $openSection; ?>";
        if (section) { showSection(section); }
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Producteur.js"></script>
</body>
</html>