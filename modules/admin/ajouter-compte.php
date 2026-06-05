<?php
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include_once("../../includes/fonctions-auth.php");

// Vérifier la permission
exiger_permission("gestion_comptes");
include("../../includes/header.php");

$message = "";
$erreur = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifiant = trim($_POST["identifiant"] ?? "");
    $motDePasse = $_POST["mot_de_passe"] ?? "";
    $role = $_POST["role"] ?? "";
    $nomComplet = trim($_POST["nom_complet"] ?? "");

    if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $identifiant)) {
        $erreur = "L'identifiant doit contenir entre 3 et 50 caractères valides.";
    } elseif (strlen($motDePasse) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($nomComplet === "") {
        $erreur = "Le nom complet est obligatoire.";
    } else {
        [$succes, $resultat] = creerUtilisateur($identifiant, $motDePasse, $role, $nomComplet);
        if ($succes) {
            $message = $resultat;
            $_POST = [];
        } else {
            $erreur = $resultat;
        }
    }
}

?>

<h2>Ajouter un compte utilisateur</h2>

<?php if ($message): ?>
  <p class="alert alert-success"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if ($erreur): ?>
  <p class="alert alert-error"><?php echo htmlspecialchars($erreur); ?></p>
<?php endif; ?>

<form method="post">
  <label for="identifiant">Identifiant</label>
  <input type="text" name="identifiant" id="identifiant" value="<?php echo htmlspecialchars($_POST["identifiant"] ?? ""); ?>" required>
  <label for="mot_de_passe">Mot de passe</label>
  <input type="password" name="mot_de_passe" id="mot_de_passe" minlength="8" required>
  <label for="role">Rôle</label>
  <select name="role" id="role">
    <option value="caissier">Caissier</option>
    <option value="manager">Manager</option>
    <option value="super_admin">Super Administrateur</option>
  </select>
  <label for="nom_complet">Nom complet</label>
  <input type="text" name="nom_complet" id="nom_complet" value="<?php echo htmlspecialchars($_POST["nom_complet"] ?? ""); ?>" required>
  <button type="submit">Créer le compte</button>
</form>

<p><a href="gestion-comptes.php">← Retour à la gestion des comptes</a></p>

<?php
include("../../includes/footer.php");
?>
