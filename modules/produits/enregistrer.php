<?php
/**
 * Enregistrer un produit - Interface complète
 * Étudiant 2 : Front-End & Hardware
 */
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include("../../includes/header.php");

// Vérifier la permission
exiger_permission("enregistrer_produit");

// Charger les fonctions
include_once("../../includes/fonctions-produits.php");

$message = "";
$erreur = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nouveau_produit = [
        "code_barre" => $_POST["code_barre"],
        "nom" => $_POST["nom"],
        "prix_unitaire_ht" => (float)$_POST["prix_unitaire_ht"],
        "quantite_stock" => (int)$_POST["quantite_stock"],
        "date_expiration" => $_POST["date_expiration"],
        "date_enregistrement" => date("Y-m-d")
    ];
    
    // Valider le produit
    $validation = validerProduit($nouveau_produit);
    
    if (!$validation[0]) {
        $erreur = $validation[1];
    } else {
        // Ajouter le produit
        $result = ajouterProduit($nouveau_produit);
        
        if ($result) {
            $message = "Produit enregistré avec succès !";
            // Réinitialiser le formulaire
            $_POST = [];
        } else {
            $erreur = "Erreur: Ce code-barre existe déjà.";
        }
    }
}

$produits = chargerProduits();
?>

<h2>Enregistrer un Produit</h2>

<?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if ($erreur): ?>
    <p style="color: red;"><?php echo htmlspecialchars($erreur); ?></p>
<?php endif; ?>

<form method="post" class="form-produit">
    <label for="code_barre">Code-barre:</label>
    <input type="text" name="code_barre" id="code_barre" required 
           value="<?php echo $_POST['code_barre'] ?? ''; ?>"
           placeholder="Ex: 1234567890123">
    
    <label for="nom">Nom du produit:</label>
    <input type="text" name="nom" id="nom" required 
           value="<?php echo $_POST['nom'] ?? ''; ?>"
           placeholder="Ex: Lait en poudre">
    
    <label for="prix_unitaire_ht">Prix unitaire HT (CDF):</label>
    <input type="number" name="prix_unitaire_ht" id="prix_unitaire_ht" required 
           value="<?php echo $_POST['prix_unitaire_ht'] ?? ''; ?>"
           min="1" step="1" placeholder="Ex: 2500">
    
    <label for="quantite_stock">Quantité en stock:</label>
    <input type="number" name="quantite_stock" id="quantite_stock" required 
           value="<?php echo $_POST['quantite_stock'] ?? ''; ?>"
           min="0" placeholder="Ex: 50">
    
    <label for="date_expiration">Date d'expiration:</label>
    <input type="date" name="date_expiration" id="date_expiration" required
           value="<?php echo $_POST['date_expiration'] ?? ''; ?>">
    
    <button type="submit" class="btn btn-primary">Enregistrer le produit</button>
</form>

<h3>Produits existants</h3>
<table>
    <thead>
        <tr>
            <th>Code-barre</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Expiration</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produits as $p): ?>
        <tr>
            <td><?php echo htmlspecialchars($p["code_barre"]); ?></td>
            <td><?php echo htmlspecialchars($p["nom"]); ?></td>
            <td><?php echo number_format($p["prix_unitaire_ht"], 0, ',', ' '); ?> CDF</td>
            <td><?php echo $p["quantite_stock"]; ?></td>
            <td><?php echo htmlspecialchars($p["date_expiration"]); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include("../../includes/footer.php"); ?>