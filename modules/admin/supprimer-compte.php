<?php
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include("../../includes/header.php");

// Vérifier la permission
exiger_permission("gestion_comptes");

$message = "";
$erreur = "";

// Traitement de la suppression
if (isset($_GET['identifiant'])) {
    $utilisateurs = json_decode(file_get_contents("../../data/utilisateurs.json"), true);
    $identifiant_a_supprimer = $_GET['identifiant'];
    
    $nouveaux_utilisateurs = [];
    foreach ($utilisateurs as $u) {
        if ($u['identifiant'] !== $identifiant_a_supprimer) {
            $nouveaux_utilisateurs[] = $u;
        }
    }
    
    file_put_contents("../../data/utilisateurs.json", json_encode($nouveaux_utilisateurs, JSON_PRETTY_PRINT));
    $message = "Utilisateur supprimé avec succès !";
}

$utilisateurs = json_decode(file_get_contents("../../data/utilisateurs.json"), true);

// Exemple : création d’un nouveau caissier
$nouvel_utilisateur = [
    "identifiant" => "paul.kongo",
    "mot_de_passe" => password_hash("MotDePasse123", PASSWORD_DEFAULT),
    "role" => "caissier",
    "nom_complet" => "Paul Kongo",
    "date_creation" => date("Y-m-d"),
    "actif" => true
];

// Ajouter au fichier
$utilisateurs[] = $nouvel_utilisateur;
file_put_contents("../data/utilisateurs.json", json_encode($utilisateurs, JSON_PRETTY_PRINT));

?>

<h2>Supprimer un compte utilisateur</h2>

<?php if ($message): ?>
  <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<table border='1'>
<tr><th>Identifiant</th><th>Nom complet</th><th>Rôle</th><th>Action</th></tr>

<?php foreach ($utilisateurs as $u): ?>
  <tr>
    <td><?php echo htmlspecialchars($u['identifiant']); ?></td>
    <td><?php echo htmlspecialchars($u['nom_complet']); ?></td>
    <td><?php echo htmlspecialchars($u['role']); ?></td>
    <td><a href="?identifiant=<?php echo urlencode($u['identifiant']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')">Supprimer</a></td>
  </tr>
<?php endforeach; ?>

</table>

<p><a href="gestion-comptes.php">← Retour à la gestion des comptes</a></p>

<?php
include("../../includes/footer.php");
?>