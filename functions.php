<?php

function require_login() {
    if (!isset($_SESSION['id_utili'])) {
        header('Location: ../authentification/authentification.php');
        exit();
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] != $role) {
        header('Location: ../Home Page/Home.php');
        exit();
    }
}

function render_navbar($variant) {
    global $pdo;

    // Compte les articles dans le panier
    $nb_panier = 0;
    if (isset($_SESSION['id_utili']) && $_SESSION['role'] == 'client') {
        try {
            $req_pan = $pdo->prepare("SELECT SUM(lp.Quantite) as total FROM Panier p JOIN Ligne_panier lp ON lp.ID_Panier = p.ID_Panier WHERE p.ID_utili = ?");
            $req_pan->execute([$_SESSION['id_utili']]);
            $row_pan = $req_pan->fetch(PDO::FETCH_ASSOC);
            if ($row_pan['total']) {
                $nb_panier = (int)$row_pan['total'];
            }
        } catch (PDOException $e) {
            $nb_panier = 0;
        }
    }

    // Brand
    $brand_html = '';
    if ($variant == 'logo') {
        $brand_html = '<a class="navbar-brand d-flex align-items-center gap-2" href="../Home Page/Home.php">
            <img src="../uploads/logo_Qofa.png" alt="" class="navbar-logo">Green Market
        </a>';
    } else {
        $brand_html = '<a class="navbar-brand" href="../Home Page/Home.php">Green Market</a>';
    }

    // Classe navbar
    $nav_class = 'navbar navbar-expand-lg navbar-dark navbar-home';
    if ($variant == 'nologo') {
        $nav_class = 'navbar navbar-expand-lg fixed-top navbar-dark navbar-home';
    }

    // Icône panier
    $cart_html = '';
    if (isset($_SESSION['id_utili']) && $_SESSION['role'] == 'client') {
        $badge = '';
        if ($nb_panier > 0) {
            $badge = '<span class="cart-badge" id="cart-count">' . $nb_panier . '</span>';
        }
        $cart_html = '<a href="../Panier/Panier.php" class="nav-icon-circle">
            <i class="bi bi-cart3"></i>' . $badge . '
        </a>';
    }

    // Icône création de marché pour les producteurs
    $market_html = '';
    if (isset($_SESSION['id_utili']) && $_SESSION['role'] == 'producteur') {
        $market_html = '<a href="../Create Market/Create Market.php" class="nav-icon-circle" title="Créer un marché">
            <i class="bi bi-shop"></i>
        </a>';
    }

    // Icône profil
    $profile_html = '';
    if (isset($_SESSION['id_utili'])) {
        $profile_href = '../Profile-client/Profile-client.php';
        if ($_SESSION['role'] == 'producteur') {
            $profile_href = '../Profile%20Producteur/producer-dashboard.php';
        } else if ($_SESSION['role'] == 'admin') {
            $profile_href = '../Profile_Admin/Admin.php';
        }
        $profile_html = '<a href="' . $profile_href . '" class="nav-icon-circle">
            <i class="bi bi-person"></i>
        </a>';
    } else {
        $profile_html = '<a href="../authentification/authentification.php" class="nav-icon-circle">
            <i class="bi bi-person"></i>
        </a>';
    }

    // Master dot — visible seulement si panier non vide
    $master_dot = '';
    if ($nb_panier > 0) {
        $master_dot = '<span class="master-dot" id="masterDot"></span>';
    }

    echo '<nav class="' . $nav_class . '">
        <div class="container pt-2">
            ' . $brand_html . '
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto nav-links">
                    <li class="nav-item"><a class="nav-link" href="../Home Page/Home.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Cooperatives/Cooperatives.php">Coopératives</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Categories/Categories.php">Catégories</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Produits/Produits.php">Boutique</a></li>
                </ul>
                <div class="nav-utils">
                    <div class="nav-expand" id="navExpand">
                        ' . $cart_html . '
                        ' . $market_html . '
                        ' . $profile_html . '
                        <button type="button" class="lang-toggle" id="langToggle">FR</button>
                    </div>
                    <button type="button" class="nav-toggle-btn" id="navToggleBtn" aria-expanded="false" aria-label="Ouvrir le menu">
                        <i class="bi bi-three-dots-vertical"></i>
                        ' . $master_dot . '
                    </button>
                </div>
            </div>
        </div>
    </nav>';
}

function render_footer() {
    echo '<footer>
        <div class="footer-top pt-4">
            <div class="footer-stripe"></div>
            <div class="container">
                <div class="row g-4">
                    <div class="col-12 col-md-3">
                        <h5 class="text-white fw-bold mb-2" style="font-family:\'Playfair Display\',serif;">Green Market</h5>
                        <p class="footer-text">Votre marketplace de produits artisanaux marocains, directs des coopératives.</p>
                        <div class="footer-socials">
                            <a href="#" class="footer-social"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="footer-social"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="footer-social"><i class="bi bi-twitter-x"></i></a>
                            <a href="#" class="footer-social"><i class="bi bi-youtube"></i></a>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <h6 class="footer-title">Liens utiles</h6>
                        <a href="../Home Page/Home.php" class="footer-link">Accueil</a>
                        <a href="../Produits/Produits.php" class="footer-link">Boutique</a>
                        <a href="../Categories/Categories.php" class="footer-link">Catégories</a>
                        <a href="#contact" class="footer-link">Contact</a>
                    </div>
                    <div class="col-6 col-md-3">
                        <h6 class="footer-title">Catégories</h6>
                        <a href="#" class="footer-link">Produits Bio</a>
                        <a href="#" class="footer-link">Cosmétiques</a>
                        <a href="#" class="footer-link">Artisanat</a>
                        <a href="#" class="footer-link">Mode Traditionnelle</a>
                    </div>
                    <div class="col-12 col-md-3" id="contact">
                        <h6 class="footer-title">Contact</h6>
                        <div class="footer-contact-item"><i class="bi bi-envelope"></i><span>contact@greenmarket.ma</span></div>
                        <div class="footer-contact-item"><i class="bi bi-telephone"></i><span>+212 6 00 00 00 00</span></div>
                        <div class="footer-contact-item"><i class="bi bi-geo-alt"></i><span>Marrakech, Maroc</span></div>
                    </div>
                </div>
                <div class="footer-divider"></div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p class="footer-bottom-text">&copy; 2026 Green Market. Tous droits réservés.</p>
            </div>
        </div>
    </footer>';
}
?>