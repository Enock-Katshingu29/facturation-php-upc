<?php
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include_once("../../includes/fonctions-auth.php");

// Vérifier la permission
exiger_permission("gestion_comptes");
include("../../includes/header.php");

$utilisateurs = chargerUtilisateurs();
?>

<h2>Liste des utilisateurs</h2>
<p><a class="btn" href="ajouter-compte.php">Ajouter un compte</a></p>

<table>
<tr><th>Identifiant</th><th>Nom complet</th><th>Rôle</th><th>Date création</th><th>Actif</th><th>Action</th></tr>

<?php foreach ($utilisateurs as $u): ?>
  <tr>
    <td><?php echo htmlspecialchars($u['identifiant']); ?></td>
    <td><?php echo htmlspecialchars($u['nom_complet']); ?></td>
    <td><?php echo htmlspecialchars($u['role']); ?></td>
    <td><?php echo htmlspecialchars($u['date_creation']); ?></td>
    <td><?php echo $u['actif'] ? 'Oui' : 'Non'; ?></td>
    <td>
      <a href="supprimer-compte.php">Gérer la suppression</a>
    </td>
  </tr>
<?php endforeach; ?>

</table>

<?php
include("../../includes/footer.php");
?>
