<?php
include("auth/session.php"); // Vérifie la connexion

// Déterminer le chemin de base du projet (même logique que header.php)
$base_url = dirname($_SERVER['SCRIPT_NAME']);
if ($base_url === '/') $base_url = '';

include("includes/header.php"); // Menu et header

// Charger les données
$produits = json_decode(file_get_contents("data/produits.json"), true);
$factures = json_decode(file_get_contents("data/factures.json"), true);
$utilisateurs = json_decode(file_get_contents("data/utilisateurs.json"), true);

$dateJour = date("Y-m-d");
$nbProduits = count($produits);
$nbFacturesJour = 0;
$totalCAJour = 0;
$nbUtilisateurs = count($utilisateurs);

foreach ($factures as $f) {
    if ($f["date"] === $dateJour) {
        $nbFacturesJour++;
        $totalCAJour += $f["total_ttc"];
    }
}

// Calculer le CA mensuel pour les managers et super admin
$mois = date("Y-m");
$totalCAMensuel = 0;
$nbFacturesMois = 0;
foreach ($factures as $f) {
    if (strpos($f["date"], $mois) === 0) {
        $nbFacturesMois++;
        $totalCAMensuel += $f["total_ttc"];
    }
}
?>

<h2>Tableau de Bord - <?php echo ucfirst($_SESSION["role"]); ?></h2>

<?php if ($_SESSION["role"] === "caissier"): ?>
    <!-- DASHBOARD CAISSIER -->
    <div class="dashboard">
        <div class="card">
            <h3>Produits disponibles</h3>
            <p><?php echo $nbProduits; ?> produits</p>
        </div>
        <div class="card">
            <h3>Factures créées aujourd'hui</h3>
            <p><?php echo $nbFacturesJour; ?> factures</p>
        </div>
        <div class="card">
            <h3>CA du jour</h3>
            <p><?php echo number_format($totalCAJour, 0, ',', ' '); ?> CDF</p>
        </div>
    </div>

    <div class="actions">
        <h3>Actions disponibles</h3>
        <div class="action-buttons">
            <a href="<?php echo $base_url; ?>/modules/produits/lire.php" class="btn">Voir les produits</a>
            <a href="<?php echo $base_url; ?>/modules/facturation/nouvelle-facture.php" class="btn">Créer une facture</a>
            <a href="<?php echo $base_url; ?>/rapports/rapport-journalier.php" class="btn">Rapport journalier</a>
        </div>
    </div>

<?php elseif ($_SESSION["role"] === "manager"): ?>
    <!-- DASHBOARD MANAGER -->
    <div class="dashboard">
        <div class="card">
            <h3>Produits enregistrés</h3>
            <p><?php echo $nbProduits; ?> produits</p>
        </div>
        <div class="card">
            <h3>Factures du jour</h3>
            <p><?php echo $nbFacturesJour; ?> factures</p>
        </div>
        <div class="card">
            <h3>CA du jour</h3>
            <p><?php echo number_format($totalCAJour, 0, ',', ' '); ?> CDF</p>
        </div>
        <div class="card">
            <h3>Factures du mois</h3>
            <p><?php echo $nbFacturesMois; ?> factures</p>
        </div>
        <div class="card">
            <h3>CA du mois</h3>
            <p><?php echo number_format($totalCAMensuel, 0, ',', ' '); ?> CDF</p>
        </div>
    </div>

    <div class="actions">
        <h3>Actions disponibles</h3>
        <div class="action-buttons">
            <a href="<?php echo $base_url; ?>/modules/produits/enregistrer.php" class="btn">Gérer les produits</a>
            <a href="<?php echo $base_url; ?>/modules/facturation/nouvelle-facture.php" class="btn">Créer une facture</a>
            <a href="<?php echo $base_url; ?>/rapports/rapport-journalier.php" class="btn">Rapport journalier</a>
            <a href="<?php echo $base_url; ?>/rapports/rapport-mensuel.php" class="btn">Rapport mensuel</a>
        </div>
    </div>

<?php elseif ($_SESSION["role"] === "super_admin"): ?>
    <!-- DASHBOARD SUPER ADMIN -->
    <div class="dashboard">
        <div class="card">
            <h3>Produits enregistrés</h3>
            <p><?php echo $nbProduits; ?> produits</p>
        </div>
        <div class="card">
            <h3>Factures du jour</h3>
            <p><?php echo $nbFacturesJour; ?> factures</p>
        </div>
        <div class="card">
            <h3>CA du jour</h3>
            <p><?php echo number_format($totalCAJour, 0, ',', ' '); ?> CDF</p>
        </div>
        <div class="card">
            <h3>Factures du mois</h3>
            <p><?php echo $nbFacturesMois; ?> factures</p>
        </div>
        <div class="card">
            <h3>CA du mois</h3>
            <p><?php echo number_format($totalCAMensuel, 0, ',', ' '); ?> CDF</p>
        </div>
        <div class="card">
            <h3>Utilisateurs actifs</h3>
            <p><?php echo $nbUtilisateurs; ?> comptes</p>
        </div>
    </div>

    <div class="actions">
        <h3>Actions disponibles</h3>
        <div class="action-buttons">
            <a href="<?php echo $base_url; ?>/modules/produits/enregistrer.php" class="btn">Gérer les produits</a>
            <a href="<?php echo $base_url; ?>/modules/facturation/nouvelle-facture.php" class="btn">Créer une facture</a>
            <a href="<?php echo $base_url; ?>/rapports/rapport-journalier.php" class="btn">Rapport journalier</a>
            <a href="<?php echo $base_url; ?>/rapports/rapport-mensuel.php" class="btn">Rapport mensuel</a>
            <a href="<?php echo $base_url; ?>/modules/admin/gestion-comptes.php" class="btn btn-admin">Administration</a>
        </div>
    </div>
    

<?php endif; ?>

<?php include("includes/footer.php"); ?>