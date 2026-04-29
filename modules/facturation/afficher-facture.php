<?php
include("../../auth/session.php");
include("../../includes/fonctions-permissions.php");
include("../../includes/header.php");

// Vérifier la permission
exiger_permission("afficher_facture");

$factures = json_decode(file_get_contents("../../data/factures.json"), true);

// Afficher la dernière facture
$facture = end($factures);
?>

<h2>Facture : <?php echo htmlspecialchars($facture["id_facture"]); ?></h2>
<p>Date : <?php echo htmlspecialchars($facture["date"]); ?> - Heure : <?php echo htmlspecialchars($facture["heure"]); ?></p>
<p>Caissier : <?php echo htmlspecialchars($facture["caissier"]); ?></p>

<table border='1'>
<tr><th>Désignation</th><th>Prix unit. HT</th><th>Qté</th><th>Sous-total HT</th></tr>

<?php foreach ($facture["articles"] as $article): ?>
  <tr>
    <td><?php echo htmlspecialchars($article["nom"]); ?></td>
    <td><?php echo number_format($article["prix_unitaire_ht"], 0, ',', ' '); ?> CDF</td>
    <td><?php echo $article["quantite"]; ?></td>
    <td><?php echo number_format($article["sous_total_ht"], 0, ',', ' '); ?> CDF</td>
  </tr>
<?php endforeach; ?>

</table>

<p>Total HT : <?php echo number_format($facture["total_ht"], 0, ',', ' '); ?> CDF</p>
<p>TVA (18%) : <?php echo number_format($facture["tva"], 0, ',', ' '); ?> CDF</p>
<p>Total TTC : <?php echo number_format($facture["total_ttc"], 0, ',', ' '); ?> CDF</p>

<p><a href="nouvelle-facture.php">← Nouvelle facture</a></p>

<?php
include("../../includes/footer.php");
?>