            <div class="col-12 col-md-3">
                <div class="profile-sidebar p-3">
                    <!-- Cooperative Logo -->
                    <div class="text-center my-2">
                        <img alt="Logo Coopérative" class="rounded-3" src="../assets/images/placeholder-logo.png" width="100" />
                        <p class="small text-muted mt-1 mb-0">Coopérative Amal</p>
                    </div>
                    <hr class="my-2" />
                    <!-- Avatar + Info -->
                    <div class="d-flex align-items-center gap-3 p-2">
                        <div class="avatar"></div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= $_SESSION['prenom'] ?></h6>
                            <small class="text-muted"><?= $_SESSION['mail'] ?></small>
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
