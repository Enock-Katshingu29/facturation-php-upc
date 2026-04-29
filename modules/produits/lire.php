<?php
/**
 * Liste des produits - Lecture avec fonctions centralisées
 * Étudiant 1 : Lead Backend & Data
 */
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include("../../includes/header.php");
include_once("../../includes/fonctions-produits.php");
// Vérifier la permission
exiger_permission("voir_produits");

// Charger les produits via la fonction centralisée
$produits = chargerProduits();
?>

<h2>Liste des Produits</h2>

<?php if (empty($produits)): ?>
    <p>Aucun produit disponible.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid #ddd; padding: 10px;">Code Barre</th>
                <th style="border: 1px solid #ddd; padding: 10px;">Nom</th>
                <th style="border: 1px solid #ddd; padding: 10px;">Prix HT</th>
                <th style="border: 1px solid #ddd; padding: 10px;">Stock</th>
                <th style="border: 1px solid #ddd; padding: 10px;">Date Expiration</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $produit): ?>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 10px;"><?php echo htmlspecialchars($produit["code_barre"] ?? ""); ?></td>
                    <td style="border: 1px solid #ddd; padding: 10px;"><?php echo htmlspecialchars($produit["nom"] ?? ""); ?></td>
                    <td style="border: 1px solid #ddd; padding: 10px;"><?php echo number_format($produit["prix_unitaire_ht"] ?? 0, 0, ',', ' '); ?> CDF</td>
                    <td style="border: 1px solid #ddd; padding: 10px;"><?php echo $produit["quantite_stock"] ?? 0; ?></td>
                    <td style="border: 1px solid #ddd; padding: 10px;"><?php echo htmlspecialchars($produit["date_expiration"] ?? ""); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
include("../../includes/footer.php");
?>
