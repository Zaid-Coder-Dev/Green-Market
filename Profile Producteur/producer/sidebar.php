<div class="col-12 col-md-3">
    <div class="profile-sidebar p-3">
        <?php
        // Fetch boutique info including logo
        $id_utili = $_SESSION['id_utili'] ?? 0;
        $logo_path = '../'; // Default fallback
        $cooperative_name = 'Coopérative Amal';
        
        if ($id_utili > 0) {
            try {
                $stmt = $pdo->prepare("
                    SELECT nom_boutique, logo 
                    FROM boutique 
                    WHERE ID_utili = ?
                ");
                $stmt->execute([$id_utili]);
                $boutique = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($boutique) {
                    if (!empty($boutique['logo'])) {
                        $logo_path = $boutique['logo'];
                    }
                    if (!empty($boutique['nom_boutique'])) {
                        $cooperative_name = $boutique['nom_boutique'];
                    }
                }
            } catch (PDOException $e) {
                // Silent fail - use defaults
            }
        }
        
        // Get user initial for avatar
        $user_initial = strtoupper(substr($_SESSION['prenom'] ?? 'U', 0, 1));
        ?>
        
        <!-- Cooperative Logo -->
        <div class="text-center my-2">
            <img 
                alt="Logo Coopérative" 
                class="rounded-3" 
                src="<?= htmlspecialchars($logo_path) ?>" 
                width="100" 
                onerror="this.src='/xampp/htdocs/GitHub/Green-Market/uploads/boutiques_images/logo_default.png'"
            />
            <p class="small text-muted mt-1 mb-0"><?= htmlspecialchars($cooperative_name) ?></p>
        </div>
        <hr class="my-2" />
        
        <!-- Avatar + Info -->
        <div class="d-flex align-items-center gap-3 p-2">
            <div class="avatar"><?= $user_initial ?></div>
            <div>
                <h6 class="fw-bold mb-0"><?= $_SESSION['prenom'] ?></h6>
                <small class="text-muted"><?= $_SESSION['email'] ?></small>
            </div>
        </div>
        <hr />
        
        <!-- Navigation -->
        <div class="d-flex flex-column gap-1 sidebar-menu">
            <a class="sidebar-link active" data-section="dashboard" href="#">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a class="sidebar-link" data-section="produits" href="#">
                <i class="bi bi-boxes me-2"></i>Mes Produits
            </a>
            <a class="sidebar-link" data-section="commandes" href="#">
                <i class="bi bi-cart3 me-2"></i>Commandes
            </a>
            <a class="sidebar-link" data-section="paimnt" href="#">
                <i class="bi bi-credit-card me-2"></i>Paiements
            </a>
            <a class="sidebar-link" data-section="avis" href="#">
                <i class="bi bi-star me-2"></i>Avis Clients
            </a>
            <a class="sidebar-link" data-section="categorie" href="#">
                <i class="bi bi-grid me-2"></i>Catégories
            </a>
            <a class="sidebar-link" data-section="profil" href="#">
                <i class="bi bi-person me-2"></i>Mon Profil
            </a>
        </div>
        <hr />
        <a class="sidebar-link text-danger" href="../../deconnexion.php">
            <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
        </a>
    </div>
</div>