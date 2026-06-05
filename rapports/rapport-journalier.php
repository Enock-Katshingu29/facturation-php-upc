<?php
include("../auth/session.php");
include("../config/config.php");
include("../includes/fonctions-permissions.php");

// Chemin de base pour les liens
$base_url = base_url();

// Vérifier la permission
exiger_permission("rapport_journalier");
include("../includes/header.php");

$factures = json_decode(file_get_contents("../data/factures.json"), true) ?: [];
$date_du_jour = date("Y-m-d");

$total_journalier = 0;
$nbFactures = 0;
$totalCA = 0;

foreach ($factures as $f) {
    if ($f["date"] === $date_du_jour) {
        $total_journalier += $f["total_ttc"];
    }
}

$dateJour = $date_du_jour;
?>


  <h2>Rapport journalier - <?php echo htmlspecialchars($dateJour); ?></h2>
  <table>
    <thead>
    <tr>
      <th>ID Facture</th>
      <th>Caissier</th>
      <th>Total HT</th>
      <th>TVA</th>
      <th>Total TTC</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($factures as $f): ?>
      <?php if ($f["date"] === $dateJour): ?>
        <tr>
          <td><?php echo htmlspecialchars($f['id_facture']); ?></td>
          <td><?php echo htmlspecialchars($f['caissier']); ?></td>
          <td><?php echo number_format($f['total_ht'], 0, ',', ' '); ?> CDF</td>
          <td><?php echo number_format($f['tva'], 0, ',', ' '); ?> CDF</td>
          <td><?php echo number_format($f['total_ttc'], 0, ',', ' '); ?> CDF</td>
        </tr>
        <?php 
          $totalCA += $f["total_ttc"];
          $nbFactures++;
        ?>
      <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="report-summary">
    <p>Nombre de factures : <strong><?php echo $nbFactures; ?></strong></p>
    <p>Chiffre d’affaires du jour : <strong><?php echo number_format($totalCA, 0, ',', ' '); ?> CDF</strong></p>
  </div>

<?php
include("../includes/footer.php");
?>
