<?php
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include_once("../../includes/fonctions-auth.php");

// Vérifier la permission
exiger_permission("gestion_comptes");
include("../../includes/header.php");

$message = "";
$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifiantASupprimer = trim($_POST["identifiant"] ?? "");

    if ($identifiantASupprimer === ($_SESSION["identifiant"] ?? "")) {
        $erreur = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        [$succes, $resultat] = supprimerUtilisateur($identifiantASupprimer);
        if ($succes) {
            $message = $resultat;
        } else {
            $erreur = $resultat;
        }
    }
}

$utilisateurs = chargerUtilisateurs();

?>

<h2>Supprimer un compte utilisateur</h2>

<?php if ($message): ?>
  <p class="alert alert-success"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if ($erreur): ?>
  <p class="alert alert-error"><?php echo htmlspecialchars($erreur); ?></p>
<?php endif; ?>

<table>
<tr><th>Identifiant</th><th>Nom complet</th><th>Rôle</th><th>Action</th></tr>

<?php foreach ($utilisateurs as $u): ?>
  <tr>
    <td><?php echo htmlspecialchars($u['identifiant']); ?></td>
    <td><?php echo htmlspecialchars($u['nom_complet']); ?></td>
    <td><?php echo htmlspecialchars($u['role']); ?></td>
    <td>
      <?php if ($u["identifiant"] === ($_SESSION["identifiant"] ?? "")): ?>
        Compte actuel
      <?php else: ?>
        <form method="post" class="inline-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')">
          <input type="hidden" name="identifiant" value="<?php echo htmlspecialchars($u["identifiant"], ENT_QUOTES, "UTF-8"); ?>">
          <button type="submit" class="btn-small">Supprimer</button>
        </form>
      <?php endif; ?>
    </td>
  </tr>
<?php endforeach; ?>

</table>

<p><a href="gestion-comptes.php">← Retour à la gestion des comptes</a></p>

<?php
include("../../includes/footer.php");
?>
