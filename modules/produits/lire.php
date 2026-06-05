<?php
/**
 * Liste des produits - Lecture avec fonctions centralisées
 * Étudiant 1 : Lead Backend & Data
 */
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include_once("../../includes/fonctions-produits.php");
// Vérifier la permission
exiger_permission("voir_produits");
include("../../includes/header.php");

// Charger les produits via la fonction centralisée
$produits = chargerProduits();
?>

<h2>Liste des Produits</h2>

<?php if (empty($produits)): ?>
    <p>Aucun produit disponible.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Code-barre</th>
                <th>Nom</th>
                <th>Prix HT</th>
                <th>Stock</th>
                <th>Date d'expiration</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $produit): ?>
                <tr>
                    <td><?php echo htmlspecialchars($produit["code_barre"] ?? ""); ?></td>
                    <td><?php echo htmlspecialchars($produit["nom"] ?? ""); ?></td>
                    <td><?php echo number_format($produit["prix_unitaire_ht"] ?? 0, 0, ',', ' '); ?> CDF</td>
                    <td><?php echo (int)($produit["quantite_stock"] ?? 0); ?></td>
                    <td><?php echo htmlspecialchars($produit["date_expiration"] ?? ""); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
include("../../includes/footer.php");
?>
