<?php
include("../auth/session.php");
include("../config/config.php");
include("../includes/fonctions-permissions.php");
include("../includes/header.php");

// Chemin de base pour les liens
$base_url = base_url();

// Vérifier la permission
exiger_permission("rapport_journalier");

$factures = json_decode(file_get_contents("../data/factures.json"), true);
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


  <h2>Rapport Journalier - <?php echo $dateJour; ?></h2>
  <table>
    <tr>
      <th>ID Facture</th>
      <th>Caissier</th>
      <th>Total HT</th>
      <th>TVA</th>
      <th>Total TTC</th>
    </tr>
    <?php foreach ($factures as $f): ?>
      <?php if ($f["date"] === $dateJour): ?>
        <tr>
          <td><?php echo $f['id_facture']; ?></td>
          <td><?php echo $f['caissier']; ?></td>
          <td><?php echo $f['total_ht']; ?> CDF</td>
          <td><?php echo $f['tva']; ?> CDF</td>
          <td><?php echo $f['total_ttc']; ?> CDF</td>
        </tr>
        <?php 
          $totalCA += $f["total_ttc"];
          $nbFactures++;
        ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </table>
  <p>Nombre de factures : <?php echo $nbFactures; ?></p>
  <p>Chiffre d’affaires du jour : <?php echo $totalCA; ?> CDF</p>

<?php
include("../includes/footer.php");
?>