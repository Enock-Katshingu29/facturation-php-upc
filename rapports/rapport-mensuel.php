<?php
include("../auth/session.php");
include("../includes/fonctions-permissions.php");
include("../includes/header.php");

// Vérifier la permission
exiger_permission("rapport_mensuel");

// Charger les factures
$factures = json_decode(file_get_contents("../data/factures.json"), true);

// Déterminer le mois en cours (ex: "2026-04")
$mois = date("Y-m");

$total_mensuel = 0;
$nb_factures = 0;
?>

<h2>Rapport Mensuel - <?php echo $mois; ?></h2>
<table border='1'>
<tr><th>Date</th><th>ID Facture</th><th>Caissier</th><th>Total HT</th><th>TVA</th><th>Total TTC</th></tr>

<?php foreach ($factures as $f): ?>
  <?php if (strpos($f["date"], $mois) === 0): ?>
  <tr>
    <td><?php echo htmlspecialchars($f["date"]); ?></td>
    <td><?php echo htmlspecialchars($f["id_facture"]); ?></td>
    <td><?php echo htmlspecialchars($f["caissier"]); ?></td>
    <td><?php echo number_format($f["total_ht"], 0, ',', ' '); ?> CDF</td>
    <td><?php echo number_format($f["tva"], 0, ',', ' '); ?> CDF</td>
    <td><?php echo number_format($f["total_ttc"], 0, ',', ' '); ?> CDF</td>
  </tr>
  <?php 
    $total_mensuel += $f["total_ttc"];
    $nb_factures++;
  endif; ?>
<?php endforeach; ?>

</table>

<p>Nombre de factures : <?php echo $nb_factures; ?></p>
<p>Chiffre d'affaires du mois : <?php echo number_format($total_mensuel, 0, ',', ' '); ?> CDF</p>

<?php
include("../includes/footer.php");
?>