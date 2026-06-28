<?php
    session_start();

    include_once('../connexion.php');
    include_once('../functions.php');
    require_role('producteur');

    $openSection = isset($_GET['section']) ? $_GET['section'] : '';
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
    document.addEventListener("DOMContentLoaded", function(){
        let section = "<?php echo htmlspecialchars($openSection); ?>";
        if(section){ showSection(section); }
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Producteur.js"></script>
</body>
</html>
