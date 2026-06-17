<?php
session_start();

$emailCookie = "";
if (isset($_COOKIE['remember_mail'])) {
    $emailCookie = $_COOKIE['remember_mail'];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Green Market — Authentification</title>

  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="Inscription.css">
</head>

<body>

  <div class="page">
    <a href="../Home Page/Home.html" class="logo-top">✦ GREEN MARKET</a>

    <!-- LEFT -->
    <div class="left-side">
      <h1>
        Produits Marocains Authentiques<br>
        <span class="text-highlight">Fait avec Tradition</span>
      </h1>
      <p>
        Green Market connecte les coopératives marocaines directement à vous —
        huile d'argan, poterie, belgha, textiles et cosmétiques naturels,
        fabriqués à la main avec soin.
      </p>
      <a href="../Produits/Produits.php" class="btn btn-form">Explorer les produits →</a>
    </div>

    <!-- RIGHT -->
    <div class="right-side">
      <div class="form-card">

        <!-- LOGIN -->
        <?php
        if (isset($_SESSION['success'])) {
          echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
          unset($_SESSION['success']);
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          extract($_POST);
          if (isset($login)) {
            $err = [];
            if (!isset($mail) || empty($mail)) $err['mail'] = "Veuillez saisir votre e-mail";
            elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $err['mail'] = 'Veuillez saisir une adresse e-mail valide';
            else {
              include("../connexion.php");
              try {
                $req = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
                $req->execute([$mail]);
                if ($req->rowCount() == 0) $err['mail'] = "cette emil n'existe pas ";
              } catch (PDOException $e) {
                die('error delection reference :' . $e->getMessage());
              }
            }

            if (!isset($mdp) || empty($mdp)) $err['mdp'] = "Veuillez saisir votre mot de passe";
            if (empty($err)) {
              include("../connexion.php");
              try {
                $relog = $pdo->prepare("SELECT * FROM utilisateur WHERE est_active=1 AND email = ? ");
                $relog->execute([$mail]);
                $tablog = $relog->fetch(PDO::FETCH_ASSOC);
                if (empty($tablog)) {
                  echo "<div class='text-danger small mt-1'>Login ou mot de passe incorrects</div>";
                } else {
                  if (password_verify($mdp, $tablog['mot_de_passe'])) {
                    $_SESSION['id_utili'] = $tablog['id_utili'];
                    $_SESSION['email'] = $tablog['email'];
                    $_SESSION['role'] = $tablog['role'];

                    if (isset($remember)) {
                      setcookie('remember_mail', $mail, time() + 30 * 24 * 60 * 60);
                    } else {
                      setcookie('remember_mail', '', time() - 3600);
                    }

                    header("Location: ../Home Page/Home.php");
                    exit;
                  } else  echo "<div style='color:red'>Login ou mot de passe incorrects</div>";
                }
              } catch (PDOException $e) {
                die("Erreur authentification :" . $e->getMessage());
              }
            }
          }
        }
        ?>
        <form method="POST" class="form-panel" id="form-login">
          <h3>Connexion membre</h3>

          <?php if (isset($err['mail'])) echo "<div class='text-danger small mt-1'>" . $err['mail'] . "</div>" ?>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" placeholder="Email" name="mail" value="<?php echo $emailCookie; ?>">
          </div>

          <?php if (isset($err['mdp'])) echo "<div class='text-danger small mt-1'>" . $err['mdp'] . "</div>" ?>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" placeholder="Mot de passe" name="mdp">
          </div>


          <div class="form-row">
            <label><input type="checkbox" name="remember"> Se souvenir de moi</label>
            <a href="#" class="lien-form">Mot de passe oublié ?</a>
          </div>

          <button type="submit" class="btn btn-form w-100" name="login">Se connecter</button>

          <p class="form-footer">
            Pas encore membre ? <a class="lien-form" data-go="register">Créer un compte</a>
          </p>
        </form>

        <!-- REGISTER -->
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          extract($_POST);
          if (isset($inscri)) {
            $err = [];
            // Validation des données du formulaire
            if (!isset($nom) || empty($nom)) $err['nom'] = "Veuillez saisir votre nom";
            elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $nom)) $err['nom'] = "Veuillez saisir un nom valide";

            if (!isset($prenom) || empty($prenom)) $err['prenom'] = "Veuillez saisir votre prénom";
            elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $prenom)) $err['prenom'] = "Veuillez saisir un prénom valide";

            if (!isset($mail) || empty($mail)) $err['mail'] = "Veuillez saisir votre adresse e-mail";
            elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $err['mail'] = 'Veuillez saisir une adresse e-mail valide';
            else {
              include("../connexion.php");
              try {
                $remail = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
                $remail->execute([$mail]);
                if ($remail->rowCount() != 0) $err['mail'] = "Cette adresse e-mail est déjà utilisée ";
              } catch (PDOException $e) {
                die("Erreur selection reference :" . $e->getMessage());
              }
            }

            if (!isset($mdp) || empty($mdp)) $err['mdp'] = "Veuillez saisir un mot de passe";
            elseif (strlen($mdp) < 8) $err['mdp'] = "Le mot de passe doit contenir au moins 8 caractères";

            if (!isset($mdpCon) || empty($mdpCon)) $err['mdpCon'] = "Veuillez confirmer votre mot de passe";
            elseif ($mdp !== $mdpCon) $err['mdpCon'] = "Les mots de passe ne correspondent pas";

            if (!isset($role) || empty($role)) $err['role'] = "Veuillez sélectionner un rôle";
            elseif (!in_array($role, ['client', 'producteur'])) $err['role'] = "Veuillez sélectionner un rôle valide";

            if (!isset($ville) || empty($ville)) $err['ville'] = "Veuillez saisir votre ville";

            if (!isset($rue) || empty($rue)) $err['rue'] = "Veuillez saisir votre rue";




            if (empty($err)) {

              // nettoyage des donnes
              $nom = htmlspecialchars(trim($nom));
              $prenom = htmlspecialchars(trim($prenom));

              //insertion dans la Base des donnees
              try {
                $mdp = password_hash($mdp, PASSWORD_ARGON2ID);
                $date = date("Y-m-d H:i:s");
                $res = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, ville, rue, date_inscription) VALUES (?,?,?,?,?,?,?,?)");
                $r =$res->execute([$nom, $prenom, $mail, $mdp, $role, $ville, $rue, $date]);
                if ($r == False) {
                  echo "Echec d'insertion ";
                } else {
                  $_SESSION['success'] = "Compte créé avec succès !";
                  header("Location:../Home Page/Home.php");
                  exit;
                }
              } catch (PDOException $e) {
                die("erreur insertion prod : " . $e->getMessage());
              }
            }
          }
        }


        ?>
        <form method="POST" class="form-panel d-none" id="form-register" action="<?= $_SERVER['PHP_SELF']; ?>" novalidate>
          <?php
          if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($err) && isset($inscri)) {
          ?>
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                showRegister();
              });
            </script>
          <?php
          }
          ?>
          <h3>Créer un compte</h3>

          <div class="d-flex gap-2">
            <div class="flex-fill">
              <?php if (isset($err['nom'])) echo "<div class='text-danger small mt-1'>" . $err['nom'] . "</div>" ?>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="nom" class="form-control" value="<?php if (isset($nom)) echo $nom ?>" placeholder="Nom">
              </div>
            </div>
            <div class="flex-fill">
              <?php if (isset($err['prenom'])) echo "<div class='text-danger small mt-1'>" . $err['prenom'] . "</div>" ?>
              <div class="input-group">
                <input type="text" name="prenom" class="form-control" value="<?php if (isset($prenom)) echo $prenom ?>" placeholder="Prénom">
              </div>
            </div>
          </div>

          <?php if (isset($err['mail'])) echo "<div class='text-danger small mt-1'>" . $err['mail'] . "</div>" ?>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="mail" class="form-control" value="<?php if (isset($inscri) && isset($mail)) echo $mail; ?>" placeholder="Email">
          </div>

          <?php $cities = ["Casablanca","Rabat","Salé","Tanger","Tétouan","Fès","Marrakech","Agadir","Meknès","Oujda","Kenitra","Safi","El Jadida","Nador","Béni Mellal","Khouribga","Errachidia","Ouarzazate","Laâyoune","Dakhla","Settat","Mohammedia","Skhirat","Temara","Larache","Ksar El Kebir","Berkane","Taourirt","Al Hoceïma","Taza","Ifrane","Midelt","Azrou","Chefchaouen","Essaouira","Asilah","Fnideq","Martil","Tan-Tan","Guelmim","Taroudant","Zagora","Oued Zem","Youssoufia","Jerada","Guercif","Sidi Bennour","Sidi Kacem","Sidi Slimane"]; ?>
          <datalist id="villes">
            <?php foreach ($cities as $city) { echo "<option value='$city'>"; } ?>
          </datalist>
          <div class="d-flex gap-2">
            <div class="flex-fill">
              <?php if (isset($err['ville'])) echo "<div class='text-danger small mt-1'>" . $err['ville'] . "</div>" ?>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                <input list="villes" name="ville" class="form-control" value="<?php if(isset($ville)) echo $ville; ?>" placeholder="Ville">
              </div>
            </div>
            <div class="flex-fill">
              <?php if (isset($err['rue'])) echo "<div class='text-danger small mt-1'>" . $err['rue'] . "</div>" ?>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                <input type="text" name="rue" class="form-control" value="<?php if (isset($rue)) echo $rue; ?>" placeholder="Rue">
              </div>
            </div>
          </div>

          <?php if (isset($err['mdp'])) echo "<div class='text-danger small mt-1'>" . $err['mdp'] . "</div>" ?>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="mdp" class="form-control" placeholder="Mot de passe">
          </div>

          <?php if (isset($err['mdpCon'])) echo "<div class='text-danger small mt-1'>" . $err['mdpCon'] . "</div>" ?>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
            <input type="password" name="mdpCon" class="form-control" placeholder="Confirmer le mot de passe">
          </div>

          <?php if (isset($err['role'])) echo "<div class='text-danger small mt-1'>" . $err['role'] . "</div>" ?>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
            <select name="role" class="form-control">
              <option value="" selected disabled>Choisir un rôle</option>
              <option value="client" <?= (isset($role) && $role == 'client') ? 'selected' : '' ?>>Client</option>
              <option value="producteur" <?= (isset($role) && $role == 'producteur') ? 'selected' : '' ?>>Producteur</option>
            </select>
          </div>


          <button type="submit" class="btn btn-form w-100 mt-2" name="inscri">S'inscrire</button>

          <p class="form-footer">
            Déjà membre ? <a class="lien-form" data-go="login">Se connecter</a>
          </p>
        </form>

      </div>
    </div>
  </div>

  <script src="Inscription.js"></script>
</body>

</html>