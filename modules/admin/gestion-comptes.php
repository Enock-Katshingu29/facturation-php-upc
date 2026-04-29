<?php
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include("../../includes/header.php");

// Vérifier la permission
exiger_permission("gestion_comptes");

$utilisateurs = json_decode(file_get_contents("../../data/utilisateurs.json"), true);
?>

<h2>Liste des utilisateurs</h2>
<table border='1'>
<tr><th>Identifiant</th><th>Nom complet</th><th>Rôle</th><th>Date création</th><th>Actif</th><th>Action</th></tr>

<?php foreach ($utilisateurs as $u): ?>
  <tr>
    <td><?php echo htmlspecialchars($u['identifiant']); ?></td>
    <td><?php echo htmlspecialchars($u['nom_complet']); ?></td>
    <td><?php echo htmlspecialchars($u['role']); ?></td>
    <td><?php echo htmlspecialchars($u['date_creation']); ?></td>
    <td><?php echo $u['actif'] ? 'Oui' : 'Non'; ?></td>
    <td>
      <a href="ajouter-compte.php">Ajouter</a> | 
      <a href="supprimer-compte.php?identifiant=<?php echo urlencode($u['identifiant']); ?>" onclick="return confirm('Supprimer ce compte?')">Supprimer</a>
    </td>
  </tr>
<?php endforeach; ?>

</table>

<p><a href="ajouter-compte.php">+ Ajouter un nouveau compte</a></p>

<?php
include("../../includes/footer.php");
?>