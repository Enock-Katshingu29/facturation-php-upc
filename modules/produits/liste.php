<?php
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");

// Vérifier la permission
exiger_permission("voir_produits");
include("../../includes/header.php");

$produits = json_decode(file_get_contents("../../data/produits.json"), true);

?>

<h2>Catalogue Produits</h2>
echo "<table border='1'>
<tr><th>Code-barre</th><th>Nom</th><th>Prix HT</th><th>Stock</th><th>Date Expiration</th></tr>";

foreach ($produits as $p) {
    echo "<tr>
        <td>{$p['code_barre']}</td>
        <td>{$p['nom']}</td>
        <td>{$p['prix_unitaire_ht']} CDF</td>
        <td>{$p['quantite_stock']}</td>
        <td>{$p['date_expiration']}</td>
    </tr>";
}

echo "</table>";

<?php
include("../../includes/footer.php");
?>
