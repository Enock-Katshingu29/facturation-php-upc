<?php
session_start();
include_once(__DIR__ . "/../config/config.php");
include_once(__DIR__ . "/../includes/fonctions-auth.php");

$erreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"] ?? "");
    $role = $_POST["role"] ?? "";
    $motDePasse = $_POST["mot_de_passe"] ?? "";
    $user = verifierConnexion($nom, $role, $motDePasse);

    if ($user !== null) {
        session_regenerate_id(true);
        $_SESSION["identifiant"] = $user["identifiant"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["utilisateur"] = $user;
        header("Location: " . BASE_URL . "/dashboard.php");
        exit();
    }

    $erreur = "Nom, rôle ou mot de passe incorrect.";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Système de Facturation - Connexion</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="login-page">
  <main class="login-shell">
    <section class="login-intro">
      <span class="brand-mark">K</span>
      <p class="eyebrow">KIMA S.A</p>
      <h1>Votre caisse, simple et efficace.</h1>
      <p>Gérez les produits, les ventes et les rapports depuis un espace unique.</p>
    </section>

    <section class="login-card">
    <p class="eyebrow">Espace sécurisé</p>
    <h2>Bienvenue</h2>
    <p class="login-subtitle">Connectez-vous pour accéder à votre tableau de bord.</p>
    <?php if ($erreur): ?>
      <p class="alert alert-error"><?php echo htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="post">
      <label for="nom">Nom complet</label>
      <input type="text" name="nom" id="nom" value="<?php echo htmlspecialchars($_POST["nom"] ?? "", ENT_QUOTES, "UTF-8"); ?>" autocomplete="name" required>
      
      <label for="role">Rôle</label>
      <select name="role" id="role" required>
        <option value="caissier"<?php echo ($_POST["role"] ?? "") === "caissier" ? " selected" : ""; ?>>Caissier</option>
        <option value="manager"<?php echo ($_POST["role"] ?? "") === "manager" ? " selected" : ""; ?>>Manager</option>
        <option value="super_admin"<?php echo ($_POST["role"] ?? "") === "super_admin" ? " selected" : ""; ?>>Super Administrateur</option>
      </select>

      <label for="mot_de_passe">Mot de passe</label>
      <input type="password" name="mot_de_passe" id="mot_de_passe" autocomplete="current-password" required>

      <button type="submit">Se connecter</button>
    </form>
    </section>
  </main>

  <footer class="site-footer login-footer">
    <p><strong>KIMA S.A</strong> &copy; 2026</p>
  </footer>
</body>
</html>
