<?php
session_start();

$utilisateurs = json_decode(file_get_contents(__DIR__ . "/../data/utilisateurs.json"), true);
$erreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST["nom"];
    $role = $_POST["role"];
    $motDePasse = $_POST["mot_de_passe"];

    foreach ($utilisateurs as $user) {
        if ($user["nom_complet"] === $nom && $user["role"] === $role && $user["actif"] && password_verify($motDePasse, $user["mot_de_passe"])) {
            $_SESSION["identifiant"] = $user["identifiant"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["utilisateur"] = $user;
            header('Location: ../dashboard.php');
            exit();
        }
    }
    $erreur = "Nom, rôle ou mot de passe incorrect.";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Système de Facturation - Connexion</title>
  <link rel="stylesheet" href="/facturation/assets/css/style.css">
</head>
<body>
  <main>
    <h2>Connexion</h2>
    <?php if ($erreur): ?>
      <p style="color: red;"><?php echo htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
      <input type="text" name="nom" placeholder="Votre nom complet" required>
      
      <select name="role" required>
        <option value="caissier">Caissier</option>
        <option value="manager">Manager</option>
        <option value="super_admin">Super Administrateur</option>
      </select>

      <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>

      <button type="submit">Se connecter</button>
    </form>
  </main>

  <footer>
    <p>&copy; 2026 - Supermarché SA KIMA S.A</p>
  </footer>
</body>
</html>