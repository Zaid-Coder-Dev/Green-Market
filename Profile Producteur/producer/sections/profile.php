<?php

$id = $_SESSION['id_utili'];

try {
    $reu = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utili = ?");
    $reu->execute([$id]);
    $user = $reu->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Utilisateur introuvable.");
    }
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$success_profil = '';
$success_mdp = '';
$err = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    extract($_POST);
    $err = [];

    // MODIFIER LE PROFIL
    if (isset($save_profil)) {

        if (!isset($nom) || empty($nom)) {
            $err['nom'] = "Veuillez saisir votre nom";
        } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $nom)) {
            $err['nom'] = "Veuillez saisir un nom valide";
        }

        if (!isset($prenom) || empty($prenom)) {
            $err['prenom'] = "Veuillez saisir votre prénom";
        } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $prenom)) {
            $err['prenom'] = "Veuillez saisir un prénom valide";
        }

        // Email validation
        if (!isset($email) || empty($email)) {
            $err['email'] = "Veuillez saisir votre email";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err['email'] = "Veuillez saisir une adresse email valide";
        }

        if (empty($err)) {
            try {
                $repro = $pdo->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, rue = ?, ville = ? WHERE id_utili = ?");
                $repro->execute([$nom, $prenom, $email, $rue, $ville, $id]);

                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;

                $success_profil = "Profil mis à jour avec succès.";
                $openSection = "profil";

                $user['nom'] = $nom;
                $user['prenom'] = $prenom;
                $user['email'] = $email;
                $user['rue'] = $rue;
                $user['ville'] = $ville;

            } catch (PDOException $e) {
                die("Erreur lors de la mise à jour : " . $e->getMessage());
            }
        }
    }

    // MODIFIER MDP
    if (isset($save_mdp)) {
        
        if (!isset($ancien_mdp) || empty($ancien_mdp)) {
            $err['ancien_mdp'] = "Veuillez saisir votre ancien mot de passe";
        }

        if (!isset($nv_mdp) || empty($nv_mdp)) {
            $err['nv_mdp'] = "Veuillez saisir le nouveau mot de passe";
        } elseif (strlen($nv_mdp) < 8) {
            $err['nv_mdp'] = "Le mot de passe doit contenir au moins 8 caractères";
        }

        if (!isset($mdpc) || empty($mdpc)) {
            $err['mdpc'] = "Veuillez confirmer le mot de passe";
        } elseif ($nv_mdp !== $mdpc) {
            $err['mdpc'] = "Les mots de passe ne correspondent pas";
        }

        if (empty($err)) {
            if (!password_verify($ancien_mdp, $user['mot_de_passe'])) {
                $err['ancien_mdp'] = "L'ancien mot de passe est incorrect";
            } else {
                try {
                    $new_hash = password_hash($nv_mdp, PASSWORD_ARGON2I);
                    
                    $req = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utili = ?");
                    $req->execute([$new_hash, $id]);

                    $success_mdp = "Mot de passe modifié avec succès.";
                    $openSection = "profil";
                    
                    $user['mot_de_passe'] = $new_hash;
                } catch (PDOException $e) {
                    die("Erreur lors de la modification : " . $e->getMessage());
                }
            }
        }
    }
}

// List of Moroccan cities
$moroccan_cities = [
    'Agadir', 'Al Hoceïma', 'Asilah', 'Azrou', 'Béni Mellal',
    'Berkane', 'Berrechid', 'Casablanca', 'Chefchaouen', 'Dakhla',
    'El Jadida', 'Errachidia', 'Essaouira', 'Fès', 'Fnideq',
    'Guelmim', 'Ifrane', 'Kénitra', 'Khémisset', 'Khénifra',
    'Khouribga', 'Laâyoune', 'Larache', 'Marrakech', 'Meknès',
    'Mohammedia', 'Nador', 'Ouarzazate', 'Oujda', 'Rabat',
    'Safi', 'Salé', 'Settat', 'Sidi Ifni', 'Sidi Kacem',
    'Tanger', 'Tan-Tan', 'Taounate', 'Taroudant', 'Taza',
    'Tétouan', 'Tiznit', 'Zagora'
];
?>             

<div id="profil" class="section d-none">
    <h4 class="mb-4 " style="font-size: 30px; font-weight: 700;">Mon Profil</h4>

    <?php if ($success_profil != '') { ?>
        <div class='alert alert-success alert-dismissible fade show' role='alert'>
            <?= $success_profil ?>
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>
    <?php }?>
    

    <div class="content-card mb-4" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h6 class="content-card-title mb-4" style="color: #333; font-weight: bold;">Informations personnelles</h6>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Nom</label>
                    <?php if (isset($err['nom'])) echo "<div class='text-danger small mt-1'>" . $err['nom'] . "</div>" ?>
                    <input type="text" name="nom" class="form-control form-control-profile" value="<?= $user['nom']?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Prénom</label>
                    <?php if (isset($err['prenom'])) echo "<div class='text-danger small mt-1'>" . $err['prenom'] . "</div>" ?>
                    <input type="text" name="prenom" class="form-control form-control-profile" value="<?= $user['prenom'] ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Email</label>
                    <?php if (isset($err['email'])) echo "<div class='text-danger small mt-1'>" . $err['email'] . "</div>" ?>
                    <input type="email" name="email" class="form-control form-control-profile" value="<?= $user['email'] ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Ville</label>
                    <input type="text" name="ville" class="form-control form-control-profile" value="<?= $user['ville'] ?>" list="cities-list" placeholder="Sélectionnez une ville">
                    <datalist id="cities-list">
                        <?php foreach ($moroccan_cities as $city): ?>
                            <option value="<?= $city ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Rue / Adresse</label>
                    <input type="text" name="rue" class="form-control form-control-profile" value="<?= $user['rue'] ?>">
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" name="save_profil" class="btn text-white btn-save">Sauvegarder les modifications</button>
                </div>
            </div>
        </form>
    </div>

    <div class="content-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h6 class="content-card-title mb-4" style="color: #333; font-weight: bold;">Changer le mot de passe</h6>

        <?php if ($success_mdp != '') { ?>
            <div class='alert alert-success alert-dismissible fade show' role='alert'>
                <?= $success_mdp ?>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>
        <?php } ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Ancien mot de passe</label>
                    <?php if (isset($err['ancien_mdp'])) echo "<div class='text-danger small mt-1'>" . $err['ancien_mdp'] . "</div>" ?>
                    <input type="password" name="ancien_mdp" class="form-control form-control-profile" placeholder="••••••">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Nouveau mot de passe</label>
                    <?php if (isset($err['nv_mdp'])) echo "<div class='text-danger small mt-1'>" . $err['nv_mdp'] . "</div>" ?>
                    <input type="password" name="nv_mdp" class="form-control form-control-profile" placeholder="••••••">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Confirmer le nouveau</label>
                    <?php if (isset($err['mdpc'])) echo "<div class='text-danger small mt-1'>" . $err['mdpc'] . "</div>" ?>
                    <input type="password" name="mdpc" class="form-control form-control-profile" placeholder="••••••">
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" name="save_mdp" class="btn text-white btn-save">Modifier le mot de passe</button>
                </div>
            </div>
        </form>
    </div>
</div>