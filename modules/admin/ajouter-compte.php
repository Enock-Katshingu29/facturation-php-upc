<?php
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include("../../includes/header.php");

// Vérifier la permission
exiger_permission("gestion_comptes");

$message = "";
$erreur = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $utilisateurs = json_decode(file_get_contents("../../data/utilisateurs.json"), true);
    
    $nouvel_utilisateur = [
        "identifiant" => $_POST["identifiant"],
        "mot_de_passe" => password_hash($_POST["mot_de_passe"], PASSWORD_DEFAULT),
        "role" => $_POST["role"],
        "nom_complet" => $_POST["nom_complet"],
        "date_creation" => date("Y-m-d"),
        "actif" => true
    ];
    
    // Vérifier si l'identifiant existe déjà
    $existe = false;
    foreach ($utilisateurs as $u) {
        if ($u["identifiant"] === $nouvel_utilisateur["identifiant"]) {
            $existe = true;
            break;
        }
    }
    
    if (!$existe) {
        $utilisateurs[] = $nouvel_utilisateur;
        file_put_contents("../../data/utilisateurs.json", json_encode($utilisateurs, JSON_PRETTY_PRINT));
        $message = "Utilisateur ajouté avec succès !";
    } else {
        $erreur = "Cet identifiant existe déjà.";
    }
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
file_put_contents("../../data/utilisateurs.json", json_encode($utilisateurs, JSON_PRETTY_PRINT));

?>

<h2>Ajouter un compte utilisateur</h2>

<?php if ($message): ?>
  <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if ($erreur): ?>
  <p style="color: red;"><?php echo htmlspecialchars($erreur); ?></p>
<?php endif; ?>

<form method="post">
  <input type="text" name="identifiant" placeholder="Identifiant" required>
  <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
  <select name="role">
    <option value="caissier">Caissier</option>
    <option value="manager">Manager</option>
    <option value="super_admin">Super Administrateur</option>
  </select>
  <input type="text" name="nom_complet" placeholder="Nom complet" required>
  <button type="submit">Créer le compte</button>
</form>

<p><a href="gestion-comptes.php">← Retour à la gestion des comptes</a></p>

<?php
include("../../includes/footer.php");
?>