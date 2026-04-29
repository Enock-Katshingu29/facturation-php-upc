<?php
include_once(__DIR__ . "/../config/config.php");
include_once(__DIR__ . "/fonctions-permissions.php");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Système de Facturation</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
  <header>
    <h1>Système de Caisse - Supermarché</h1>
    <nav>
      <ul>
        <li><a href="<?php echo BASE_URL; ?>/dashboard.php">Accueil</a></li>
        
        <?php if (afficher_lien("voir_produits")): ?>
          <li><a href="<?php echo BASE_URL; ?>/modules/produits/lire.php">Voir Produits</a></li>
        <?php endif; ?>
        
        <?php if (afficher_lien("gestion_produits")): ?>
          <li><a href="<?php echo BASE_URL; ?>/modules/produits/enregistrer.php">Gérer Produits</a></li>
        <?php endif; ?>
        
        <?php if (afficher_lien("creer_facture")): ?>
          <li><a href="<?php echo BASE_URL; ?>/modules/facturation/nouvelle-facture.php">Facturation</a></li>
          
        <?php endif; ?>
        
        <?php if (afficher_lien("rapport_journalier")): ?>
          <li><a href="<?php echo BASE_URL; ?>/rapports/rapport-journalier.php">Rapport Journalier</a></li>
        <?php endif; ?>
        
        <?php if (afficher_lien("rapport_mensuel")): ?>
          <li><a href="<?php echo BASE_URL; ?>/rapports/rapport-mensuel.php">Rapport Mensuel</a></li>
        <?php endif; ?>
        
        <?php if (afficher_lien("gestion_comptes")): ?>
          <li><a href="<?php echo BASE_URL; ?>/modules/admin/gestion-comptes.php">Administration</a></li>
        <?php endif; ?>
        
        <li><a href="<?php echo BASE_URL; ?>/auth/logout.php">Déconnexion</a></li>
      </ul>
    </nav>
  </header>
  <main>